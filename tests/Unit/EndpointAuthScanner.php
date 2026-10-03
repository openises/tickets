<?php
/**
 * Static analysis helper for EndpointAuthTest.
 *
 * Answers one question about a PHP endpoint file: does it authenticate the caller BEFORE it
 * produces output or touches data? It works on the token stream (never on raw text), so a
 * comment that merely mentions do_login() can neither satisfy nor break the check.
 *
 * "Gate" means a top-level, unconditional statement that is exactly one of:
 *     do_login(basename(__FILE__));     the page idiom (shows the login form, exits when anonymous)
 *     require_login_or_401();           the data idiom (HTTP 401, plain body, exits when anonymous)
 * Both live in incs/login.inc.php.
 *
 * "Before" is textual order, ignoring everything inside function/class bodies (those do not run
 * until called). Anything that could emit output or read/write data and sits before the gate is
 * reported as a violation, so a gate that is correct but too late is caught as well.
 *
 * @since v3.44.7
 */
final class EndpointAuthScanner
{
    /** Calls that must never run before the gate: they read or write data, send mail, or emit output. */
    private const EFFECT_CALLS = [
        'db_query', 'db_fetch_all', 'db_fetch_one', 'db_fetch_value', 'db_insert_id', 'db_affected_rows',
        'mysqli_query', 'mysql_query', 'mysqli_connect',
        'dump', 'var_dump', 'print_r', 'printf', 'vprintf', 'var_export', 'readfile', 'fpassthru',
        'file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 'fputs', 'unlink', 'rename', 'copy',
        'mkdir', 'rmdir', 'move_uploaded_file', 'mail', 'do_send', 'do_log', 'exec', 'shell_exec',
        'system', 'passthru', 'popen', 'proc_open', 'curl_init', 'curl_exec', 'fsockopen', 'eval',
        'session_destroy', 'unserialize', 'readdir', 'opendir', 'scandir', 'glob',
    ];

    /**
     * Calls that may run before the gate because they only inspect their arguments or the request
     * and change nothing outside the process. ANY other call before the gate is reported: a function
     * the scanner has never heard of could read or write anything, and "I did not know what it did"
     * is not an argument for letting it run for an anonymous caller.
     */
    private const SAFE_CALLS = [
        'error_reporting', 'ini_set', 'set_time_limit', 'ignore_user_abort', 'session_start', 'session_write_close',
        'session_status', 'session_name', 'session_id', 'configure_secure_session', 'headers_sent', 'header', 'http_response_code',
        'array_key_exists', 'in_array', 'is_array', 'is_string', 'is_numeric', 'is_int', 'is_null', 'is_bool', 'intval', 'floatval', 'strval', 'boolval',
        'count', 'sizeof', 'explode', 'implode', 'join', 'trim', 'ltrim', 'rtrim', 'strlen', 'strpos', 'stripos', 'strrpos', 'substr',
        'str_replace', 'str_repeat', 'strtolower', 'strtoupper', 'ucfirst', 'sprintf', 'basename', 'dirname', 'realpath', 'file_exists', 'is_file',
        'is_dir', 'define', 'defined', 'constant', 'time', 'date', 'mktime', 'microtime', 'max', 'min', 'abs', 'round', 'floor', 'ceil',
        'array_merge', 'array_keys', 'array_values', 'array_flip', 'array_slice', 'php_sapi_name', 'phpversion', 'function_exists', 'class_exists',
        'extension_loaded', 'date_default_timezone_set', 'ob_start', 'ob_get_level', 'random_int', 'sanitize_int', 'sanitize_string', 'json_encode',
        'preg_match', 'preg_replace', 'urlencode', 'rawurlencode', 'htmlspecialchars', 'e', 'strcmp', 'base64_decode', 'base64_encode', 'md5',
        'array_push', 'array_pop', 'array_shift', 'array_unshift', 'array_search', 'range', 'floatval', 'is_float', 'is_object', 'func_get_args',
        'get_magic_quotes_gpc', 'error_log', 'trigger_error', 'set_error_handler', 'set_exception_handler', 'register_shutdown_function',
        'umask', 'setlocale', 'mb_internal_encoding', 'mb_strlen', 'mb_substr', 'unset', 'array', 'list', 'compact', 'extract',
    ];

    /**
     * Helpers that live in incs/functions.inc.php and only look things up (a setting, a caption, the
     * session's level) or end the session (logout). They may run before the gate, but only after the
     * file has itself loaded functions.inc.php: before that the call is a fatal error from a missing
     * function, i.e. the gate below it is never reached and the file only "works" because a parent
     * page happens to have loaded the library first.
     */
    private const AFTER_FUNCTIONS_CALLS = [
        'get_variable', 'get_msg_variable', 'get_text', 'is_guest', 'is_unit', 'is_super', 'is_administrator', 'is_admin',
        'is_user', 'is_member', 'is_facility', 'is_statistics', 'is_service_user', 'is_manager', 'do_logout', 'do_mobile_logout',
    ];

