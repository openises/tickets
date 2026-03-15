<?php
/**
 * Tests for session hardening and CSRF token deployment.
 *
 * @since v3.44.0
 */

use PHPUnit\Framework\TestCase;

class SessionSecurityTest extends TestCase
{
    /**
     * Verify configure_secure_session() function exists and is callable.
     */
    public function testConfigureSecureSessionExists(): void
    {
        $this->assertTrue(
            function_exists('configure_secure_session'),
            'configure_secure_session() function should exist in security.inc.php'
        );
    }

    /**
     * Verify configure_secure_session() can be called without fatal error.
     * Note: In CLI/test mode, headers are already sent so session_set_cookie_params
     * will emit a warning, but the function should not fatal.
     */
    public function testConfigureSecureSessionCallable(): void
    {
        // In CLI mode, headers are already sent so we suppress the warning
        @configure_secure_session();
        $this->assertTrue(true); // If we got here without fatal error, test passes
    }

    /**
     * Verify configure_secure_session() is idempotent (safe to call multiple times).
     */
    public function testConfigureSecureSessionIdempotent(): void
    {
        @configure_secure_session();
        @configure_secure_session();
        $this->assertTrue(true);
    }

    /**
     * Verify main login uses configure_secure_session().
     */
    public function testMainLoginUsesSecureSession(): void
    {
        $file = realpath(__DIR__ . '/../../incs/login.inc.php');
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'configure_secure_session()',
            $content,
            'login.inc.php should call configure_secure_session()'
        );
    }

    /**
     * Verify main login uses session_regenerate_id().
     */
    public function testMainLoginRegeneratesSession(): void
    {
        $file = realpath(__DIR__ . '/../../incs/login.inc.php');
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'session_regenerate_id(true)',
            $content,
            'login.inc.php should call session_regenerate_id(true) after auth'
        );
    }

    /**
     * Verify mobile login uses configure_secure_session().
     */
    public function testMobileLoginUsesSecureSession(): void
    {
        $file = realpath(__DIR__ . '/../../rm/incs/mobile_login.inc.php');
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'configure_secure_session()',
            $content,
            'mobile_login.inc.php should call configure_secure_session()'
        );
    }

    /**
     * Verify mobile login uses session_regenerate_id().
     */
    public function testMobileLoginRegeneratesSession(): void
    {
        $file = realpath(__DIR__ . '/../../rm/incs/mobile_login.inc.php');
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'session_regenerate_id(true)',
            $content,
            'mobile_login.inc.php should call session_regenerate_id(true) after auth'
        );
    }

    /**
     * Verify CSRF token field is present in main login form.
     */
    public function testMainLoginHasCsrfToken(): void
    {
        $file = realpath(__DIR__ . '/../../incs/login.inc.php');
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'csrf_token_field()',
            $content,
            'login.inc.php should include csrf_token_field() in the login form'
        );
    }

    /**
     * Verify CSRF verification is present in main login POST handler.
     */
    public function testMainLoginVerifiesCsrf(): void
    {
        $file = realpath(__DIR__ . '/../../incs/login.inc.php');
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'csrf_verify(',
            $content,
            'login.inc.php should verify CSRF token in POST handler'
        );
    }

    /**
     * Verify CSRF token field is present in mobile login form.
     */
    public function testMobileLoginHasCsrfToken(): void
    {
        $file = realpath(__DIR__ . '/../../rm/incs/mobile_login.inc.php');
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'csrf_token_field()',
            $content,
            'mobile_login.inc.php should include csrf_token_field() in the login form'
        );
    }

    /**
     * Verify CSRF verification is present in mobile login POST handler.
     */
    public function testMobileLoginVerifiesCsrf(): void
    {
        $file = realpath(__DIR__ . '/../../rm/incs/mobile_login.inc.php');
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'csrf_verify(',
            $content,
            'mobile_login.inc.php should verify CSRF token in POST handler'
        );
    }
}
