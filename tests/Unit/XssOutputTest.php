<?php
/**
 * Tests for XSS prevention via the e() helper function.
 *
 * @since v3.44.0
 */

use PHPUnit\Framework\TestCase;

class XssOutputTest extends TestCase
{
    /**
     * Verify e() escapes script tags.
     */
    public function testEscapesScriptTags(): void
    {
        $this->assertSame(
            '&lt;script&gt;alert(1)&lt;/script&gt;',
            e('<script>alert(1)</script>')
        );
    }

    /**
     * Verify e() escapes double quotes.
     */
    public function testEscapesDoubleQuotes(): void
    {
        $this->assertSame(
            '&quot;onclick=&quot;alert(1)&quot;',
            e('"onclick="alert(1)"')
        );
    }

    /**
     * Verify e() escapes single quotes.
     */
    public function testEscapesSingleQuotes(): void
    {
        $this->assertSame(
            '&#039;onmouseover=&#039;alert(1)&#039;',
            e("'onmouseover='alert(1)'")
        );
    }

    /**
     * Verify e() escapes ampersands.
     */
    public function testEscapesAmpersands(): void
    {
        $this->assertSame('&amp;amp;', e('&amp;'));
    }

    /**
     * Verify e() handles null values.
     */
    public function testHandlesNull(): void
    {
        $this->assertSame('', e(null));
    }

    /**
     * Verify e() handles integers.
     */
    public function testHandlesIntegers(): void
    {
        $this->assertSame('42', e(42));
    }

    /**
     * Verify e() handles empty string.
     */
    public function testHandlesEmptyString(): void
    {
        $this->assertSame('', e(''));
    }

    /**
     * Verify e() preserves safe strings.
     */
    public function testPreservesSafeStrings(): void
    {
        $this->assertSame('Hello World 123', e('Hello World 123'));
    }

    /**
     * Verify patient.php uses e() for GET parameters.
     */
    public function testPatientPhpEscapesGetParams(): void
    {
        $file = realpath(__DIR__ . '/../../patient.php');
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            "e(\$_GET['id'])",
            $content,
            'patient.php should escape $_GET[id] with e()'
        );
        $this->assertStringContainsString(
            "e(\$_GET['ticket_id'])",
            $content,
            'patient.php should escape $_GET[ticket_id] with e()'
        );
    }

    /**
     * Verify security headers function is called globally.
     */
    public function testSecurityHeadersCalledInFunctions(): void
    {
        $file = realpath(__DIR__ . '/../../incs/functions.inc.php');
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringContainsString(
            'set_security_headers()',
            $content,
            'functions.inc.php should call set_security_headers()'
        );
    }
}
