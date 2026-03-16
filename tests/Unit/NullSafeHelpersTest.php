<?php
/**
 * Tests for null-safe wrapper functions in functions.inc.php.
 *
 * Ensures the wrappers handle null, empty, and normal inputs correctly
 * across PHP 7.2 through 8.5+ without deprecation warnings.
 *
 * @since v3.44.0
 */

// Define the null-safe wrappers here to test in isolation.
// Production versions live in incs/functions.inc.php (near line 2370).
// Duplicated here because functions.inc.php has DB connection side effects.
if (!function_exists('safe_addslashes')) {
    function safe_addslashes($str) {
        return addslashes($str ?? '');
    }
    function safe_htmlentities($str, $flags = ENT_QUOTES, $encoding = 'UTF-8') {
        return htmlentities($str ?? '', $flags, $encoding);
    }
    function safe_strlen($str) {
        return strlen($str ?? '');
    }
    function safe_strtotime($datetime) {
        if ($datetime === null || $datetime === '') {
            return false;
        }
        return strtotime($datetime);
    }
}

use PHPUnit\Framework\TestCase;

class NullSafeHelpersTest extends TestCase
{
    public function testSafeAddslashesWithNull()
    {
        $this->assertSame('', safe_addslashes(null));
    }

    public function testSafeAddslashesWithEmpty()
    {
        $this->assertSame('', safe_addslashes(''));
    }

    public function testSafeAddslashesWithNormal()
    {
        $this->assertSame("it\\'s", safe_addslashes("it's"));
        $this->assertSame('hello', safe_addslashes('hello'));
    }

    public function testSafeHtmlentitiesWithNull()
    {
        $this->assertSame('', safe_htmlentities(null));
    }

    public function testSafeHtmlentitiesWithEmpty()
    {
        $this->assertSame('', safe_htmlentities(''));
    }

    public function testSafeHtmlentitiesWithNormal()
    {
        $this->assertSame('&lt;b&gt;', safe_htmlentities('<b>'));
        $this->assertSame('a &amp; b', safe_htmlentities('a & b'));
    }

    public function testSafeStrlenWithNull()
    {
        $this->assertSame(0, safe_strlen(null));
    }

    public function testSafeStrlenWithEmpty()
    {
        $this->assertSame(0, safe_strlen(''));
    }

    public function testSafeStrlenWithNormal()
    {
        $this->assertSame(5, safe_strlen('hello'));
    }

    public function testSafeStrtotimeWithNull()
    {
        $this->assertFalse(safe_strtotime(null));
    }

    public function testSafeStrtotimeWithEmpty()
    {
        $this->assertFalse(safe_strtotime(''));
    }

    public function testSafeStrtotimeWithNormal()
    {
        $expected = strtotime('2024-01-15 10:30:00');
        $this->assertSame($expected, safe_strtotime('2024-01-15 10:30:00'));
    }

    public function testSafeStrtotimeWithRelative()
    {
        $result = safe_strtotime('now');
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }
}
