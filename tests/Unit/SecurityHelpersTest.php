<?php
/**
 * Unit tests for security helper functions (incs/security.inc.php)
 */

use PHPUnit\Framework\TestCase;

class SecurityHelpersTest extends TestCase
{
    /**
     * Test e() function escapes HTML special characters
     */
    public function testEscapesHtmlSpecialChars(): void
    {
        $this->assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'));
    }

    public function testEscapesQuotes(): void
    {
        $this->assertEquals('&quot;hello&quot; &amp; &#039;world&#039;', e('"hello" & \'world\''));
    }

    public function testEscapesNullReturnsEmptyString(): void
    {
        $this->assertEquals('', e(null));
    }

    public function testEscapesIntegerReturnsString(): void
    {
        $this->assertEquals('42', e(42));
    }

    public function testEscapesEmptyString(): void
    {
        $this->assertEquals('', e(''));
    }

    public function testEscapesUtf8(): void
    {
        // UTF-8 characters should pass through unchanged
        $this->assertEquals('café résumé', e('café résumé'));
    }

    /**
     * Test sanitize_int()
     */
    public function testSanitizeIntWithInteger(): void
    {
        $this->assertSame(42, sanitize_int(42));
    }

    public function testSanitizeIntWithString(): void
    {
        $this->assertSame(42, sanitize_int('42'));
    }

    public function testSanitizeIntWithNull(): void
    {
        $this->assertSame(0, sanitize_int(null));
    }

    public function testSanitizeIntWithDefault(): void
    {
        $this->assertSame(-1, sanitize_int(null, -1));
    }

    public function testSanitizeIntWithMaliciousString(): void
    {
        $this->assertSame(1, sanitize_int('1; DROP TABLE users'));
    }

    public function testSanitizeIntWithFloat(): void
    {
        $this->assertSame(3, sanitize_int(3.7));
    }

    /**
     * Test sanitize_string()
     */
    public function testSanitizeStringTrims(): void
    {
        $this->assertEquals('hello', sanitize_string('  hello  '));
    }

    public function testSanitizeStringStripsNullBytes(): void
    {
        $this->assertEquals('hello', sanitize_string("hel\0lo"));
    }

    public function testSanitizeStringWithNull(): void
    {
        $this->assertEquals('', sanitize_string(null));
    }

    /**
     * Test CSRF token generation and verification
     */
    public function testCsrfTokenGeneration(): void
    {
        // Start a test session
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $token = csrf_token();
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertGreaterThanOrEqual(32, strlen($token));
    }

    public function testCsrfTokenConsistentWithinSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $token1 = csrf_token();
        $token2 = csrf_token();
        $this->assertEquals($token1, $token2, 'CSRF token should be consistent within a session');
    }

    public function testCsrfVerifyWithValidToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $token = csrf_token();
        $this->assertTrue(csrf_verify($token));
    }

    public function testCsrfVerifyWithInvalidToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        csrf_token(); // Ensure a token exists
        $this->assertFalse(csrf_verify('invalid-token-value'));
    }

    public function testCsrfVerifyWithEmptyToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        csrf_token();
        $this->assertFalse(csrf_verify(''));
    }

    public function testCsrfVerifyWithNullToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        csrf_token();
        $this->assertFalse(csrf_verify(null));
    }

    /**
     * Test csrf_token_field() HTML output
     */
    public function testCsrfTokenFieldReturnsHiddenInput(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $field = csrf_token_field();
        $this->assertStringContainsString('<input type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="', $field);
    }
}
