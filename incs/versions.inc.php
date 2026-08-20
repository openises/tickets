<?php
/*
 * Single source for installer/installed version metadata.
 * Keep $tickets_current_version updated for new releases.
 * 3/1/2026: Consolidated version source and added legacy detection
 *           for installs where the settings table exists but _version is absent.
 * 6/8/2026: v3.44.3 — fix docker-autoinstall to read from this constant
 *           instead of hardcoding the version string in its own SQL.
 *           This file is now the SINGLE source of truth; bumping it
 *           propagates to the auto-installer automatically.
 * 7/16/2026: v3.44.4 — fix fatal "Cannot redeclare get_roster()" on the
 *           add-unit path in no-internet (NM) mode; units_nm.php carried
 *           duplicate copies of get_roster()/get_user_details() that also
 *           live in functions.inc.php. Code-only fix, no schema change; the
 *           bump alerts existing installs that an update is available.
 * 8/19/2026: v3.44.5 — fix reverse geocoding on map click: five call sites
 *           passed a map ZOOM where Control.Geocoder.reverse() expects a
 *           SCALE, which silently asked Nominatim for zoom -4 and got back
 *           only "United States" — clearing City/State on every click.
 *           Also stopped neighbourhood/suburb overwriting a correct city,
 *           fixed getTheAddress() reading the wrong result shape for
 *           Nominatim, and corrected stale map attribution. Code-only, no
 *           schema change. Contributed by Ron Jones (PR #12).
 */
define('TICKETS_CURRENT_VERSION', 'v3.44.5');
$tickets_current_version = TICKETS_CURRENT_VERSION;

if (!function_exists('tickets_get_versions')) {
    function tickets_get_versions() {
        if (function_exists('mysqli_report')) { mysqli_report(MYSQLI_REPORT_OFF); }
        $installerVersion = defined('TICKETS_CURRENT_VERSION') ? TICKETS_CURRENT_VERSION : 'unknown';
        $installedVersion = null;

        $mysqlInc = __DIR__ . '/mysql.inc.php';
        if (is_readable($mysqlInc)) {
            require $mysqlInc;

            if (isset($mysql_host, $mysql_user, $mysql_db)) {
                $mysqli = @new mysqli($mysql_host, $mysql_user, isset($mysql_passwd) ? $mysql_passwd : '', $mysql_db);
                if (!$mysqli->connect_errno) {
                    $prefix = isset($mysql_prefix) ? $mysql_prefix : '';
                    $settingsTable = $prefix . 'settings';
                    $settingsTableEsc = $mysqli->real_escape_string($settingsTable);

                    // Legacy detection: if settings table exists but _version is missing, mark as unknown legacy install.
                    $tableExists = false;
                    $existsResult = @$mysqli->query("SHOW TABLES LIKE '{$settingsTableEsc}'");
                    if ($existsResult) {
                        $tableExists = ($existsResult->num_rows > 0);
                        $existsResult->free();
                    }

                    if ($tableExists) {
                        $query = "SELECT `value` FROM `{$settingsTable}` WHERE `name` = '_version' LIMIT 1";
                        $result = @$mysqli->query($query);
                        if ($result) {
                            $row = $result ? $result->fetch_assoc() : null;
                            if ($row && isset($row['value']) && trim($row['value']) !== '') {
                                $installedVersion = $row['value'];
                            } else {
                                $installedVersion = 'unknown (legacy)';
                            }
                            $result->free();
                        } else {
                            $installedVersion = 'unknown (legacy)';
                        }
                    }

                    $mysqli->close();
                }
            }
        }

        return array(
            'installer' => $installerVersion,
            'installed' => $installedVersion,
            'match' => ($installedVersion !== null && $installedVersion === $installerVersion),
            'has_install' => file_exists(dirname(__DIR__) . '/install.php')
        );
    }
}
