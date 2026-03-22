<?php
/*
 * ticketsCAD installer hardening notes:
 * - Installer owns install/upgrade/schema operations; index.php no longer mutates schema.
 * - Supports clean install, upgrade sync, and write-config modes.
 * - Streams step-by-step progress and records installed _version for parity checks.
 */
error_reporting(E_ALL);
if (function_exists('mysqli_report')) { mysqli_report(MYSQLI_REPORT_OFF); }

require_once __DIR__ . '/incs/versions.inc.php';
require_once __DIR__ . '/incs/install_schema.inc.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function emit_line($line) {
    echo $line . "
";
    if (function_exists('ob_flush')) { @ob_flush(); }
    flush();
}

function installer_steps($mode, $tableCount, $seedCount) {
    $steps = array();
    if ($mode === 'install_clean') {
        $steps[] = array('type' => 'drop_tables', 'label' => 'Drop existing tables');
    }
    for ($i = 0; $i < $tableCount; $i++) {
        $steps[] = array('type' => 'schema', 'index' => $i, 'label' => 'Create/sync schema table #' . ($i + 1));
    }
    for ($i = 0; $i < $seedCount; $i++) {
        $steps[] = array('type' => 'seed', 'index' => $i, 'label' => 'Seed data rowset #' . ($i + 1));
    }
    $steps[] = array('type' => 'users', 'label' => 'Provision initial users');
    $steps[] = array('type' => 'version', 'label' => 'Record installer version');
    $steps[] = array('type' => 'config', 'label' => 'Write mysql config');
    return $steps;
}

function load_mysql_defaults() {
    $defaults = array('host' => 'localhost', 'user' => '', 'pass' => '', 'db' => '', 'prefix' => '');
    $inc = __DIR__ . '/incs/mysql.inc.php';
    if (is_readable($inc)) {
        @include $inc;
        if (isset($mysql_host)) { $defaults['host'] = $mysql_host; }
        if (isset($mysql_user)) { $defaults['user'] = $mysql_user; }
        if (isset($mysql_passwd)) { $defaults['pass'] = $mysql_passwd; }
        if (isset($mysql_db)) { $defaults['db'] = $mysql_db; }
        if (isset($mysql_prefix)) { $defaults['prefix'] = $mysql_prefix; }
    }
    return $defaults;
}

function connect_db($cfg) {
    $mysqli = @new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
    if ($mysqli->connect_errno) { return null; }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function table_exists_mysqli($mysqli, $table) {
    $esc = $mysqli->real_escape_string($table);
    $res = $mysqli->query("SHOW TABLES LIKE '{$esc}'");
    $ok = ($res && $res->num_rows > 0);
    if ($res) { $res->free(); }
    return $ok;
}

function detect_install($cfg) {
    $out = array('exists' => false, 'installed_version' => null, 'db_version' => null, 'legacy' => false);
    $mysqli = connect_db($cfg);
    if (!$mysqli) { return $out; }

    $out['db_version'] = $mysqli->server_info;
    $settings = $cfg['prefix'] . 'settings';
    if (table_exists_mysqli($mysqli, $settings)) {
        $out['exists'] = true;
        $res = $mysqli->query("SELECT `value` FROM `{$settings}` WHERE `name` = '_version' LIMIT 1");
        if ($res && ($row = $res->fetch_assoc()) && trim($row['value']) !== '') {
            $out['installed_version'] = trim($row['value']);
        } else {
            $out['installed_version'] = 'unknown (legacy)';
            $out['legacy'] = true;
        }
        if ($res) { $res->free(); }
    }

    $mysqli->close();
    return $out;
}

function write_mysql_config($cfg) {
    $path = __DIR__ . '/incs/mysql.inc.php';
    $body = "<?php\n";
    $body .= '$mysql_host = ' . var_export($cfg['host'], true) . ";\n";
    $body .= '$mysql_user = ' . var_export($cfg['user'], true) . ";\n";
    $body .= '$mysql_passwd = ' . var_export($cfg['pass'], true) . ";\n";
    $body .= '$mysql_db = ' . var_export($cfg['db'], true) . ";\n";
    $body .= '$mysql_prefix = ' . var_export($cfg['prefix'], true) . ";\n";
    return file_put_contents($path, $body) !== false;
}

function apply_prefix($sql, $prefix) {
    $patterns = array(
        '/(CREATE TABLE\s+`)([^`]+)(`)/i',
        '/(INSERT INTO\s+`)([^`]+)(`)/i',
        '/(ALTER TABLE\s+`)([^`]+)(`)/i',
        '/(DROP TABLE IF EXISTS\s+`)([^`]+)(`)/i',
        '/(UPDATE\s+`)([^`]+)(`)/i',
        '/(DELETE FROM\s+`)([^`]+)(`)/i'
    );

    foreach ($patterns as $pattern) {
        $sql = preg_replace_callback($pattern, function($m) use ($prefix) {
            if ($prefix === '' || strpos($m[2], $prefix) === 0) {
                return $m[1] . $m[2] . $m[3];
            }
            return $m[1] . $prefix . $m[2] . $m[3];
        }, $sql);
    }

    return $sql;
}

function installer_seed_target_table($insertSql) {
    if (preg_match('/^INSERT INTO\s+`([^`]+)`/i', $insertSql, $matches)) {
        return $matches[1];
    }
    return null;
}

function installer_seed_sql($insertBase, $prefix, $mode) {
    $insertSql = apply_prefix($insertBase, $prefix);
    if ($mode === 'upgrade') {
        $insertSql = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $insertSql, 1);
    }
    return $insertSql;
}

function installer_apply_seed($mysqli, $insertBase, $prefix, $mode, &$messages) {
    if (preg_match('/^INSERT INTO\s+`user`/i', $insertBase)) {
        $messages[] = 'Skipping default user seed (installer provisions super admin + guest separately).';
        return true;
    }

    $insertSql = installer_seed_sql($insertBase, $prefix, $mode);
    $targetTable = installer_seed_target_table($insertSql);
    if ($targetTable !== null) {
        $messages[] = 'Seeding data: ' . $targetTable . ' ...';
    }

    if (!$mysqli->query($insertSql)) {
        $messages[] = 'Seed warning: ' . $mysqli->error;
        return false;
    }

    if ($mode === 'upgrade' && $targetTable !== null && $mysqli->affected_rows === 0) {
        $messages[] = 'Seeding data: ' . $targetTable . ' ... Already present.';
    }
    return true;
}