    /** Calls that run a login/CLI/guard decision. */
    private const GATE_CALLS = ['do_login', 'require_login_or_401'];

    /**
     * @param string $src PHP source of one file.
     * @return array{
     *   gate: ?string, gate_line: int, gate_toplevel: bool, violations: array<int,string>,
     *   cli_guard: bool, has_inline_html: bool, toplevel_output: bool, toplevel_gate_calls: array<int,string>
     * }
     */
    public static function analyze(string $src): array
    {
        $tokens = token_get_all($src);
        $n = count($tokens);

        // Pass 1: mark every token that sits inside a function/class/closure body.
        $inDefinition = array_fill(0, $n, false);
        $depth = 0;
        $defStack = [];           // brace depth at which each open definition body started
        $pendingDefinition = false;
        $parenDepth = 0;
        for ($i = 0; $i < $n; $i++) {
            $t = $tokens[$i];
            $isArr = is_array($t);
            $id = $isArr ? $t[0] : null;
            $txt = $isArr ? $t[1] : $t;

            if ($isArr && ($id === T_FUNCTION || $id === T_CLASS || $id === T_INTERFACE || $id === T_TRAIT || $id === self::tokenId('T_FN'))) {
                // "Foo::class" and "function" used as a type are not definitions.
                $prev = self::prevSignificant($tokens, $i);
                if (!($id === T_CLASS && $prev !== null && is_array($prev) && $prev[0] === T_DOUBLE_COLON)) {
                    $pendingDefinition = true;
                }
            }
            if ($txt === '{' || ($isArr && ($id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES))) {
                $depth++;
                if ($pendingDefinition && $txt === '{') {
                    $defStack[] = $depth;
                    $pendingDefinition = false;
                }
            } elseif ($txt === '}') {
                if (!empty($defStack) && end($defStack) === $depth) {
                    $inDefinition[$i] = true;
                    array_pop($defStack);
                    $depth--;
                    continue;
                }
                $depth--;
            } elseif ($txt === ';' && $pendingDefinition) {
                // abstract/interface method without a body, or "fn" arrow function ended
                $pendingDefinition = false;
            }
            $inDefinition[$i] = !empty($defStack);
        }

        // The one conditional shape that is still a real gate: if (logout) { do_logout(); exit; } else { do_login(); }
        $elseGate = self::findLogoutElseGate($tokens, $inDefinition);

        // Pass 2: walk the executable tokens in order.
        $result = [
            'gate' => null,
            'gate_line' => 0,
            'gate_toplevel' => false,
            'violations' => [],
            'cli_guard' => self::hasCliGuard($tokens),
            'has_inline_html' => false,
            'toplevel_output' => false,
            'toplevel_gate_calls' => [],
        ];
        $depth = 0;
        $functionsLoaded = false;
        for ($i = 0; $i < $n; $i++) {
            $t = $tokens[$i];
            $isArr = is_array($t);
            $id = $isArr ? $t[0] : null;
            $txt = $isArr ? $t[1] : $t;

            // Track brace depth for executable tokens only (definition bodies are skipped but still balanced).
            if (!$inDefinition[$i]) {
                if ($txt === '{' || ($isArr && ($id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES))) {
                    $depth++;
                } elseif ($txt === '}') {
                    $depth--;
                }
            } else {
                continue;
            }

            if ($isArr && $id === T_INLINE_HTML && trim($t[1]) !== '') {
                $result['has_inline_html'] = true;
                if ($depth === 0) {
                    $result['toplevel_output'] = true;
                }
                if ($result['gate'] === null) {
                    $result['violations'][] = 'inline HTML before the gate (line ' . self::lineOf($tokens, $i) . ')';
                }
                continue;
            }
            if ($isArr && ($id === T_ECHO || $id === T_PRINT || $id === T_OPEN_TAG_WITH_ECHO)) {
                if ($depth === 0) {
                    $result['toplevel_output'] = true;
                }
                if ($result['gate'] === null) {
                    $result['violations'][] = 'output statement before the gate (line ' . self::lineOf($tokens, $i) . ')';
                }
                continue;
            }

            if ($isArr && $id === T_STRING) {
                $next = self::nextSignificant($tokens, $i);
                $prev = self::prevSignificant($tokens, $i);
                $isCall = ($next === '(') && !(is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, self::tokenId('T_NULLSAFE_OBJECT_OPERATOR'), T_DOUBLE_COLON, T_FUNCTION, T_NEW], true));
                if ($isCall) {
                    $name = strtolower($txt);
                    if (in_array($name, self::GATE_CALLS, true)) {
                        $statementStart = ($prev === null) || $prev === ';' || $prev === '{' || $prev === '}'
                            || (is_array($prev) && in_array($prev[0], [T_OPEN_TAG, T_CLOSE_TAG, T_INLINE_HTML], true));
                        $result['toplevel_gate_calls'][] = $name . '@' . self::lineOf($tokens, $i) . (($depth === 0 && $statementStart) || $i === $elseGate ? '' : '(not top-level)');
                        if ($result['gate'] === null && (($depth === 0 && $statementStart) || $i === $elseGate)) {
                            $result['gate'] = $name;
                            $result['gate_line'] = self::lineOf($tokens, $i);
                            $result['gate_toplevel'] = true;
                        }
                    } elseif ($result['gate'] === null && in_array($name, self::EFFECT_CALLS, true)) {
                        $result['violations'][] = $name . '() before the gate (line ' . self::lineOf($tokens, $i) . ')';
                    } elseif ($result['gate'] === null && in_array($name, self::AFTER_FUNCTIONS_CALLS, true)) {
                        if (!$functionsLoaded) {
                            $result['violations'][] = $txt . '() before the gate and before this file loads incs/functions.inc.php (line ' . self::lineOf($tokens, $i) . ')';
                        }
                    } elseif ($result['gate'] === null && !in_array($name, self::SAFE_CALLS, true) && !in_array($name, self::GATE_CALLS, true)) {
                        $result['violations'][] = 'call to ' . $txt . '() before the gate (line ' . self::lineOf($tokens, $i) . ') -- not on the list of calls that cannot touch data';
                    }
                }
            }
            if ($isArr && in_array($id, [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE], true) && $result['gate'] === null) {
                $target = self::includeTarget($tokens, $i);
                if (strpos(self::statementText($tokens, $i), 'functions.inc.php') !== false) {
                    $functionsLoaded = true;
                }
                // A computed path is a library only in the three forms the application itself writes: the $_SESSION['fip'] ternary
                // (functions.inc.php), $_SESSION['fmp'] (functions_major*.inc.php, chosen at login) and $the_inc (a literal two-way ternary).
                $stmt = self::statementText($tokens, $i);
                $dynamicOk = ($target === '' && (strpos($stmt, 'functions.inc.php') !== false
                    || preg_match('/\$_SESSION\[\s*[\'"](fmp|fip)[\'"]\s*\]|\$the_inc\b/', $stmt) === 1));
                if (!$dynamicOk && !self::isLibraryInclude($target, true)) {
                    $result['violations'][] = 'include of ' . ($target === '' ? '<dynamic path>' : $target) . ' before the gate (line ' . self::lineOf($tokens, $i) . ')';
                }
            }
        }
        return $result;
    }

    /**
     * True when the first executable statement of the file is the canonical CLI-only guard:
     *     if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
     * (tolerating php_sapi_name() and whitespace/comment differences).
     */
    public static function hasCliGuard(array $tokens): bool
    {
        $sig = [];
        foreach ($tokens as $t) {
            if (is_array($t)) {
                if (in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG], true)) {
                    continue;
                }
                $sig[] = $t[1];
            } else {
                $sig[] = $t;
            }
            if (count($sig) > 40) {
                break;
            }
        }
        // The guard must be the FIRST statement (nothing, not even error_reporting(), may precede it),
        // and its block must end the request.
        $flat = strtolower(implode('', $sig));
        return (bool) preg_match('/^if\(php_sapi(?:_name\(\))?!==[\'"]cli[\'"]\)\{.{0,120}?(exit|die)/s', $flat);
    }

    /**
     * Token index of the do_login() call in:
     *     if (<condition mentioning logout>) { ... do_logout(); ... exit; } else { ... do_login(...); ... }
     * -1 when the file has no such statement. Both branches end the request or authenticate, so
     * nothing after the statement can run for an anonymous caller.
     */
    private static function findLogoutElseGate(array $tokens, array $inDefinition): int
    {
        $n = count($tokens);
        $depth = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($inDefinition[$i]) {
                continue;
            }
            $t = $tokens[$i];
            $txt = is_array($t) ? $t[1] : $t;
            if ($txt === '{') {
                $depth++;
            } elseif ($txt === '}') {
                $depth--;
            }
            if (!(is_array($t) && $t[0] === T_IF) || $depth !== 0) {
                continue;
            }
            // condition
            $k = $i + 1;
            while ($k < $n && self::txt($tokens[$k]) !== '(') {
                $k++;
            }
            $cond = '';
            $p = 0;
            for (; $k < $n; $k++) {
                $x = self::txt($tokens[$k]);
                if ($x === '(') {
                    $p++;
                } elseif ($x === ')') {
                    $p--;
                    if ($p === 0) {
                        break;
                    }
                }
                $cond .= strtolower($x);
            }
            if (strpos($cond, 'logout') === false) {
                continue;
            }
            // if-block
            $k++;
            while ($k < $n && self::txt($tokens[$k]) !== '{') {
                $k++;
            }
            $b = 0;
            $hasLogout = false;
            $hasExit = false;
            for (; $k < $n; $k++) {
                $x = $tokens[$k];
                $tx = self::txt($x);
                if ($tx === '{') {
                    $b++;
                } elseif ($tx === '}') {
                    $b--;
                    if ($b === 0) {
                        break;
                    }
                } elseif (is_array($x) && $x[0] === T_STRING && strtolower($x[1]) === 'do_logout') {
                    $hasLogout = true;
                } elseif (is_array($x) && $x[0] === T_EXIT) {
                    $hasExit = true;
                }
            }
            if (!$hasLogout || !$hasExit) {
                continue;
            }
            // else-block
            $k++;
            while ($k < $n && is_array($tokens[$k]) && in_array($tokens[$k][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                $k++;
            }
            if (!($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_ELSE)) {
                continue;
            }
            $b = 0;
            for (; $k < $n; $k++) {
                $x = $tokens[$k];
                $tx = self::txt($x);
                if ($tx === '{') {
                    $b++;
                } elseif ($tx === '}') {
                    $b--;
                    if ($b === 0) {
                        break;
                    }
                } elseif ($b >= 1 && is_array($x) && $x[0] === T_STRING && strtolower($x[1]) === 'do_login') {
                    return $k;
                }
            }
        }
        return -1;
    }

    private static function txt($t): string
    {
        return is_array($t) ? $t[1] : $t;
    }
    /** Token id of a constant that does not exist on every supported PHP version (-1 when absent). */
    private static function tokenId(string $name): int
    {
        return defined($name) ? (int) constant($name) : -1;
    }

    /** Library files a script may load before its gate: they define functions, they do not serve data. */
    private static function isLibraryInclude(string $target, bool $strict = false): bool
    {
        if ($target === '') {
            return !$strict; // computed include path: only the $_SESSION['fip'] ternary (checked by the caller) is a known library
        }
        $t = str_replace('\\', '/', $target);
        return (bool) preg_match('#(^|/)incs/[A-Za-z0-9_.\-]+\.php$#', $t)
            || (bool) preg_match('#(^|/)lib/[A-Za-z0-9_./\-]+\.php$#', $t)
            || (bool) preg_match('#\.inc\.php$#', $t)
            || (bool) preg_match('#(^|/)(config|path|settings)\.inc\.php$#', $t);
    }

    private static function includeTarget(array $tokens, int $i): string
    {
        $n = count($tokens);
        $out = '';
        for ($k = $i + 1; $k < $n; $k++) {
            $t = $tokens[$k];
            if ($t === ';') {
                break;
            }
            if (is_array($t)) {
                if ($t[0] === T_CONSTANT_ENCAPSED_STRING) {
                    $out .= trim($t[1], "'\"");
                } elseif ($t[0] === T_VARIABLE || $t[0] === T_STRING) {
                    // a variable or constant in the path: only the plain-literal part counts
                    if ($out === '' && $t[0] === T_VARIABLE) {
                        return '';
                    }
                }
            }
        }
        return $out;
    }

    /** Source text of the statement that starts at token $i (up to its semicolon). */
    private static function statementText(array $tokens, int $i): string
    {
        $out = '';
        for ($k = $i, $n = count($tokens); $k < $n; $k++) {
            $t = $tokens[$k];
            if ($t === ';') {
                break;
            }
            $out .= is_array($t) ? $t[1] : $t;
        }
        return $out;
    }

    private static function lineOf(array $tokens, int $i): int
    {
        for ($k = $i; $k >= 0; $k--) {
            if (is_array($tokens[$k])) {
                return (int) $tokens[$k][2];
            }
        }
        return 1;
    }

    /** @return array|string|null */
    private static function nextSignificant(array $tokens, int $i)
    {
        $n = count($tokens);
        for ($k = $i + 1; $k < $n; $k++) {
            $t = $tokens[$k];
            if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            return $t;
        }
        return null;
    }

    /** @return array|string|null */
    private static function prevSignificant(array $tokens, int $i)
    {
        for ($k = $i - 1; $k >= 0; $k--) {
            $t = $tokens[$k];
            if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            return $t;
        }
        return null;
    }
}
