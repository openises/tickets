<?php
/**
 * TicketsCAD PHP Compatibility Layer
 *
 * Polyfills functions removed or deprecated across PHP versions.
 * Include this FIRST — before any other code runs.
 *
 * Supports: PHP 7.0 through 8.4+
 *
 * Changes by PHP version:
 *   PHP 8.4: Implicit nullable parameters deprecated
 *   PHP 8.2: utf8_encode/utf8_decode REMOVED, ${var} interpolation deprecated
 *   PHP 8.1: strftime() deprecated, FILTER_SANITIZE_STRING removed
 *   PHP 8.0: each() REMOVED, create_function() REMOVED, stricter type juggling
 *   PHP 7.4: array_key_exists() on objects deprecated
 *   PHP 7.2: each() deprecated, create_function() deprecated
 *
 * @package TicketsCAD
 * @since   v3.44.2
 */

// ═══════════════════════════════════════════════════════════════
//  PHP 8.2+ — utf8_encode / utf8_decode REMOVED
//  These were thin wrappers for ISO-8859-1 ↔ UTF-8 conversion.
//  Polyfill using mb_convert_encoding or manual byte conversion.
// ═══════════════════════════════════════════════════════════════

if (!function_exists('utf8_encode')) {
    function utf8_encode($string) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        // Manual fallback: convert each byte > 0x7F to UTF-8 two-byte sequence
        $output = '';
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($string[$i]);
            if ($byte < 0x80) {
                $output .= $string[$i];
            } else {
                $output .= chr(0xC0 | ($byte >> 6)) . chr(0x80 | ($byte & 0x3F));
            }
        }
        return $output;
    }
}

if (!function_exists('utf8_decode')) {
    function utf8_decode($string) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
        }
        // Manual fallback
        $output = '';
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($string[$i]);
            if ($byte < 0x80) {
                $output .= $string[$i];
            } elseif (($byte & 0xE0) === 0xC0) {
                $next = ($i + 1 < $len) ? ord($string[++$i]) : 0;
                $codepoint = (($byte & 0x1F) << 6) | ($next & 0x3F);
                $output .= ($codepoint < 256) ? chr($codepoint) : '?';
            } else {
                // 3-byte or 4-byte sequences map to '?' in ISO-8859-1
                if (($byte & 0xF0) === 0xE0) { $i += 2; }
                elseif (($byte & 0xF8) === 0xF0) { $i += 3; }
                $output .= '?';
            }
        }
        return $output;
    }
}

// ═══════════════════════════════════════════════════════════════
//  PHP 8.1+ — FILTER_SANITIZE_STRING removed
// ═══════════════════════════════════════════════════════════════

if (!defined('FILTER_SANITIZE_STRING')) {
    define('FILTER_SANITIZE_STRING', FILTER_DEFAULT);
}

// ═══════════════════════════════════════════════════════════════
//  PHP 8.0+ — each() REMOVED
//  Used by very old code to iterate arrays. Replace with foreach
//  where possible, but polyfill for code we can't easily change.
// ═══════════════════════════════════════════════════════════════

if (!function_exists('each')) {
    function each(&$array) {
        $value = current($array);
        $key = key($array);
        if ($key === null) {
            return false;
        }
        next($array);
        return array(1 => $value, 'value' => $value, 0 => $key, 'key' => $key);
    }
}

// ═══════════════════════════════════════════════════════════════
//  PHP 8.0+ — create_function() REMOVED
//  Polyfill wraps eval() — not ideal but maintains BC.
//  New code should never use this.
//  SECURITY: eval() here is inherent to the create_function() API.
//  $args and $code come from internal callers only (no user input).
//  No current call sites exist — this polyfill is kept for BC safety.
// ═══════════════════════════════════════════════════════════════

if (!function_exists('create_function')) {
    function create_function($args, $code) {
        static $counter = 0;
        $funcName = '__compat_lambda_' . (++$counter);
        eval("function {$funcName}({$args}) { {$code} }"); // NOSONAR — BC polyfill, no user input reaches here
        return $funcName;
    }
}