function installer_ident($name) {
    return '`' . str_replace('`', '``', $name) . '`';
}

function installer_literal($mysqli, $value) {
    return ($value === null) ? 'NULL' : "'" . $mysqli->real_escape_string((string)$value) . "'";
}

function installer_table_row_count($mysqli, $tableName) {
    $res = $mysqli->query('SELECT COUNT(*) AS `count` FROM ' . installer_ident($tableName));
    if (!$res) { return 0; }
    $row = $res->fetch_assoc();
    $res->free();
    return isset($row['count']) ? (int)$row['count'] : 0;
}

function installer_fetch_columns($mysqli, $tableName) {
    $columns = array();
    $res = $mysqli->query('SHOW COLUMNS FROM ' . installer_ident($tableName));
    if (!$res) { return $columns; }
    while ($row = $res->fetch_assoc()) {
        $columns[] = $row;
    }
    $res->free();
    return $columns;
}

function installer_fetch_create_table_sql($mysqli, $tableName) {
    $res = $mysqli->query('SHOW CREATE TABLE ' . installer_ident($tableName));
    if (!$res) { return null; }
    $row = $res->fetch_assoc();
    $res->free();
    if (!$row) { return null; }
    return isset($row['Create Table']) ? $row['Create Table'] : null;
}

function installer_normalize_create_table_sql($sql) {
    if (!is_string($sql) || $sql === '') { return ''; }
    $sql = str_replace(array("
", "
"), "
", $sql);
    $sql = preg_replace('/CREATE TABLE\s+`[^`]+`/i', 'CREATE TABLE `__TABLE__`', $sql, 1);
    $sql = preg_replace('/AUTO_INCREMENT=\d+/i', 'AUTO_INCREMENT', $sql);
    $sql = preg_replace('/\s+/', ' ', trim($sql));
    return strtolower($sql);
}

function installer_table_matches_target_schema($mysqli, $tableName, $createSql) {
    $existingSql = installer_fetch_create_table_sql($mysqli, $tableName);
    if ($existingSql === null) { return false; }
    return installer_normalize_create_table_sql($existingSql) === installer_normalize_create_table_sql($createSql);
}

function installer_extract_create_table_parts($sql) {
    $parts = array('definition' => '', 'options' => '');
    if (!is_string($sql) || $sql === '') { return $parts; }
    if (preg_match('/CREATE TABLE\s+`[^`]+`\s*\((.*)\)\s*(.*)$/is', $sql, $matches)) {
        $parts['definition'] = $matches[1];
        $parts['options'] = $matches[2];
    }
    return $parts;
}

function installer_normalize_sql_fragment($sql) {
    if (!is_string($sql) || $sql === '') { return ''; }
    $sql = str_replace(array("
", "
"), "
", $sql);
    $sql = preg_replace('/AUTO_INCREMENT=\d+/i', 'AUTO_INCREMENT', $sql);
    $sql = preg_replace('/\s+/', ' ', trim($sql));
    return strtolower($sql);
}

function installer_table_requires_rebuild($mysqli, $tableName, $createSql) {
    $existingSql = installer_fetch_create_table_sql($mysqli, $tableName);
    if ($existingSql === null) { return true; }
    $existingParts = installer_extract_create_table_parts($existingSql);
    $targetParts = installer_extract_create_table_parts($createSql);
    return installer_normalize_sql_fragment($existingParts['definition']) !== installer_normalize_sql_fragment($targetParts['definition']);
}

function installer_build_table_options_alter_sql($tableName, $createSql) {
    $parts = array();
    $charset = null;
    $collation = null;
    if (preg_match('/ENGINE=([^\s]+)/i', $createSql, $m)) {
        $parts[] = 'ENGINE=' . $m[1];
    }
    if (preg_match('/DEFAULT CHARSET=([^\s]+)/i', $createSql, $m)) {
        $charset = $m[1];
    }
    if (preg_match('/COLLATE=([^\s]+)/i', $createSql, $m)) {
        $collation = $m[1];
    }
    if ($charset !== null) {
        $convert = 'CONVERT TO CHARACTER SET ' . $charset;
        if ($collation !== null) {
            $convert .= ' COLLATE ' . $collation;
        }
        $parts[] = $convert;
    } elseif ($collation !== null) {
        $parts[] = 'COLLATE=' . $collation;
    }
    if (preg_match('/ROW_FORMAT=([^\s]+)/i', $createSql, $m)) {
        $parts[] = 'ROW_FORMAT=' . $m[1];
    }
    if (empty($parts)) { return null; }
    return 'ALTER TABLE ' . installer_ident($tableName) . ' ' . implode(', ', $parts);
}

function installer_convert_table_options($mysqli, $tableName, $createSql, &$logs) {
    $alterSql = installer_build_table_options_alter_sql($tableName, $createSql);
    if ($alterSql === null) {
        $logs[] = 'Upgrading ' . $tableName . '... Failed: unable to determine target table options.';
        return false;
    }
    if (!$mysqli->query($alterSql)) {
        $logs[] = 'Upgrading ' . $tableName . '... Failed to update table options: ' . $mysqli->error;
        return false;
    }
    $logs[] = 'Upgrading ' . $tableName . '... Done! Updated table options only.';
    return true;
}

function installer_build_csv_download_link($tableName) {
    $url = 'install.php?download_unmigrated=' . rawurlencode($tableName) . '&format=csv';
    return '<a href="' . h($url) . '">Download CSV</a>';
}

function installer_export_table_as_csv($mysqli, $tableName) {
    if (!table_exists_mysqli($mysqli, $tableName)) {
        header('HTTP/1.1 404 Not Found');
        echo 'Table not found.';
        return;
    }

    $filename = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $tableName) . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    $res = $mysqli->query('SELECT * FROM ' . installer_ident($tableName));
    if ($res) {
        $headers = array();
        $fields = $res->fetch_fields();
        foreach ($fields as $field) { $headers[] = $field->name; }
        fputcsv($out, $headers);
        while ($row = $res->fetch_assoc()) {
            fputcsv($out, $row);
        }
        $res->free();
    }
    fclose($out);
}

function installer_migration_select_expression($tableName, $column) {
    if ($tableName === 'requests' && ($column === 'lat' || $column === 'lng')) {
        $ident = installer_ident($column);
        return "CASE "
            . "WHEN " . $ident . " IS NULL THEN NULL "
            . "WHEN TRIM(CAST(" . $ident . " AS CHAR)) = '' THEN NULL "
            . "WHEN TRIM(CAST(" . $ident . " AS CHAR)) REGEXP '^-?[0-9]+(\.[0-9]+)?$' THEN ROUND(CAST(" . $ident . " AS DECIMAL(10,7)), 7) "
            . "ELSE NULL END AS " . $ident;
    }
    return installer_ident($column);
}

function installer_delete_single_row($mysqli, $tableName, $row) {
    $conditions = array();
    foreach ($row as $column => $value) {
        $conditions[] = installer_ident($column) . ' <=> ' . installer_literal($mysqli, $value);
    }
    $sql = 'DELETE FROM ' . installer_ident($tableName) . ' WHERE ' . implode(' AND ', $conditions) . ' LIMIT 1';
    return (bool)$mysqli->query($sql);
}

function create_or_sync_table($mysqli, $tableName, $createSql, $mode, &$logs) {
    if ($mode !== 'upgrade' || !table_exists_mysqli($mysqli, $tableName)) {
        if (!$mysqli->query($createSql)) {
            $logs[] = "Create table warning for {$tableName}: " . $mysqli->error;
            return false;
        }
        return true;
    }

    if (installer_table_matches_target_schema($mysqli, $tableName, $createSql)) {
        $logs[] = $tableName . ' already matches current schema.';
        return true;
    }

    if (!installer_table_requires_rebuild($mysqli, $tableName, $createSql)) {
        $logs[] = 'Upgrading ' . $tableName . '...';
        if (installer_convert_table_options($mysqli, $tableName, $createSql, $logs)) {
            return true;
        }
        $logs[] = 'Upgrading ' . $tableName . '... Falling back to full table migration.';
    }

    $tempTable = $tableName . '__upgrade_tmp';
    $unmigratedTable = $tableName . '_unmigrated';
    $logs[] = 'Upgrading ' . $tableName . '...';

    $mysqli->query('DROP TABLE IF EXISTS ' . installer_ident($tempTable));
    $mysqli->query('DROP TABLE IF EXISTS ' . installer_ident($unmigratedTable));

    $tempCreateSql = preg_replace('/CREATE TABLE\s+`[^`]+`/i', 'CREATE TABLE ' . installer_ident($tempTable), $createSql, 1);
    if (!$mysqli->query($tempCreateSql)) {
        $logs[] = 'Upgrading ' . $tableName . '... Failed to create temporary table: ' . $mysqli->error;
        return false;
    }

    $sourceColumns = installer_fetch_columns($mysqli, $tableName);
    $targetColumns = installer_fetch_columns($mysqli, $tempTable);
    $sourceColumnMap = array();
    $commonColumns = array();
    foreach ($sourceColumns as $column) {
        $sourceColumnMap[$column['Field']] = true;
    }
    foreach ($targetColumns as $column) {
        if (isset($sourceColumnMap[$column['Field']])) {
            $commonColumns[] = $column['Field'];
        }
    }

    if (empty($commonColumns)) {
        $logs[] = 'Upgrading ' . $tableName . '... Failed: no compatible columns found.';
        $mysqli->query('DROP TABLE IF EXISTS ' . installer_ident($tempTable));
        return false;
    }

    $insertColumnsList = array();
    $selectColumns = array();
    foreach ($commonColumns as $column) {
        $insertColumnsList[] = installer_ident($column);
        $selectColumns[] = installer_migration_select_expression($tableName, $column);
    }
    $insertColumns = implode(', ', $insertColumnsList);
    $placeholders = implode(', ', array_fill(0, count($commonColumns), '?'));
    $insertSql = 'INSERT INTO ' . installer_ident($tempTable) . ' (' . $insertColumns . ') VALUES (' . $placeholders . ')';
    $insertStmt = $mysqli->prepare($insertSql);
    if (!$insertStmt) {
        $logs[] = 'Upgrading ' . $tableName . '... Failed to prepare insert: ' . $mysqli->error;
        $mysqli->query('DROP TABLE IF EXISTS ' . installer_ident($tempTable));
        return false;
    }

    $selectSql = 'SELECT ' . implode(', ', $selectColumns) . ' FROM ' . installer_ident($tableName);
    $res = $mysqli->query($selectSql);
    if (!$res) {
        $insertStmt->close();
        $mysqli->query('DROP TABLE IF EXISTS ' . installer_ident($tempTable));
        $logs[] = 'Upgrading ' . $tableName . '... Failed to read source rows: ' . $mysqli->error;
        return false;
    }

    $mysqli->begin_transaction();
    $migratedRows = 0;
    $failedRows = 0;
    while ($row = $res->fetch_assoc()) {
        $params = array();
        $types = '';
        foreach ($commonColumns as $column) {
            $types .= 's';
            $params[] = isset($row[$column]) ? (string)$row[$column] : null;
        }
        $bind = array($types);
        for ($i = 0; $i < count($params); $i++) { $bind[] = &$params[$i]; }
        call_user_func_array(array($insertStmt, 'bind_param'), $bind);
        if ($insertStmt->execute()) {
            if (installer_delete_single_row($mysqli, $tableName, $row)) {
                $migratedRows++;
            } else {
                $failedRows++;
                $logs[] = 'Row delete warning in ' . $tableName . ': ' . $mysqli->error;
            }
        } else {
            $failedRows++;
        }
    }
    $res->free();
    $insertStmt->close();
    $mysqli->commit();

    $remainingRows = installer_table_row_count($mysqli, $tableName);
    if ($remainingRows > 0) {
        if (!$mysqli->query('RENAME TABLE ' . installer_ident($tableName) . ' TO ' . installer_ident($unmigratedTable) . ', ' . installer_ident($tempTable) . ' TO ' . installer_ident($tableName))) {
            $logs[] = 'Upgrading ' . $tableName . '... Failed to rename migrated tables: ' . $mysqli->error;
            return false;
        }
        $logs[] = 'Unmigrated data left in ' . $unmigratedTable . '! ' . installer_build_csv_download_link($unmigratedTable);
    } else {
        if (!$mysqli->query('DROP TABLE IF EXISTS ' . installer_ident($tableName))) {
            $logs[] = 'Drop warning for ' . $tableName . ': ' . $mysqli->error;
        }
        if (!$mysqli->query('RENAME TABLE ' . installer_ident($tempTable) . ' TO ' . installer_ident($tableName))) {
            $logs[] = 'Upgrading ' . $tableName . '... Failed to activate migrated table: ' . $mysqli->error;
            return false;
        }
    }

    $logs[] = 'Upgrading ' . $tableName . '... Done! Migrated ' . $migratedRows . ' row(s)' . ($failedRows > 0 ? ', left ' . $failedRows . ' row(s) unmigrated.' : '.');
    return true;
}

/**
 * Insert a setting if it doesn't already exist. Used during upgrades to add
 * new settings without overwriting any value an admin has already configured.
 */
function ensure_setting($mysqli, $prefix, $name, $default_value) {
    $table = $prefix . 'settings';
    if (!table_exists_mysqli($mysqli, $table)) { return; }
    // Check if setting already exists
    $stmt = $mysqli->prepare("SELECT `value` FROM `{$table}` WHERE `name` = ?");
    if ($stmt) {
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = ($result && $result->num_rows > 0);
        $stmt->close();
        if ($exists) { return; } // Already present, don't overwrite
    }
    // Insert with default value
    $stmt = $mysqli->prepare("INSERT INTO `{$table}` (`name`,`value`) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param('ss', $name, $default_value);
        $stmt->execute();
        $stmt->close();
    }
}

function upsert_version_setting($mysqli, $prefix, $version) {
    $table = $prefix . 'settings';
    if (!table_exists_mysqli($mysqli, $table)) { return; }
    $stmt = $mysqli->prepare("UPDATE `{$table}` SET `value` = ? WHERE `name` = '_version'");
    if ($stmt) {
        $stmt->bind_param('s', $version);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) { return; }
    }
    $stmt = $mysqli->prepare("INSERT INTO `{$table}` (`name`,`value`) VALUES ('_version', ?) ");
    if ($stmt) {
        $stmt->bind_param('s', $version);
        $stmt->execute();
        $stmt->close();
    }
}

function ensure_initial_users($mysqli, $prefix, $username, $password, $displayName, $mode) {
    $table = $prefix . 'user';
    if (!table_exists_mysqli($mysqli, $table)) { return false; }

    $guestMd5 = '084e0343a0486ff05530df6c705c8bb4';
    $guestUser = 'guest';
    $guestInfo = 'Guest';
    $levelSuper = 0;
    $levelGuest = 3;

    if ($mode === 'install_clean') {
        if (!$mysqli->query("DELETE FROM `{$table}`")) { return false; }
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $u = $mysqli->real_escape_string($username);
    $d = $mysqli->real_escape_string($displayName);
    $h = $mysqli->real_escape_string($hash);

    $superSql = "INSERT INTO `{$table}` (`id`,`user`,`passwd`,`info`,`level`,`status`,`open_at`,`sort_desc`,`reporting`) "
        . "VALUES (1,'{$u}','{$h}','{$d}',{$levelSuper},'approved','d',1,0) "
        . "ON DUPLICATE KEY UPDATE `user`=VALUES(`user`), `passwd`=VALUES(`passwd`), `info`=VALUES(`info`), `level`=VALUES(`level`), `status`='approved', `open_at`='d', `sort_desc`=1, `reporting`=0";
    if (!$mysqli->query($superSql)) { return false; }

    $gUser = $mysqli->real_escape_string($guestUser);
    $gInfo = $mysqli->real_escape_string($guestInfo);
    $gHash = $mysqli->real_escape_string($guestMd5);
    $guestSql = "INSERT INTO `{$table}` (`id`,`user`,`passwd`,`info`,`level`,`status`,`open_at`,`sort_desc`,`reporting`) "
        . "VALUES (2,'{$gUser}','{$gHash}','{$gInfo}',{$levelGuest},'approved','d',1,0) "
        . "ON DUPLICATE KEY UPDATE `user`=VALUES(`user`), `passwd`=VALUES(`passwd`), `info`=VALUES(`info`), `level`=VALUES(`level`), `status`='approved', `open_at`='d', `sort_desc`=1, `reporting`=0";
    return (bool)$mysqli->query($guestSql);
}

function perform_install($cfg, $mode, $adminUser, $adminPass, $adminName, $installerVersion, $emit = null) {
    global $INSTALL_SCHEMA_TABLES, $INSTALL_SCHEMA_SEED;

    $logs = array();
    $push = function($msg) use (&$logs, $emit) {
        $logs[] = $msg;
        if ($emit) { call_user_func($emit, $msg); }
    };
    $mysqli = connect_db($cfg);
    if (!$mysqli) {
        return array(false, array('Database connection failed. Check MySQL credentials.'));
    }

    if ($mode === 'write_config') {
        $push('Writing configuration file...');
        $ok = write_mysql_config($cfg);
        $mysqli->close();
        return array($ok, array($ok ? 'Config file written to incs/mysql.inc.php.' : 'Failed to write incs/mysql.inc.php.'));
    }

    if ($mode === 'install_clean') {
        $push('Clean install selected: dropping existing tables...');
        $tables = $mysqli->query('SHOW TABLES');
        if ($tables) {
            while ($row = $tables->fetch_array(MYSQLI_NUM)) {
                if (!$mysqli->query("DROP TABLE IF EXISTS `{$row[0]}`")) {
                    $push('Drop warning: ' . $mysqli->error);
                }
            }
            $tables->free();
        }
    }

    foreach ($INSTALL_SCHEMA_TABLES as $baseName => $createBase) {
        $tableName = $cfg['prefix'] . $baseName;
        $createSql = apply_prefix($createBase, $cfg['prefix']);
        $tableLogs = array();
        $okTable = create_or_sync_table($mysqli, $tableName, $createSql, $mode, $tableLogs);
        if ($mode !== 'upgrade') {
            $push('Upgrading ' . $tableName . '... ' . ($okTable ? 'Done!' : 'Failed!'));
        }
        foreach ($tableLogs as $line) { $push($line); }
        if (!$okTable) {
            $mysqli->close();
            return array(false, $logs);
        }
    }

    foreach ($INSTALL_SCHEMA_SEED as $insertBase) {
        $seedMessages = array();
        installer_apply_seed($mysqli, $insertBase, $cfg['prefix'], $mode, $seedMessages);
        foreach ($seedMessages as $message) { $push($message); }
    }

    if ($mode === 'install_clean') {
        if (!ensure_initial_users($mysqli, $cfg['prefix'], $adminUser, $adminPass, $adminName, $mode)) {
            $push('Warning: failed to create/update initial users.');
        } else {
            $push('Super admin and guest accounts provisioned.');
        }
    } else {
        $push('Skipping super admin provisioning during upgrade.');
    }

    $push('Updating installed version setting...');
    upsert_version_setting($mysqli, $cfg['prefix'], $installerVersion);
    $push('Writing mysql config file...');
    write_mysql_config($cfg);
    $mysqli->close();

    $push('Installer version recorded as ' . $installerVersion . '.');
    return array(true, $logs);
}

$versions = tickets_get_versions();
$installerVersion = $versions['installer'];
$defaults = load_mysql_defaults();
$detection = detect_install($defaults);

session_start();
$action = isset($_POST['action']) ? (string)$_POST['action'] : '';
$installerApiActions = array('execute_step', 'execute_stream', 'execute');
$isInstallerApiCall = ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, $installerApiActions, true));
if ($detection['exists'] && !$isInstallerApiCall) {
    $isAdmin = isset($_SESSION['level']) && ((int)$_SESSION['level'] === 0 || (int)$_SESSION['level'] === 1);
    if (!$isAdmin) {
        // Show a styled login-required message instead of redirecting to an include file
        require_once __DIR__ . '/incs/versions.inc.php';
        $iv = htmlspecialchars($installerVersion, ENT_QUOTES, 'UTF-8');
        ?><!doctype html>
<html><head>
<meta charset="utf-8"><title>ticketsCAD Installer — Login Required</title>
<style>
body{background:#1a1a2e;color:#e0e0e0;font-family:Arial,sans-serif;margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:#16213e;border:1px solid #0f3460;border-radius:12px;padding:32px 40px;max-width:480px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,.4);text-align:center}
h1{margin:0 0 8px;font-size:22px;color:#e94560}
p{color:#8899aa;line-height:1.6;margin:16px 0}
.btn{display:inline-block;background:#1570ef;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:bold;font-size:16px;margin-top:8px}
.btn:hover{background:#1259c4}
</style>
</head><body>
<div class="card">
    <h1>Administrator Login Required</h1>
    <p>The ticketsCAD installer requires an administrator account to proceed. Please log in first, then return to the installer.</p>
    <a class="btn" href="index.php">Log In</a>
</div>
</body></html><?php
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download_unmigrated'])) {
    $tableName = trim((string)$_GET['download_unmigrated']);
    if ($tableName === '') {
        header('HTTP/1.1 400 Bad Request');
        echo 'Missing table name.';
        exit();
    }
    // Security: only allow safe table name characters
    if (!preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
        header('HTTP/1.1 400 Bad Request');
        echo 'Invalid table name.';
        exit();
    }
    // Security: restrict download to *_unmigrated backup tables only
    if (substr($tableName, -11) !== '_unmigrated') {
        header('HTTP/1.1 403 Forbidden');
        echo 'Only _unmigrated backup tables can be downloaded.';
        exit();
    }
    $mysqli = connect_db($defaults);
    if (!$mysqli) {
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Database connection failed.';
        exit();
    }
    installer_export_table_as_csv($mysqli, $tableName);
    $mysqli->close();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'execute_stream') {
    @set_time_limit(0);
    while (ob_get_level() > 0) { @ob_end_flush(); }
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-cache');

    $cfg = array(
        'host' => trim($_POST['db_host']),
        'user' => trim($_POST['db_user']),
        'pass' => (string)$_POST['db_pass'],
        'db' => trim($_POST['db_name']),
        'prefix' => trim($_POST['db_prefix'])
    );

    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'install_clean';
    $adminUser = trim((string)($_POST['admin_user'] ?? ''));
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $adminName = trim((string)($_POST['admin_name'] ?? ''));

    if ($mode === 'install_clean' && ($adminUser === '' || strlen($adminPass) < 6 || $adminName === '')) {
        emit_line('ERROR: Super admin user, name, and password (min 6 chars) are required.');
        emit_line('DONE:0');
        exit();
    }

    emit_line('Starting installer...');
    list($ok, $logs) = perform_install($cfg, $mode, $adminUser, $adminPass, $adminName, $installerVersion, 'emit_line');
    emit_line('DONE:' . ($ok ? '1' : '0'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'execute_step') {
    header('Content-Type: application/json');

    $cfg = array(
        'host' => trim($_POST['db_host']),
        'user' => trim($_POST['db_user']),
        'pass' => (string)$_POST['db_pass'],
        'db' => trim($_POST['db_name']),
        'prefix' => trim($_POST['db_prefix'])
    );

    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'install_clean';
    $adminUser = trim((string)($_POST['admin_user'] ?? ''));
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $adminName = trim((string)($_POST['admin_name'] ?? ''));
    $step = isset($_POST['step']) ? max(0, (int)$_POST['step']) : 0;

    if ($mode === 'install_clean' && ($adminUser === '' || strlen($adminPass) < 6 || $adminName === '')) {
        echo json_encode(array('ok' => false, 'done' => true, 'step' => $step, 'messages' => array('Super admin user, name, and password (min 6 chars) are required.')));
        exit();
    }

    if ($mode === 'write_config') {
        $ok = write_mysql_config($cfg);
        echo json_encode(array(
            'ok' => $ok,
            'done' => true,
            'step' => $step,
            'messages' => array($ok ? 'Config file written to incs/mysql.inc.php.' : 'Failed to write incs/mysql.inc.php.')
        ));
        exit();
    }

    $mysqli = connect_db($cfg);
    if (!$mysqli) {
        echo json_encode(array('ok' => false, 'done' => true, 'step' => $step, 'messages' => array('Database connection failed. Check MySQL credentials.')));
        exit();
    }

    $seed = array_values($INSTALL_SCHEMA_SEED);
    $tables = array_keys($INSTALL_SCHEMA_TABLES);
    $tableSql = array_values($INSTALL_SCHEMA_TABLES);
    $steps = installer_steps($mode, count($tables), count($seed));

    if ($step >= count($steps)) {
        $mysqli->close();
        echo json_encode(array('ok' => true, 'done' => true, 'step' => $step, 'messages' => array('Installer already complete.')));
        exit();
    }

    $current = $steps[$step];
    $messages = array();
    $ok = true;

    switch ($current['type']) {
        case 'drop_tables':
            $messages[] = 'Clean install selected: dropping existing tables...';
            $res = $mysqli->query('SHOW TABLES');
            if ($res) {
                while ($row = $res->fetch_array(MYSQLI_NUM)) {
                    if ($mysqli->query("DROP TABLE IF EXISTS `{$row[0]}`")) {
                        $messages[] = 'Dropped table: ' . $row[0];
                    } else {
                        $messages[] = 'Drop warning for ' . $row[0] . ': ' . $mysqli->error;
                    }
                }
                $res->free();
            }
            break;
        case 'schema':
            $i = (int)$current['index'];
            $baseName = $tables[$i];
            $tableName = $cfg['prefix'] . $baseName;
            $logs = array();
            $result = create_or_sync_table(
                $mysqli,
                $tableName,
                apply_prefix($tableSql[$i], $cfg['prefix']),
                $mode,
                $logs
            );
            if ($mode !== 'upgrade') {
                $messages[] = 'Upgrading ' . $tableName . '... ' . ($result ? 'Done!' : 'Failed!');
            }
            foreach ($logs as $line) { $messages[] = $line; }
            if (!$result) {
                $ok = false;
            } elseif (!table_exists_mysqli($mysqli, $tableName)) {
                $ok = false;
                $messages[] = 'Schema error: expected table missing after create/sync: ' . $baseName;
            }
            break;
        case 'seed':
            $i = (int)$current['index'];
            $insertBase = $seed[$i];
            installer_apply_seed($mysqli, $insertBase, $cfg['prefix'], $mode, $messages);
            break;
        case 'users':
            if ($mode === 'install_clean') {
                if (!ensure_initial_users($mysqli, $cfg['prefix'], $adminUser, $adminPass, $adminName, $mode)) {
                    $ok = false;
                    $messages[] = 'Failed to create/update initial users (super admin + guest).';
                } else {
                    $messages[] = 'Super admin and guest accounts provisioned.';
                }
            } else {
                $messages[] = 'Skipping super admin provisioning during upgrade.';
            }
            break;
        case 'version':
            $messages[] = 'Updating installed version setting...';
            upsert_version_setting($mysqli, $cfg['prefix'], $installerVersion);
            $messages[] = 'Installer version recorded as ' . $installerVersion . '.';
            // Ensure new settings exist (added in v3.44.0)
            ensure_setting($mysqli, $cfg['prefix'], 'tile_mode', 'online');
            ensure_setting($mysqli, $cfg['prefix'], 'tile_server_url', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png');
            ensure_setting($mysqli, $cfg['prefix'], 'tile_cache_days', '60');
            $messages[] = 'Tile settings provisioned.';
            break;
        case 'config':
            $messages[] = 'Writing mysql config file...';
            if (!write_mysql_config($cfg)) {
                $ok = false;
                $messages[] = 'Failed to write incs/mysql.inc.php.';
            } else {
                $messages[] = 'Config file written to incs/mysql.inc.php.';
            }
            break;
    }

    $mysqli->close();
    echo json_encode(array(
        'ok' => $ok,
        'done' => ($step + 1) >= count($steps),
        'step' => $step,
        'next_step' => $step + 1,
        'total_steps' => count($steps),
        'messages' => $messages
    ));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'execute') {
    header('Content-Type: application/json');
    $cfg = array(
        'host' => trim($_POST['db_host']),
        'user' => trim($_POST['db_user']),
        'pass' => (string)$_POST['db_pass'],
        'db' => trim($_POST['db_name']),
        'prefix' => trim($_POST['db_prefix'])
    );

    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'install_clean';
    $adminUser = trim((string)($_POST['admin_user'] ?? ''));
    $adminPass = (string)($_POST['admin_pass'] ?? '');
    $adminName = trim((string)($_POST['admin_name'] ?? ''));

    if ($mode === 'install_clean') {
        if ($adminUser === '' || strlen($adminPass) < 6 || $adminName === '') {
            echo json_encode(array('ok' => false, 'logs' => array('Super admin user, name, and password (min 6 chars) are required.')));
            exit();
        }
    }

    list($ok, $logs) = perform_install($cfg, $mode, $adminUser, $adminPass, $adminName, $installerVersion);
    echo json_encode(array('ok' => $ok, 'logs' => $logs));
    exit();
}

$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown';
$modeDefault = $detection['exists'] ? 'upgrade' : 'install_clean';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>ticketsCAD Installer</title>
<link rel="stylesheet" href="default.css" type="text/css">
<style>
body{background:#f5f7fb;color:#1d2939;font-family:Arial,sans-serif;margin:0}
.wrap{max-width:980px;margin:28px auto;padding:0 16px}
.card{background:#fff;border:1px solid #d0d5dd;border-radius:12px;padding:18px 20px;box-shadow:0 4px 12px rgba(16,24,40,.08)}
h1{margin:0 0 8px;font-size:28px}.muted{color:#475467}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
label{font-weight:bold;display:block;margin-bottom:4px} input,select{width:100%;padding:9px;border:1px solid #98a2b3;border-radius:8px}
.mode-switch{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:4px}
.mode-option{position:relative;display:block;border:1px solid #d0d5dd;border-radius:10px;padding:10px 12px;background:#fff;cursor:pointer;transition:.2s}
.mode-option input{position:absolute;opacity:0;pointer-events:none}
.mode-title{display:block;font-weight:700;color:#101828;font-size:13px}
.mode-desc{display:block;color:#667085;font-size:12px;margin-top:4px;line-height:1.3}
.mode-option.active{border-color:#1570ef;background:#eef4ff;box-shadow:inset 0 0 0 1px #1570ef}
.mode-option.disabled{opacity:.55;cursor:not-allowed;background:#f9fafb}
.badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#eef4ff;color:#3538cd;font-weight:bold;font-size:12px}
.btn{background:#1570ef;color:#fff;border:none;padding:11px 16px;border-radius:10px;font-weight:bold;cursor:pointer}
.btn-primary-lg{font-size:18px;padding:14px 24px;border-radius:12px}
.btn:disabled{opacity:.6;cursor:not-allowed}
#progress{display:none;margin-top:14px}.spinner{width:28px;height:28px;border:4px solid #d1e0ff;border-top-color:#1570ef;border-radius:50%;animation:spin .8s linear infinite;display:inline-block;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}
#log{margin-top:12px;background:#101828;color:#d0d5dd;padding:12px;border-radius:8px;max-height:260px;overflow:auto;font-family:monospace;font-size:12px;white-space:pre-wrap}
.notice{padding:8px 10px;border-radius:8px;background:#fffaeb;border:1px solid #fedf89;margin-bottom:10px}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>ticketsCAD Installer <span class="badge"><?php echo h($installerVersion); ?></span></h1>
    <p class="muted">Installed version: <strong><?php echo h($detection['installed_version'] === null ? 'not detected' : $detection['installed_version']); ?></strong></p>
    <p class="muted">Latest GitHub release: <strong><?php echo h(getLatestGitHubRelease('openises', 'tickets')); ?></strong></p>      
    <?php if ($detection['legacy']) { ?><div class="notice">Detected settings table without a _version value. This appears to be an unknown legacy install.</div><?php } ?>
    <?php
    $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
    if ($reason === 'version_mismatch') {
        $instVer = isset($_GET['installed']) ? h($_GET['installed']) : '?';
        $currVer = isset($_GET['current']) ? h($_GET['current']) : '?';
        echo '<div class="notice" style="background:#fef3f2;border-color:#fecdca;color:#b42318;">';
        echo '<strong>Version mismatch detected.</strong> ';
        echo 'Your database is at <strong>' . $instVer . '</strong> but the application files are <strong>' . $currVer . '</strong>. ';
        echo 'Please run an <strong>Upgrade</strong> to sync the database schema with the current release.';
        echo '</div>';
    } elseif ($reason === 'no_database') {
        echo '<div class="notice" style="background:#fef3f2;border-color:#fecdca;color:#b42318;">';
        echo '<strong>No database configuration found.</strong> ';
        echo 'Please configure your database connection and run a clean install.';
        echo '</div>';
    }
    ?>
    <p class="muted">System details: PHP <?php echo h(PHP_VERSION); ?> · OS <?php echo h(PHP_OS); ?> · Web <?php echo h($serverSoftware); ?> · DB <?php echo h($detection['db_version'] ? $detection['db_version'] : 'not connected'); ?></p>

    <form id="installerForm">
      <div class="grid">
        <div><label>Mode</label>
          <div class="mode-switch" id="modeSwitch">
            <label class="mode-option <?php echo $modeDefault === 'install_clean' ? 'active' : ''; ?>">
              <input type="radio" name="mode" value="install_clean" <?php echo $modeDefault === 'install_clean' ? 'checked' : ''; ?>>
              <span class="mode-title">Install / Reinstall</span>
              <span class="mode-desc">Drop all tables and rebuild clean.</span>
            </label>
            <label class="mode-option <?php echo $modeDefault === 'upgrade' ? 'active' : ''; ?> <?php echo $detection['exists'] ? '' : 'disabled'; ?>">
              <input type="radio" name="mode" value="upgrade" <?php echo $modeDefault === 'upgrade' ? 'checked' : ''; ?> <?php echo $detection['exists'] ? '' : 'disabled'; ?>>
              <span class="mode-title">Upgrade</span>
              <span class="mode-desc">Create missing tables and sync columns.</span>
            </label>
            <label class="mode-option">
              <input type="radio" name="mode" value="write_config">
              <span class="mode-title">Write Config</span>
              <span class="mode-desc">Save DB credentials only.</span>
            </label>
          </div>
        </div>
        <div><label>Table prefix (optional)</label><input name="db_prefix" value="<?php echo h($defaults['prefix']); ?>"></div>
        <div><label>MySQL host</label><input name="db_host" required value="<?php echo h($defaults['host']); ?>"></div>
        <div><label>MySQL database</label><input name="db_name" required value="<?php echo h($defaults['db']); ?>"></div>
        <div><label>MySQL username</label><input name="db_user" required value="<?php echo h($defaults['user']); ?>"></div>
        <div><label>MySQL password</label><input type="password" name="db_pass" value="<?php echo h($defaults['pass']); ?>"></div>

        <div id="adminFields" style="display:<?php echo $modeDefault === 'install_clean' ? 'contents' : 'none'; ?>;">
          <div><label>Super admin username</label><input id="admin_user" name="admin_user" value="admin"></div>
          <div><label>Super admin display name</label><input id="admin_name" name="admin_name" value="Super Administrator"></div>
          <div><label>Super admin password</label><input type="password" id="admin_pass" name="admin_pass"></div>
          <div><label>Confirm password</label><input type="password" id="admin_pass_confirm"></div>
        </div>
      </div>
      <p id="passStatus" class="muted"></p>
      <button class="btn btn-primary-lg" id="runBtn" type="submit">Do It</button>
      <button class="btn" id="resetBtn" type="reset" style="background:#475467;margin-left:8px;">Reset Form</button>
    </form>

    <div id="progress"><span class="spinner"></span> <strong>Working...</strong></div>
    <div id="log"></div>
  </div>
</div>
<script>
(function(){
  var f=document.getElementById('installerForm'),log=document.getElementById('log'),prog=document.getElementById('progress'),btn=document.getElementById('runBtn');
  var pass=document.getElementById('admin_pass'),confirmPass=document.getElementById('admin_pass_confirm'),status=document.getElementById('passStatus');
  var adminFields=document.getElementById('adminFields');
  var adminUser=document.getElementById('admin_user'),adminName=document.getElementById('admin_name');
  var modeInputs=[].slice.call(document.querySelectorAll('input[name="mode"]'));
  var installerMeta={
    tables: <?php echo json_encode(array_keys($INSTALL_SCHEMA_TABLES)); ?>,
    seedCount: <?php echo json_encode(count($INSTALL_SCHEMA_SEED)); ?>
  };
  function getMode(){ var c=modeInputs.find(function(i){return i.checked;}); return c?c.value:'install_clean'; }
  function updateModeCards(){ modeInputs.forEach(function(i){ var c=i.closest('.mode-option'); if(!c){return;} c.classList.toggle('active', i.checked); }); }
  function updateAdminFields(){
    var isInstall=(getMode()==='install_clean');
    if(adminFields){ adminFields.style.display=isInstall?'contents':'none'; }
    [adminUser, adminName, pass, confirmPass].forEach(function(el){ if(!el){ return; } el.disabled=!isInstall; });
    if(!isInstall){ status.textContent=''; }
  }
  function previewStepLine(step){
    var mode=getMode();
    var offset=(mode==='install_clean')?1:0;
    if(mode==='install_clean' && step===0){ return 'Clean install selected: dropping existing tables...'; }
    if(step >= offset && step < offset + installerMeta.tables.length){
      var prefix=(f.elements.db_prefix && f.elements.db_prefix.value)?f.elements.db_prefix.value:'';
      return 'Upgrading ' + prefix + installerMeta.tables[step - offset] + '...';
    }
    return null;
  }

  function validatePass(){
    if(getMode()!=='install_clean'){status.textContent='';return true;}
    if(pass.value.length<6){status.textContent='Password must be at least 6 characters.';status.style.color='#b42318';return false;}
    if(pass.value!==confirmPass.value){status.textContent='Passwords do not match.';status.style.color='#b42318';return false;}
    status.textContent='Passwords match.';status.style.color='#027a48';return true;
  }

  pass.addEventListener('input',validatePass);
  confirmPass.addEventListener('input',validatePass);
  modeInputs.forEach(function(i){ i.addEventListener('change', function(){ updateModeCards(); updateAdminFields(); validatePass(); }); });
  updateModeCards();
  updateAdminFields();

  function escapeHtml(value){
    return String(value).replace(/[&<>"']/g, function(ch){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch];
    });
  }

  var pendingUpgradeLine = null;

  function renderLogLine(target, line){
    // Check if line contains a server-generated download link
    var linkMatch = line.match(/^(.*?)(<a\s+href="[^"]*"[^>]*>[^<]*<\/a>)(.*)$/i);
    if(linkMatch){
      // Build link safely via DOM instead of innerHTML
      target.textContent = linkMatch[1];
      var tmp = document.createElement('span');
      tmp.innerHTML = linkMatch[2]; // only the <a> tag from server
      if(tmp.firstChild && tmp.firstChild.tagName === 'A') {
        target.appendChild(tmp.firstChild);
      }
      if(linkMatch[3]) {
        target.appendChild(document.createTextNode(linkMatch[3]));
      }
    } else {
      target.textContent = line;
    }
  }

  function appendLines(lines){
    if(!lines || !lines.length){ return; }
    lines.forEach(function(line){
      var pendingMatch = line.match(/^(Upgrading .*\.\.\.)$/);
      if(pendingMatch){
        if(pendingUpgradeLine){
          var currentPending = pendingUpgradeLine.getAttribute('data-line') || '';
          if(currentPending === line){
            return;
          }
          renderLogLine(pendingUpgradeLine, currentPending || pendingUpgradeLine.textContent || '');
        }
        var pendingDiv=document.createElement('div');
        pendingDiv.setAttribute('data-line', line);
        renderLogLine(pendingDiv, line);
        log.appendChild(pendingDiv);
        pendingUpgradeLine = pendingDiv;
        return;
      }

      if(pendingUpgradeLine){
        var pendingText = pendingUpgradeLine.getAttribute('data-line') || '';
        if(pendingText !== '' && line.indexOf(pendingText) === 0){
          pendingUpgradeLine.setAttribute('data-line', line);
          renderLogLine(pendingUpgradeLine, line);
          pendingUpgradeLine = null;
          return;
        }
      }

      var div=document.createElement('div');
      renderLogLine(div, line);
      log.appendChild(div);
    });
    log.scrollTop = log.scrollHeight;
  }

  function runStep(step){
    var previewLine = previewStepLine(step);
    if(previewLine){ appendLines([previewLine]); }

    var data = new FormData(f);
    data.append('action','execute_step');
    data.append('step', String(step));

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'install.php', true);
    xhr.onload = function(){
      var payload = null;
      try { payload = JSON.parse(xhr.responseText || '{}'); }
      catch (err) {
        appendLines(['Installer step parse error.', xhr.responseText || 'No response body.', 'Hint: if you see login HTML here, installer auth redirect intercepted the API call.']);
        prog.style.display='none';
        btn.disabled=false;
        document.getElementById('resetBtn').disabled=false;
        return;
      }

      appendLines(payload.messages || []);
      if(payload.ok === false){
        appendLines(['Installation stopped due to error.']);
        prog.style.display='none';
        btn.disabled=false;
        document.getElementById('resetBtn').disabled=false;
        return;
      }

      if(payload.done){
        prog.style.display='none';
        appendLines(['Install complete. Open: index.php']);
        if(!document.getElementById('doneLink')){
          var doneLink = document.createElement('a');
          doneLink.id='doneLink';
          doneLink.href='index.php'; doneLink.textContent='Go to TicketsCAD';
          doneLink.className='btn btn-primary-lg';
          doneLink.style.display='inline-block'; doneLink.style.marginTop='12px'; doneLink.style.fontWeight='bold';
          log.parentNode.appendChild(doneLink);
        }
        btn.textContent='Done';
        btn.disabled=true;
        return;
      }

      runStep(payload.next_step || (step + 1));
    };
    xhr.onerror = function(){
      prog.style.display='none';
      appendLines(['Installer request failed.']);
      btn.disabled=false;
      document.getElementById('resetBtn').disabled=false;
    };
    xhr.send(data);
  }

  f.addEventListener('submit', function(e){
    e.preventDefault();
    if(!validatePass()){return;}

    prog.style.display='block';
    btn.disabled=true;
    document.getElementById('resetBtn').disabled=true;
    log.textContent='';
    var oldDone = document.getElementById('doneLink');
    if(oldDone){ oldDone.parentNode.removeChild(oldDone); }
    appendLines(['Starting installer...']);
    runStep(0);
  });
})();
</script>
</body>
</html>
