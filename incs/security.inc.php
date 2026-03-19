<?php
/**
 * Tickets CAD - Security Helper Functions
 *
 * Provides centralized security utilities for XSS prevention, CSRF protection,
 * input sanitization, and password hashing.
 *
 * @since v3.44.0
 */

/**
 * Escape a string for safe HTML output (XSS prevention).
 *
 * Use this function around ANY variable being output into HTML, especially
 * user-supplied data from $_GET, $_POST, $_REQUEST, or database values that
 * originated from user input.
 *
 * Usage:
 *   <input value="<?php echo e($row['name']); ?>">
 *   <td><?php echo e($_GET['search']); ?></td>
 *
 * @param mixed $value The value to escape
 * @return string HTML-safe escaped string
 */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Get or generate a CSRF token for the current session.
 *
 * The token is stored in $_SESSION and remains valid for the entire session.
 * Call this when rendering forms to get the token value for a hidden field.
 *
 * @return string The CSRF token
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 *
 * Call this at the top of any form processing script to verify the token.
 *
 * @param mixed $token The submitted token to verify
 * @return bool True if the token is valid
 */
function csrf_verify($token): bool
{
    if (empty($token) || !is_string($token)) {
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (empty($_SESSION['_csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['_csrf_token'], $token);
}

/**
 * Generate a hidden HTML input field containing the CSRF token.
 *
 * Usage in forms:
 *   <form method="POST" action="process.php">
 *       <?php echo csrf_token_field(); ?>
 *       ... other fields ...
 *   </form>
 *
 * @return string HTML hidden input element
 */
function csrf_token_field(): string
{
    $token = e(csrf_token());
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Sanitize a value as an integer.
 *
 * Safely converts any input to an integer, with an optional default value.
 * Use this for IDs, counts, and other numeric parameters from user input.
 *
 * @param mixed $value   The value to sanitize
 * @param int   $default Default value if input is null/empty (default: 0)
 * @return int Sanitized integer value
 */
function sanitize_int($value, int $default = 0): int
{
    if ($value === null || $value === '') {
        return $default;
    }
    return (int) $value;
}

/**
 * Sanitize a string value.
 *
 * Trims whitespace, removes null bytes, and ensures string type.
 * This is basic cleanup — NOT a substitute for prepared statements in SQL
 * or e() for HTML output.
 *
 * @param mixed $value The value to sanitize
 * @return string Sanitized string
 */
function sanitize_string($value): string
{
    if ($value === null) {
        return '';
    }
    $value = (string) $value;
    // Remove null bytes
    $value = str_replace("\0", '', $value);
    return trim($value);
}

/**
 * Sanitize a latitude or longitude value for DECIMAL(10,7) storage.
 *
 * Returns a rounded float, or NULL if the value is empty, non-numeric,
 * or outside valid coordinate range.
 *
 * @param mixed  $value     Raw coordinate value
 * @param string $axis      'lat' or 'lng' (for range validation)
 * @return float|null       Sanitized coordinate or NULL
 * @since v3.44.1
 */
function sanitize_coordinate($value, $axis = '') {
    $value = sanitize_string($value);
    if ($value === '') {
        return null;
    }
    if (!is_numeric($value)) {
        return null;
    }
    $coord = round((float) $value, 7);
    // Range validation
    if ($axis === 'lat' && ($coord < -90.0 || $coord > 90.0)) {
        return null;
    }
    if ($axis === 'lng' && ($coord < -180.0 || $coord > 180.0)) {
        return null;
    }
    return $coord;
}

/**
 * Hash a password using PHP's built-in password_hash with bcrypt.
 *
 * Use this for ALL new password storage. Never use md5() for passwords.
 *
 * @param string $password The plaintext password to hash
 * @return string The bcrypt hash
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a password against a stored hash.
 *
 * Supports both modern bcrypt hashes and legacy MD5 hashes for backward
 * compatibility during migration. When a legacy MD5 hash matches, the
 * caller should rehash with hash_password() and update the database.
 *
 * @param string $password    The plaintext password to check
 * @param string $storedHash  The stored hash from the database
 * @return array ['valid' => bool, 'needs_rehash' => bool]
 */
function verify_password(string $password, string $storedHash): array
{
    // Try modern password_verify first (bcrypt)
    if (password_verify($password, $storedHash)) {
        return [
            'valid' => true,
            'needs_rehash' => password_needs_rehash($storedHash, PASSWORD_BCRYPT, ['cost' => 12])
        ];
    }

    // Fallback: check legacy MD5 hashes (case-insensitive and case-sensitive)
    if ($storedHash === md5(strtolower($password)) || $storedHash === md5($password)) {
        return [
            'valid' => true,
            'needs_rehash' => true  // Always rehash MD5 passwords
        ];
    }

    return ['valid' => false, 'needs_rehash' => false];
}

/**
 * Set security headers on the HTTP response.
 *
 * Call this early in the page lifecycle, before any output is sent.
 * Safe to call multiple times (headers_sent() check prevents errors).
 */
function set_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    // Prevent clickjacking - page can only be framed by same origin
    // Note: Tickets uses framesets internally, SAMEORIGIN allows this
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Enable browser XSS filter
    header('X-XSS-Protection: 1; mode=block');

    // Prevent caching of sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    // Referrer policy - don't leak full URL to external sites
    header('Referrer-Policy: same-origin');
}

/**
 * Configure secure session cookie parameters.
 *
 * Call this BEFORE session_start() to set HttpOnly, Secure (when HTTPS),
 * and SameSite flags on the session cookie. Prevents session hijacking
 * via JavaScript access and cross-site request forgery.
 *
 * Safe to call multiple times - returns immediately if session already started.
 *
 * @since v3.44.0
 */
function configure_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return; // Already started, too late to configure
    }

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,            // Session cookie (expires when browser closes)
        'path'     => '/',
        'domain'   => '',
        'secure'   => $is_https,    // Only send over HTTPS if available
        'httponly'  => true,         // Not accessible via JavaScript
        'samesite' => 'Lax'         // Prevents CSRF via cross-site form POST
    ]);
}
