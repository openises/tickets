<?php
/*
 * Single source for installer/installed version metadata.
 * Keep $tickets_current_version updated for new releases.
 * 3/1/2026: Consolidated version source and added legacy detection
 *           for installs where the settings table exists but _version is absent.
 */
$tickets_current_version = 'v3.43.0';

if (!function_exists('tickets_get_versions')) {
    function tickets_get_versions() {
        $installerVersion = isset($GLOBALS['tickets_current_version']) ? $GLOBALS['tickets_current_version'] : 'unknown';
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
                            $row = $result->fetch_assoc();
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
