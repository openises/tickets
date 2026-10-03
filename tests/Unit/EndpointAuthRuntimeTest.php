<?php
/**
 * Runtime proof that the endpoint gates work: a real PHP web server, a real MariaDB/MySQL database,
 * real HTTP requests. The static half is EndpointAuthTest; this one answers "does a stranger actually
 * get nothing, and does a logged-in user actually still get everything".
 *
 * Needs a database server it may create a scratch database on. Set, for example:
 *   TICKETS_TEST_DB_HOST=127.0.0.1  TICKETS_TEST_DB_PORT=3306  TICKETS_TEST_DB_USER=root  TICKETS_TEST_DB_PASS=...
 * The test creates its own database (tickets_endpoint_auth_<random>), copies the PHP files of this
 * checkout to a temporary directory, installs the schema there, starts `php -S` on a free port, and
 * removes all of it afterwards. It never touches an existing database. Without the variables (or
 * without CREATE DATABASE rights) every test is skipped, like the other database-backed suites.
 *
 * @since v3.44.7
 */

use PHPUnit\Framework\TestCase;

class EndpointAuthRuntimeTest extends TestCase
{
    private const MARKER_NAME = 'Zaphod Beeblebrox';
    private const MARKER_DESC = 'Heart of Gold evacuation EPTEST';
    private const ADMIN_USER = 'ep_admin_user';
    private const ADMIN_PASS = 'Adm1n-EP-test-pass';
    private const GUEST_USER = 'ep_guest_user';
    private const GUEST_PASS = 'Gu3st-EP-test-pass';

    /** @var ?string reason every test is skipped, or null when the environment is usable */
    private static $skip = 'not initialised';
    /** @var string */
    private static $dir = '';
    /** @var string */
    private static $dbName = '';
    /** @var ?mysqli */
    private static $db = null;
    /** @var resource|null */
    private static $proc = null;
    /** @var string */
    private static $base = '';
    /** @var string */
    private static $root = '';
    /** @var array<string,string> cookie jar of the helper client */
    private $cookies = [];

    // ================================================================ environment