// ═══════════════════════════════════════════════════════════════
//  PHP 8.1+ — strftime() DEPRECATED (E_DEPRECATED warnings)
//  Many legacy date formatting calls use strftime. Suppress the
//  deprecation warning since the function still works through 8.x.
// ═══════════════════════════════════════════════════════════════

// We don't polyfill strftime() because it still works in PHP 8.x,
// just throws E_DEPRECATED. The error_reporting below handles it.
// A full replacement would require IntlDateFormatter which may not
// be available on all systems.

// ═══════════════════════════════════════════════════════════════
//  PHP 7.2+ — Suppress deprecation notices from legacy code
//  This prevents E_DEPRECATED from breaking JSON output or causing
//  500 errors when the error handler is strict.
// ═══════════════════════════════════════════════════════════════

// Only suppress deprecation notices — all real errors still reported
$currentLevel = error_reporting();
if ($currentLevel & E_DEPRECATED) {
    error_reporting($currentLevel & ~E_DEPRECATED);
}

// ═══════════════════════════════════════════════════════════════
//  PHP 8.0+ — str_contains / str_starts_with / str_ends_with
//  These are useful new functions. Polyfill for PHP < 8.0.
// ═══════════════════════════════════════════════════════════════

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

// ═══════════════════════════════════════════════════════════════
//  PHP 8.0+ — Stricter error handling for null parameters
//  Many legacy functions pass null where string is expected.
//  Set up a custom error handler to silently convert these.
// ═══════════════════════════════════════════════════════════════

if (PHP_MAJOR_VERSION >= 8) {
    $previousHandler = set_error_handler(function ($severity, $message, $file, $line) use (&$previousHandler) {
        // Suppress "passing null to non-nullable parameter" deprecation
        if ($severity === E_DEPRECATED && strpos($message, 'Passing null to parameter') !== false) {
            return true; // Suppress
        }
        // Suppress "strftime() is deprecated" (PHP 8.1+)
        if ($severity === E_DEPRECATED && strpos($message, 'strftime()') !== false) {
            return true; // Suppress
        }
        // Suppress "implicitly nullable parameter" (PHP 8.4+)
        if ($severity === E_DEPRECATED && strpos($message, 'implicitly nullable') !== false) {
            return true; // Suppress
        }
        // Suppress "Use of ${var} in strings is deprecated" (PHP 8.2+)
        if ($severity === E_DEPRECATED && strpos($message, '${') !== false) {
            return true; // Suppress
        }
        // Pass everything else to the previous handler or PHP's default
        if ($previousHandler) {
            return call_user_func($previousHandler, $severity, $message, $file, $line);
        }
        return false; // Let PHP handle it
    });
}

// ═══════════════════════════════════════════════════════════════
//  PHP 7.0+ — Null coalescing for array access safety
//  Helper function for legacy code that uses array_key_exists on
//  potentially null arrays.
// ═══════════════════════════════════════════════════════════════

if (!function_exists('array_get')) {
    /**
     * Safely get a value from an array with a default.
     * Works with null arrays and missing keys.
     */
    function array_get($array, $key, $default = null) {
        if (!is_array($array)) return $default;
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
}

// ═══════════════════════════════════════════════════════════════
//  PHP 8.0+ — mysqli_result changes
//  fetch_assoc() returns null instead of false on empty result.
//  This doesn't break while() loops but may break strict === false
//  checks. No polyfill needed, but document for awareness.
// ═══════════════════════════════════════════════════════════════

// No polyfill needed — while($row = $result->fetch_assoc()) works
// the same with both null and false as the loop terminator.

// ═══════════════════════════════════════════════════════════════
//  Startup diagnostic — log PHP version on first load
// ═══════════════════════════════════════════════════════════════

// Log once per session to help with debugging
if (function_exists('session_status') && session_status() === PHP_SESSION_NONE) {
    // Don't start session here — let the caller do it
} elseif (isset($_SESSION) && !isset($_SESSION['_compat_logged'])) {
    $_SESSION['_compat_logged'] = true;
    // Optionally log: error_log("TicketsCAD: PHP " . PHP_VERSION . " compat layer loaded");
}
