<?php
/**
 * Incident numbering scheme (the `_inc_num` setting) - pure helpers.
 *
 * No database, no session, no output, no includes: everything here takes the stored value as an
 * argument so it can be unit tested (tests/Unit/IncNumSchemeTest.php). The database side lives
 * in get_inc_num_ary() in functions.inc.php.
 *
 * The scheme is a six-element array kept in the `settings` table as base64(serialize($array)):
 *
 *   [0] style      0 none, 1 number only, 2 label + number, 3 year + number
 *   [1] label      used by style 2
 *   [2] separator
 *   [3] next number
 *   [4] prepend the incident nature (1) or not (0)
 *   [5] two-digit year the counter belongs to (style 3 starts again at 1 when it changes)
 *
 * Installs older than 3/2/11 stored the plain serialized array; both forms are read.
 *
 * Why this exists (GH #18): every caller used to unserialize the value without checking it and
 * add.php then wrote its copy back after incrementing. A value that does not unserialize (empty,
 * truncated, hand edited) gives false, and serialize(false) is a perfectly valid stored value, so
 * one bad read replaced the operator's scheme with a boolean for good. The rules below are what
 * stops that: a stored value is parsed defensively, a fallback is never written back, and the
 * only thing ever written is a real scheme whose counter actually moved.
 *
 * @package TicketsCAD
 * @since   v3.44.7
 */

/**
 * The scheme used when none is stored or the stored one is unusable: no serial numbers, counter
 * attached to the current year.
 *
 * @return array Six elements, see the file comment.
 */
function inc_num_default() {
    return array("0", "", "", "", "0", date("y"));
    }

/**
 * Parse a stored `_inc_num` value into a usable six-element scheme. Never returns anything but an
 * array and never raises a PHP notice, whatever it is given.
 *
 * $status says what was found, so a caller can tell a real scheme from a fallback:
 *   'ok'      a usable scheme (a short or sparse array is completed from the default)
 *   'empty'   nothing stored; the normal state of an install that never configured numbering
 *   'corrupt' something was stored but it is not a scheme; the default is returned and the stored
 *             value is NOT to be overwritten with it
 *
 * The payload is read with allowed_classes => false: the setting lives in the database, so what
 * it holds is only as trustworthy as the database, and object instantiation from it has no use.
 *
 * @param mixed       $raw    The stored value (string, false when the row is absent, null).
 * @param string|null $status Out parameter: 'ok', 'empty' or 'corrupt'.
 * @return array Six elements, see the file comment.
 */
function inc_num_parse($raw, &$status = null) {
    $default = inc_num_default();
    $raw = is_scalar($raw) ? trim((string) $raw) : '';
    if ($raw === '') {
        $status = 'empty';
        return $default;
        }
    $payload = (strpos($raw, "{") > 0)? $raw : base64_decode($raw);            // plain (pre 3/2/11) or base64
    $ary = (is_string($payload) && (strncmp($payload, 'a:', 2) === 0))?        // a scheme is always a serialized ARRAY ('a:6:{...}'):
        @unserialize($payload, array('allowed_classes' => false)) : false;     // anything else never reaches unserialize()
    if (!is_array($ary)) {
        $status = 'corrupt';
        return $default;
        }
    if (!array_key_exists(0, $ary) || !is_numeric($ary[0]) || !in_array((int) $ary[0], array(0, 1, 2, 3), true)) {
        $status = 'corrupt';                                                    // not one of the four styles
        return $default;
        }
    foreach ($ary as $v) {                                                      // every field is a plain value; an object (never
        if (!is_scalar($v) && ($v !== null)) {                                  // instantiated, see below) or a nested array would
            $status = 'corrupt';                                                // only break the string handling that follows
            return $default;
            }
        }
    foreach ($default as $k => $v) {                                            // tolerate a short or sparse array
        if (!array_key_exists($k, $ary)) {$ary[$k] = $v;}
        }
    $status = 'ok';
    return $ary;
    }

/**
 * The form a scheme is stored in: base64 of the serialized array (since 3/2/11).
 *
 * @param array $ary A six-element scheme.
 * @return string
 */
function inc_num_encode($ary) {
    return base64_encode(serialize($ary));
    }

/**
 * Step the counter after an incident has taken its serial number, and say whether the result is
 * worth writing back. This is the logic add.php and add_nm.php each carried inline.
 *
 * Style 3 starts again at 1 when the year has changed; any other style in use just counts up;
 * style 0 does nothing. Only a scheme that was really stored ($status 'ok') and really moved
 * (style above 0) is to be saved. Writing anything else is how a bad read became permanent.
 *
 * @param array  $ary    Scheme from inc_num_parse().
 * @param string $status Status from inc_num_parse().
 * @return array array(0 => scheme to store, 1 => true when it must be written back)
 */
function inc_num_advance($ary, $status) {
    $the_year = date("y");
    if ((((int) $ary[0]) == 3) && (!($ary[5] == $the_year))) {                  // year style and change?
        $ary[3] = 1;                                                            // roll over and start at 1
        $ary[5] = $the_year;
        } else {
        if (((int) $ary[0]) > 0) {                                              // step to next no. if scheme in use
            $ary[3]++;                                                          // do the deed for next use
            }
        }
    return array($ary, (($status == 'ok') && (((int) $ary[0]) > 0)));
    }
