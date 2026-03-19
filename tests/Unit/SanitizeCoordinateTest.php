<?php
/**
 * Tests for sanitize_coordinate() — consolidated in incs/security.inc.php
 */
class SanitizeCoordinateTest extends \PHPUnit\Framework\TestCase
{
    public function testValidLatitude()
    {
        $this->assertSame(44.977753, sanitize_coordinate('44.977753', 'lat'));
    }

    public function testValidLongitude()
    {
        $this->assertSame(-93.2650109, sanitize_coordinate('-93.2650109', 'lng'));
    }

    public function testEmptyReturnsNull()
    {
        $this->assertNull(sanitize_coordinate(''));
        $this->assertNull(sanitize_coordinate(null));
    }

    public function testNonNumericReturnsNull()
    {
        $this->assertNull(sanitize_coordinate('not-a-number'));
        $this->assertNull(sanitize_coordinate('abc'));
    }

    public function testLatOutOfRangeReturnsNull()
    {
        $this->assertNull(sanitize_coordinate('91', 'lat'));
        $this->assertNull(sanitize_coordinate('-91', 'lat'));
    }

    public function testLngOutOfRangeReturnsNull()
    {
        $this->assertNull(sanitize_coordinate('181', 'lng'));
        $this->assertNull(sanitize_coordinate('-181', 'lng'));
    }

    public function testEdgeValuesAccepted()
    {
        $this->assertSame(90.0, sanitize_coordinate('90', 'lat'));
        $this->assertSame(-90.0, sanitize_coordinate('-90', 'lat'));
        $this->assertSame(180.0, sanitize_coordinate('180', 'lng'));
        $this->assertSame(-180.0, sanitize_coordinate('-180', 'lng'));
    }

    public function testWithoutAxisSkipsRangeCheck()
    {
        // No axis = no range check, just numeric validation
        $this->assertSame(999.0, sanitize_coordinate('999'));
    }

    public function testRoundsToSevenDecimals()
    {
        $this->assertSame(44.9777534, sanitize_coordinate('44.97775341234'));
    }

    public function testZeroIsValid()
    {
        $this->assertSame(0.0, sanitize_coordinate('0', 'lat'));
    }
}