    public static function setUpBeforeClass(): void
    {
        self::$root = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
        $host = getenv('TICKETS_TEST_DB_HOST');
        if ($host === false || $host === '') {
            self::$skip = 'set TICKETS_TEST_DB_HOST/PORT/USER/PASS to a database server that may create a scratch database';
            return;
        }
        if (!extension_loaded('mysqli')) {
            self::$skip = 'mysqli is not loaded';
            return;
        }
        mysqli_report(MYSQLI_REPORT_OFF);
        $port = (int) (getenv('TICKETS_TEST_DB_PORT') ?: 3306);
        $user = (string) (getenv('TICKETS_TEST_DB_USER') ?: 'root');
        $pass = (string) (getenv('TICKETS_TEST_DB_PASS') ?: '');
        $link = @new mysqli($host, $user, $pass, '', $port);
        if ($link->connect_errno) {
            self::$skip = 'cannot connect to the test database server: ' . $link->connect_error;
            return;
        }
        self::$dbName = 'tickets_endpoint_auth_' . bin2hex(random_bytes(4));
        if (!$link->query('CREATE DATABASE `' . self::$dbName . '` CHARACTER SET utf8mb4')) {
            self::$skip = 'cannot create the scratch database: ' . $link->error;
            return;
        }
        $link->select_db(self::$dbName);
        self::$db = $link;

        // 1. the application files, in a private copy that gets its own database configuration
        self::$dir = str_replace('\\', '/', sys_get_temp_dir()) . '/tickets_endpoint_auth_' . bin2hex(random_bytes(4));
        mkdir(self::$dir, 0777, true);
        self::copyPhpTree(self::$root, self::$dir);
        if (!is_dir(self::$dir . '/fonts') && is_dir(self::$root . '/fonts')) {
            mkdir(self::$dir . '/fonts', 0777, true);
            foreach (glob(self::$root . '/fonts/*') as $f) {
                @copy($f, self::$dir . '/fonts/' . basename($f));
            }
        }
        file_put_contents(
            self::$dir . '/incs/mysql.inc.php',
            "<?php\n\$mysql_host = " . var_export($host, true) . ";\n\$mysql_user = " . var_export($user, true)
            . ";\n\$mysql_passwd = " . var_export($pass, true) . ";\n\$mysql_db = " . var_export(self::$dbName, true)
            . ";\n\$mysql_prefix = '';\n\$mysql_port = " . (int) (getenv('TICKETS_TEST_DB_PORT') ?: 3306) . ";\n"
        );

        // 2. schema + seed + users + one incident/unit/facility with recognisable contents
        // hash_password() is already loaded from this checkout's incs/security.inc.php by the test bootstrap
        require self::$dir . '/incs/install_schema.inc.php';
        /** @var array<string,string> $INSTALL_SCHEMA_TABLES */
        /** @var array<int,string> $INSTALL_SCHEMA_SEED */
        foreach ($INSTALL_SCHEMA_TABLES as $name => $sql) {
            if (!$link->query($sql)) {
                self::$skip = "installing table $name failed: " . $link->error;
                return;
            }
        }
        foreach ($INSTALL_SCHEMA_SEED as $sql) {
            $link->query($sql);
        }
        $versionSrc = (string) file_get_contents(self::$dir . '/incs/versions.inc.php');
        $version = preg_match('/define\(\'TICKETS_CURRENT_VERSION\',\s*\'([^\']+)\'/', $versionSrc, $vm) ? $vm[1] : 'unknown';
        $link->query("DELETE FROM settings WHERE name = '_version'");
        $stmt = $link->prepare("INSERT INTO settings (name, value) VALUES ('_version', ?)");
        $stmt->bind_param('s', $version);
        $stmt->execute();
        $link->query("SET SESSION sql_mode = ''");
        $link->query('DELETE FROM user');
        foreach ([[1, self::ADMIN_USER, self::ADMIN_PASS, 0, 'Test Administrator'], [2, self::GUEST_USER, self::GUEST_PASS, 3, 'Test Guest']] as $u) {
            $hash = hash_password($u[2]);
            $stmt = $link->prepare("INSERT INTO user (id, user, passwd, info, level, status, open_at, sort_desc, reporting) VALUES (?, ?, ?, ?, ?, 'approved', 'd', 1, 0)");
            $stmt->bind_param('isssi', $u[0], $u[1], $hash, $u[4], $u[3]);
            $stmt->execute();
        }
        $name = self::MARKER_NAME;
        $desc = self::MARKER_DESC;
        $link->query("INSERT INTO ticket (id, in_types_id, contact, street, city, state, phone, lat, lng, date, problemstart, scope, description, comments, status, severity, updated, `_by`, owner)
            VALUES (1, 1, '" . $link->real_escape_string($name) . "', '100 Main St', 'Springfield', 'MN', '5550142', 44.97, -93.26, NOW(), NOW(), 'EPTEST incident', '" . $link->real_escape_string($desc) . "', 'comment EPTEST', 2, 1, NOW(), 1, 1)");
        $link->query("INSERT INTO responder (id, name, handle, description, contact_via, phone, un_status_id, cellphone, updated, user_id, mobile, multi, lat, lng, type)
            VALUES (1, 'Engine EPTEST', 'EPT', 'unit EPTEST', 'ept@example.org', '5559876543', 1, '5559876543', NOW(), 1, 1, 0, 44.97, -93.26, 1)");
        $link->query("INSERT INTO facilities (id, name, handle, description, street, city, state, status_id, contact_name, contact_phone, contact_email, security_phone, lat, lng, type, updated, user_id, `_by`)
            VALUES (1, 'EPTEST Hospital', 'EPH', 'facility EPTEST', '1 Hospital Way', 'Springfield', 'MN', 1, 'Dr EPTEST', '5550003333', 'dr@example.org', '5550002222', 44.98, -93.27, 1, NOW(), 1, 1)");
        $link->query("INSERT INTO action (ticket_id, date, description, user, action_type, responder, updated) VALUES (1, NOW(), 'action note EPTEST', 1, 1, 0, NOW())");

        // 3. the web server
        $sock = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if (!$sock) {
            self::$skip = "no free port: $errstr";
            return;
        }
        $port = (int) substr(strrchr(stream_socket_get_name($sock, false), ':'), 1);
        fclose($sock);
        @mkdir(self::$dir . '/_sessions');
        $args = [PHP_BINARY, '-d', 'mysqli.default_port=' . (int) (getenv('TICKETS_TEST_DB_PORT') ?: 3306), '-d', 'display_errors=0',
            // php-cli defaults to NO memory limit; every web SAPI has one (128M is the php.ini default). Without a
            // cap here, one runaway request can take the whole runner down with it (that is what ended the first
            // GitHub run of this test), so the test server gets a limit like the real thing.
            '-d', 'memory_limit=512M',
            '-d', 'log_errors=1', '-d', 'error_log=' . self::$dir . '/php-error.log', '-d', 'session.save_path=' . self::$dir . '/_sessions',
            '-S', "127.0.0.1:$port", '-t', self::$dir];
        $descriptors = [0 => ['file', (DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null'), 'r'], 1 => ['file', self::$dir . '/server.log', 'a'], 2 => ['file', self::$dir . '/server.log', 'a']];
        self::$proc = proc_open($args, $descriptors, $pipes, self::$dir, null, ['bypass_shell' => true]);
        if (!is_resource(self::$proc)) {
            self::$skip = 'could not start php -S';
            return;
        }
        self::$base = "http://127.0.0.1:$port";
        $up = false;
        for ($i = 0; $i < 60; $i++) {
            $c = @fsockopen('127.0.0.1', $port, $e1, $e2, 0.2);
            if ($c) {
                fclose($c);
                $up = true;
                break;
            }
            usleep(150000);
        }
        if (!$up) {
            self::$skip = 'php -S did not start';
            return;
        }
        self::$skip = null;
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$proc)) {
            $status = proc_get_status(self::$proc);
            if (DIRECTORY_SEPARATOR === '\\' && !empty($status['pid'])) {
                @exec('taskkill /T /F /PID ' . (int) $status['pid'] . ' > NUL 2>&1');
            }
            @proc_terminate(self::$proc);
            @proc_close(self::$proc);
        }
        if (self::$db instanceof mysqli && self::$dbName !== '') {
            @self::$db->query('DROP DATABASE `' . self::$dbName . '`');
            @self::$db->close();
        }
        if (self::$dir !== '' && is_dir(self::$dir)) {
            self::removeTree(self::$dir);
        }
    }

    protected function setUp(): void
    {
        if (self::$skip !== null) {
            $this->markTestSkipped('SKIP: ' . self::$skip);
        }
        $this->cookies = [];
    }

    /** Copies every .php file: the application is all PHP; images, scripts and docs are not needed to answer HTTP. */
    private static function copyPhpTree(string $from, string $to): void
    {
        $from = str_replace('\\', '/', $from);
        $skipDirs = ['.git', '.github', 'vendor', 'node_modules'];
        $it = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
            static function ($f, $k, $iter) use ($skipDirs) {
                if ($iter->hasChildren()) {
                    return !in_array($f->getFilename(), $skipDirs, true);
                }
                return strtolower($f->getExtension()) === 'php';
            }
        ));
        foreach ($it as $f) {
            $rel = ltrim(substr(str_replace('\\', '/', $f->getPathname()), strlen($from)), '/');
            if (strpos($rel, 'tests/Unit/') === 0) {
                continue;
            }
            $dest = $to . '/' . $rel;
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0777, true);
            }
            copy($f->getPathname(), $dest);
        }
    }

    private static function removeTree(string $dir): void
    {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($dir);
    }

    // ================================================================ tiny HTTP client

    /**
     * @param array<string,string> $post
     * @return array{status:int, headers:array<int,string>, body:string, setcookie:bool}
     */
    private function http(string $method, string $path, array $post = [], bool $withCookies = true): array
    {
        $headers = ['Connection: close'];
        if ($withCookies && $this->cookies) {
            $pairs = [];
            foreach ($this->cookies as $k => $v) {
                $pairs[] = "$k=$v";
            }
            $headers[] = 'Cookie: ' . implode('; ', $pairs);
        }
        $opts = ['method' => $method, 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 90];
        if ($method === 'POST') {
            $body = http_build_query($post);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Content-Length: ' . strlen($body);
            $opts['content'] = $body;
        }
        $opts['header'] = implode("\r\n", $headers);
        $ctx = stream_context_create(['http' => $opts]);
        $http_response_header = [];
        $out = @file_get_contents(self::$base . '/' . ltrim($path, '/'), false, $ctx);
        $status = 0;
        if ($out === false && empty($http_response_header) && is_resource(self::$proc) && !proc_get_status(self::$proc)['running']) {
            // Nothing answered because the test web server is gone. Say so and show why, instead of letting every
            // later request come back as "HTTP 0" and fail a dozen unrelated assertions.
            $log = @file_get_contents(self::$dir . '/server.log');
            throw new \RuntimeException('the test web server exited while serving ' . $method . ' ' . $path . "\n--- server.log (tail) ---\n"
                . substr((string) $log, -1500) . "\n--- php-error.log (tail) ---\n" . substr((string) @file_get_contents(self::$dir . '/php-error.log'), -1500));
        }
        $setcookie = false;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) {
                $status = (int) $m[1];
            } elseif (stripos($h, 'Set-Cookie:') === 0) {
                $setcookie = true;
                if (preg_match('/^Set-Cookie:\s*([^=;\s]+)=([^;]*)/i', $h, $m)) {
                    $this->cookies[$m[1]] = $m[2];
                }
            }
        }
        return ['status' => $status, 'headers' => $http_response_header ?? [], 'body' => $out === false ? '' : $out, 'setcookie' => $setcookie];
    }

    private function login(string $user, string $pass): void
    {
        $this->cookies = [];
        self::$db->query("UPDATE user SET sid = NULL WHERE user = '" . self::$db->real_escape_string($user) . "'");
        $page = $this->http('GET', 'main.php');
        $this->assertSame(200, $page['status'], 'login page');
        $this->assertSame(1, preg_match('/name=["\']csrf_token["\'][^>]*value=["\']([^"\']+)/i', $page['body'], $m), 'login page carries a CSRF token');
        $r = $this->http('POST', 'main.php', ['frm_user' => $user, 'frm_passwd' => $pass, 'csrf_token' => $m[1], 'frm_maps' => 'show',
            'frm_daynight' => 'Day', 'scr_height' => '900', 'scr_width' => '1400', 'no_autoforward' => '0', 'frm_referer' => '']);
        $this->assertContains($r['status'], [200, 302], 'login POST');
        $row = self::$db->query("SELECT sid FROM user WHERE user = '" . self::$db->real_escape_string($user) . "'")->fetch_assoc();
        $this->assertNotEmpty($row['sid'], "login as $user did not establish a session (database shows no sid)");
    }

    private static function isLoginForm(string $body): bool
    {
        // Both the page login and the mobile login render <FORM ... NAME="login_form">. (Some pages merely contain a
        // frm_passwd input -- the user editor, the portal profile -- which is not a login form.)
        return (bool) preg_match('/NAME\s*=\s*["\']login_form["\']/i', $body);
    }

    // ================================================================ inventory access

    /** @return array<string,array{class:string,reason:string}> */
    private static function inventory(): array
    {
        $out = [];
        $doc = file_get_contents(self::$root . '/docs/security/legacy-endpoint-auth-inventory.md');
        foreach (preg_split('/\R/', (string) $doc) as $line) {
            if (preg_match('/^\|\s*`([^`]+)`\s*\|\s*([A-Z]+)\s*\|\s*([a-z\/]+)\s*\|\s*(.+?)\s*\|\s*$/', $line, $m)) {
                $out[$m[1]] = ['class' => $m[2], 'reason' => $m[4]];
            }
        }
        return $out;
    }

    // ================================================================ anonymous: nothing

    public function testTheReportedLeakIsClosed(): void
    {
        // Before 3.44.7 this returned the incident's contact name, phone, description and actions to anyone.
        $r = $this->http('GET', 'ajax/reports.php?report=l&tick_sel=1', [], false);
        $this->assertSame(401, $r['status']);
        $this->assertSame('Authentication required.', trim($r['body']));
        foreach ([self::MARKER_NAME, self::MARKER_DESC, '5550142', 'EPTEST'] as $leak) {
            $this->assertStringNotContainsString($leak, $r['body']);
        }
    }

    public function testControlTheSeededDataIsReallyThereForALoggedInUser(): void
    {
        // Without this the tests below could pass because the database is simply empty.
        $this->login(self::ADMIN_USER, self::ADMIN_PASS);
        $r = $this->http('GET', 'ajax/reports.php?report=l&tick_sel=1');
        $this->assertSame(200, $r['status']);
        $this->assertStringContainsString(self::MARKER_NAME, $r['body']);
        $this->assertStringContainsString(self::MARKER_DESC, $r['body']);
    }

    /**
     * Scripts that die with a PHP fatal error before they print or read anything, for EVERY caller, logged in
     * or not, identically in 3.44.6 (checked against that tree): a relative include path that cannot resolve
     * from their own directory (the fragments are only ever included by a page that sits in the root), or a
     * function declared twice. They serve nothing to anybody, so a visitor gets nothing. Pinned here so that
     * fixing one makes this test ask for a real 401/login form instead. Their gate sits after the include
     * that fails; the static test still proves it is there.
     */
    private const FATAL_FOR_EVERYONE = [
        'ajax/lines_a.php', 'ajax/member_capabilities.php', 'ajax/update_assigns.php', 'resp_popup.php', 'portal/ajax/download.php',
        'portal/new_request.php', 'incs/addrlkup.php', 'incs/phonelkup.php', 'icons/buttons/landb.php', 'db_loader.php',
        'forms/edit_form.php', 'forms/view_form.php', 'forms/mobile_screen.php', 'forms/mmarkup_add_screen.php', 'forms/mi_view_screen.php',
        'forms/mi_view_screen_NM.php', 'forms/reports_print_screen.php', 'forms/sit_screen.php', 'forms/sit_screen_NM.php',
        'forms/wl_screen.php', 'forms/wl_screen_NM.php',
    ];

    /** Pages that deliberately end the request silently, before the gate, when there is no session at all ("kill this window"). */
    private const SILENT_WITHOUT_SESSION = ['action_w.php', 'track_u.php'];

    public function testAnonymousRequestsToEveryGatedEndpointGetNothing(): void
    {
        $problems = [];
        $n = 0;
        foreach (self::inventory() as $file => $row) {
            if ($row['class'] !== 'LOGIN' && $row['class'] !== 'GATED') {
                continue;
            }
            // "?x=1": several endpoints return an empty page when $_GET is empty; give the gate something to guard.
            foreach (['GET', 'POST'] as $method) {
                $n++;
                $r = $this->http($method, $file . '?x=1', $method === 'POST' ? ['x' => '1'] : [], false);
                $leak = '';
                foreach ([self::MARKER_NAME, self::MARKER_DESC, '5550142', 'EPTEST', self::ADMIN_USER, self::GUEST_USER] as $needle) {
                    if (strpos($r['body'], $needle) !== false) {
                        $leak = " LEAKS '$needle'";
                    }
                }
                if (in_array($file, self::FATAL_FOR_EVERYONE, true)) {
                    if (!in_array($r['status'], [500, 302], true) || $r['body'] !== '') {
                        $problems[] = "$method $file is listed as fatal-for-everyone but answered HTTP {$r['status']}, " . strlen($r['body']) . ' bytes';
                    }
                    continue;
                }
                if (in_array($file, self::SILENT_WITHOUT_SESSION, true)) {
                    if ($r['status'] !== 200 || $r['body'] !== '') {
                        $problems[] = "$method $file should end silently without a session but answered HTTP {$r['status']}, " . strlen($r['body']) . ' bytes';
                    }
                    continue;
                }
                $okData = ($r['status'] === 401 && trim($r['body']) === 'Authentication required.');
                // a page: the login form, either directly or after a redirect to index.php (add.php, edit.php ... do both)
                $okForm = (in_array($r['status'], [200, 302], true) && self::isLoginForm($r['body']));
                if ($row['class'] === 'LOGIN' ? !$okForm : !($okData || $okForm)) {
                    $problems[] = "$method $file -> HTTP {$r['status']}, " . strlen($r['body']) . ' bytes' . $leak . ' (' . substr(preg_replace('/\s+/', ' ', strip_tags($r['body'])), 0, 60) . ')';
                } elseif ($leak !== '') {
                    $problems[] = "$method $file$leak";
                }
            }
        }
        $this->assertGreaterThan(1000, $n, 'expected 500+ gated endpoints x 2 methods');
        $this->assertSame([], $problems, "anonymous requests that were not refused:\n  " . implode("\n  ", array_slice($problems, 0, 40)));
    }

    public function testAVisitorWithoutASessionCookieIsRefusedWithoutCreatingASession(): void
    {
        $r = $this->http('GET', 'ajax/check_memory.php', [], false);
        $this->assertSame(401, $r['status']);
        $this->assertSame('Authentication required.', trim($r['body']));
        $this->assertFalse($r['setcookie'], 'no session (and no cookie) is created for a visitor who has none');
        $this->assertStringContainsString('text/plain', implode("\n", $r['headers']));
        $this->assertStringNotContainsString('WWW-Authenticate', implode("\n", $r['headers']), 'a 401 here must not pop the browser basic-auth dialog');
    }

    public function testLibraryIncludesAndStaticFilesAnswerAVisitorWithNothingOfTheApplication(): void
    {
        // INCLUDE rows promise "prints nothing when requested directly". Ask each of them, without a session.
        $problems = [];
        $n = 0;
        foreach (self::inventory() as $file => $row) {
            if ($row['class'] !== 'INCLUDE' || !is_file(self::$dir . '/' . $file)) {
                continue;
            }
            $n++;
            $r = $this->http('GET', $file . '?x=1', [], false);
            if (trim($r['body']) !== '') {        // a stray newline after a closing tag is not content
                $problems[] = "$file -> HTTP {$r['status']}, " . strlen($r['body']) . ' bytes: ' . substr(preg_replace('/\s+/', ' ', strip_tags($r['body'])), 0, 60);
            }
            foreach ([self::MARKER_NAME, self::ADMIN_USER, 'EPTEST'] as $needle) {
                if (strpos($r['body'], $needle) !== false) {
                    $problems[] = "$file LEAKS '$needle'";
                }
            }
        }
        $this->assertGreaterThan(60, $n, 'expected the library includes to be present in the private copy');
        $this->assertSame([], $problems, "library includes that answered a visitor with content:\n  " . implode("\n  ", array_slice($problems, 0, 30)));
    }

    public function testTheTopFrameShownAboveTheLoginFormEmbedsNoUserNamesOrUnits(): void
    {
        self::$db->query("DELETE FROM settings WHERE name IN ('broadcast', 'internet')");
        self::$db->query("INSERT INTO settings (name, value) VALUES ('broadcast', '1'), ('internet', '1')");
        $anon = $this->http('GET', 'top.php', [], false);
        $this->assertSame(200, $anon['status']);
        $this->assertStringNotContainsString(self::ADMIN_USER, $anon['body'], 'user names must not reach a visitor');
        $this->assertStringNotContainsString(self::GUEST_USER, $anon['body']);
        $this->assertMatchesRegularExpression('/var resps = \[\];/', $anon['body'], 'the unit table must be empty for a visitor');
        // positive control: the same page for a logged-in user does carry them, so the check above is not vacuous
        $this->login(self::ADMIN_USER, self::ADMIN_PASS);
        $in = $this->http('GET', 'top.php');
        $this->assertStringContainsString(self::ADMIN_USER, $in['body'], 'control: socket2me embeds user names for a logged-in user');
    }

    // ================================================================ command line only

    public function testCommandLineScriptsAreNotServed(): void
    {
        $problems = [];
        foreach (self::inventory() as $file => $row) {
            if ($row['class'] !== 'CLI' && $row['class'] !== 'TOOL') {
                continue;
            }
            $r = $this->http('GET', $file, [], false);
            if ($r['status'] !== 404 || trim($r['body']) !== '') {
                $problems[] = "$file -> HTTP {$r['status']}, " . strlen($r['body']) . ' bytes';
            }
        }
        $this->assertSame([], $problems);
    }

    public function testTheAdminPasswordResetToolIsOffUntilTheAdministratorSwitchesItOnAndOnlyOnce(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('SKIP: the reset tool connects with PDO and pdo_mysql is not loaded');
        }
        $tool = 'tools/reset-admin-password.php';
        $enable = self::$dir . '/tools/reset-admin-password.enable';
        $this->assertSame(404, $this->http('GET', $tool, [], false)['status']);
        $before = self::$db->query('SELECT passwd FROM user WHERE id = 2')->fetch_assoc()['passwd'];
        $this->assertSame(404, $this->http('POST', $tool, ['user_id' => '2', 'new_password' => 'attacker-chosen'], false)['status']);
        $this->assertSame($before, self::$db->query('SELECT passwd FROM user WHERE id = 2')->fetch_assoc()['passwd'], 'the refused request changed nothing');

        file_put_contents($enable, '');                                   // the administrator proves control of the server
        $page = $this->http('GET', $tool, [], false);
        $this->assertSame(200, $page['status'], 'with the enable file the page is available');
        $this->assertStringContainsString(self::ADMIN_USER, $page['body'], 'it lists the administrator accounts');
        $r = $this->http('POST', $tool, ['user_id' => '2', 'new_password' => 'Reset-by-owner-1'], false);
        $this->assertSame(200, $r['status']);
        $after = self::$db->query('SELECT passwd FROM user WHERE id = 2')->fetch_assoc()['passwd'];
        $this->assertNotSame($before, $after, 'the reset ran');
        self::$db->query("UPDATE user SET passwd = '" . self::$db->real_escape_string($before) . "' WHERE id = 2");   // put the guest back for the other tests
        $this->assertFileDoesNotExist($enable, 'a successful reset switches the page off again');
        $this->assertSame(404, $this->http('GET', $tool, [], false)['status']);
    }

    // ================================================================ public entry points still work

    public function testThePublicEntryPointsStillServeVisitors(): void
    {
        $r = $this->http('GET', 'index.php', [], false);
        $this->assertSame(200, $r['status']);
        $this->assertStringContainsString('main.php', $r['body'], 'frameset names the login frame');
        $this->assertStringContainsString('top.php', $r['body']);

        $login = $this->http('GET', 'main.php', [], false);
        $this->assertSame(200, $login['status']);
        $this->assertTrue(self::isLoginForm($login['body']), 'visitors get the login form');

        $css = $this->http('GET', 'stylesheet.php', [], false);
        $this->assertSame(200, $css['status']);
        $this->assertStringContainsString('BODY', $css['body'], 'the login page needs its stylesheet');

        $contact = $this->http('GET', 'contact.php', [], false);
        $this->assertSame(200, $contact['status'], 'the public request-access form');
        $this->assertStringNotContainsString(self::MARKER_NAME, $contact['body']);

        $confirm = $this->http('GET', 'contact_response.php?nx=' . base64_encode('nobody') . '&ex=' . base64_encode('x@example.org') . '&sx=' . base64_encode('wrong'), [], false);
        $this->assertSame(200, $confirm['status']);
        $this->assertStringContainsString('problem', $confirm['body'], 'a wrong confirmation secret is refused');

        $rss = $this->http('GET', 'rss/road_conditions.php', [], false);
        $this->assertSame(200, $rss['status']);
        $this->assertStringContainsString('<rss', $rss['body']);
        $this->assertStringNotContainsString(self::MARKER_NAME, $rss['body']);

        if (function_exists('gd_info')) {
            $img = $this->http('GET', 'ajax/create_image.php?string=AB12', [], false);
            $this->assertSame(200, $img['status'], 'the captcha image of the public contact form');
        }
    }

    public function testTheMobilePageShowsItsLoginFormBeforeAnyDataWhenForcedLoginIsOn(): void
    {
        self::$db->query("DELETE FROM settings WHERE name = 'responder_mobile_forcelogin'");
        self::$db->query("INSERT INTO settings (name, value) VALUES ('responder_mobile_forcelogin', '1')");
        $r = $this->http('GET', 'rm/index.php', [], false);
        $this->assertSame(200, $r['status']);
        $this->assertTrue(self::isLoginForm($r['body']), 'mobile visitors get the mobile login form');
        $this->assertStringNotContainsString('EPTEST', $r['body']);
        foreach (['rm/forms/mapindex.php', 'rm/forms/nomapindex.php'] as $fragment) {
            $f = $this->http('GET', $fragment, [], false);
            $this->assertSame(401, $f['status'], "$fragment without a session while login is forced");
        }
    }

    public function testTheInstallerRefusesAnonymousWritesOnAnInstalledSystem(): void
    {
        // The installer is PUBLIC with a gate of its own (b6b5ca6/929e8f8). Pin that it still holds.
        $post = ['action' => 'execute_stream', 'db_host' => (string) getenv('TICKETS_TEST_DB_HOST'), 'db_user' => (string) (getenv('TICKETS_TEST_DB_USER') ?: 'root'),
            'db_pass' => (string) (getenv('TICKETS_TEST_DB_PASS') ?: ''), 'db_name' => self::$dbName, 'db_prefix' => ''];
        $r = $this->http('POST', 'install.php', $post, false);
        $this->assertSame(401, $r['status']);
        $this->assertSame(1, (int) self::$db->query('SELECT COUNT(*) c FROM ticket')->fetch_assoc()['c'], 'nothing was wiped');
    }

    // ================================================================ logged in: everything still works

    public function testALoggedInUserIsNotLockedOutOfAnyGatedEndpoint(): void
    {
        $this->login(self::ADMIN_USER, self::ADMIN_PASS);
        // endpoints that call out to the internet or rewrite the whole database are left to the static test
        $skip = ['ajax/dedupe.php', 'ajax/optimize_table.php', 'ajax/gettiles.php', 'ajax/deltile.php', 'ajax/deltiles.php', 'tile_proxy.php',
            'ajax/update_localmaps.php', 'ajax/update_localmap_boundary.php', 'ajax/empty_wastebasket.php', 'ajax/get_addrs.php',
            'get_all_emails.php', 'get_messages.php', 'ajax/get_messages.php', 'do_send.php', 'do_send_mail.php', 'sitemap/gwsitemap.php'];
        $problems = [];
        $n = 0;
        foreach (self::inventory() as $file => $row) {
            if (($row['class'] !== 'LOGIN' && $row['class'] !== 'GATED') || in_array($file, $skip, true) || strpos($row['reason'], 'outbound') !== false) {
                continue;
            }
            $n++;
            $r = $this->http('GET', $file);
            if ($r['status'] === 401 || self::isLoginForm($r['body'])) {
                $problems[] = "$file -> HTTP {$r['status']}" . (self::isLoginForm($r['body']) ? ' (login form)' : '') . ' for a valid session';
            }
        }
        $this->assertGreaterThan(400, $n);
        $this->assertSame([], $problems, "valid sessions that a gate refused:\n  " . implode("\n  ", array_slice($problems, 0, 40)));
    }

    public function testTheReportsAndTheirDownloadsWorkForALoggedInUser(): void
    {
        $this->login(self::ADMIN_USER, self::ADMIN_PASS);
        $title = base64_encode('Incident log');

        $ajax = $this->http('GET', 'ajax/reports.php?report=l&tick_sel=1');
        $this->assertSame(200, $ajax['status']);
        $this->assertStringContainsString(self::MARKER_NAME, $ajax['body']);

        $doc = $this->http('GET', "download_report.php?report=l&ticksel=1&mode=doc&title=$title");
        $this->assertSame(200, $doc['status']);
        $this->assertStringContainsString('application/msword', implode("\n", $doc['headers']));
        $this->assertStringContainsString('Incident_log.doc', implode("\n", $doc['headers']));
        $this->assertStringContainsString(self::MARKER_NAME, $doc['body'], 'download_report.php still delivers the report');

        $xls = $this->http('GET', "download_report.php?report=l&ticksel=1&mode=xls&title=$title");
        $this->assertStringContainsString('application/vnd.ms-excel', implode("\n", $xls['headers']));
        $this->assertStringContainsString(self::MARKER_NAME, $xls['body']);

        $csv = $this->http('GET', "download_report_csv.php?report=l&ticksel=1&title=$title");
        $this->assertSame(200, $csv['status']);
        $this->assertStringContainsString('text/csv', implode("\n", $csv['headers']));
        $this->assertStringContainsString(self::MARKER_DESC, $csv['body'], 'download_report_csv.php still delivers the report');

        // ...and the same three, without the session, deliver nothing
        $this->cookies = [];
        foreach (["download_report.php?report=l&ticksel=1&mode=doc&title=$title", "download_report_csv.php?report=l&ticksel=1&title=$title"] as $url) {
            $r = $this->http('GET', $url, [], false);
            $this->assertSame(401, $r['status'], $url);
            $this->assertStringNotContainsString(self::MARKER_NAME, $r['body']);
        }
    }

    public function testAnyLoggedInAccountPassesIncludingGuest(): void
    {
        $this->login(self::GUEST_USER, self::GUEST_PASS);
        $r = $this->http('GET', 'ajax/check_memory.php');
        $this->assertSame(200, $r['status'], 'documented: the gate asks for a login, not for a level');
    }

    public function testASessionWhoseLoginHasExpiredIsRefused(): void
    {
        $this->login(self::ADMIN_USER, self::ADMIN_PASS);
        $this->assertSame(200, $this->http('GET', 'ajax/check_memory.php')['status']);
        self::$db->query("UPDATE user SET expires = '2001-01-01 00:00:00' WHERE user = '" . self::ADMIN_USER . "'");
        $data = $this->http('GET', 'ajax/check_memory.php');
        $this->assertSame(401, $data['status'], 'an expired login is not authenticated');
        $this->assertSame('Authentication required.', trim($data['body']));
        $page = $this->http('GET', 'about.php');
        $this->assertTrue(self::isLoginForm($page['body']), 'an expired login gets the login form on a page');
    }

    public function testASessionForAUserThatNoLongerExistsIsRefused(): void
    {
        $this->login(self::GUEST_USER, self::GUEST_PASS);
        $this->assertSame(200, $this->http('GET', 'ajax/check_memory.php')['status']);
        self::$db->query("DELETE FROM user WHERE user = '" . self::GUEST_USER . "'");
        $this->assertSame(401, $this->http('GET', 'ajax/check_memory.php')['status']);
        // restore for later tests
        $hash = hash_password(self::GUEST_PASS);
        $stmt = self::$db->prepare("INSERT INTO user (id, user, passwd, info, level, status, open_at, sort_desc, reporting) VALUES (2, ?, ?, 'Test Guest', 3, 'approved', 'd', 1, 0)");
        $u = self::GUEST_USER;
        $stmt->bind_param('ss', $u, $hash);
        $stmt->execute();
    }

    public function testASessionThatKeepsPollingStaysLoggedInButItIsNotRewrittenOnEveryRequest(): void
    {
        $this->login(self::ADMIN_USER, self::ADMIN_PASS);
        $tz = self::$db->query("SELECT value FROM settings WHERE name = 'timezone'")->fetch_assoc()['value'] ?? '';
        $timeout = (int) (self::$db->query("SELECT value FROM settings WHERE name = 'session_timeout'")->fetch_assoc()['value'] ?? 0);
        $timeout = $timeout > 0 ? $timeout : 60;
        $delta = (int) (self::$db->query("SELECT value FROM settings WHERE name = 'delta_mins'")->fetch_assoc()['value'] ?? 0);
        $prevTz = date_default_timezone_get();
        date_default_timezone_set($tz !== '' ? $tz : 'America/New_York');
        try {
            $appNow = time() - $delta * 60;
            $fmt = static function ($ts) { return date('Y-m-d H:i:s', $ts); };
            $get = static function () {
                return strtotime(self::$db->query("SELECT expires FROM user WHERE user = '" . self::ADMIN_USER . "'")->fetch_assoc()['expires']);
            };
            // almost over: a pass pushes it out to a full timeout
            self::$db->query("UPDATE user SET expires = '" . $fmt($appNow + 120) . "' WHERE user = '" . self::ADMIN_USER . "'");
            $this->assertSame(200, $this->http('GET', 'ajax/check_memory.php')['status']);
            $this->assertGreaterThan($appNow + ($timeout * 60) - 300, $get(), 'a nearly-expired login that is still polling is extended');
            // plenty left (more than half): left alone, no write per request
            $keep = $appNow + ($timeout * 60) - 60;
            self::$db->query("UPDATE user SET expires = '" . $fmt($keep) . "' WHERE user = '" . self::ADMIN_USER . "'");
            $this->assertSame(200, $this->http('GET', 'ajax/check_memory.php')['status']);
            $this->assertSame($keep, $get(), 'a login with more than half its time left is not rewritten');
        } finally {
            date_default_timezone_set($prevTz);
        }
    }
}
