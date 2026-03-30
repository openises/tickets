<?php
/**
 * Clone a MySQL/MariaDB database for testing.
 *
 * Usage: php tools/clone_db.php [source_db] [target_db]
 * Defaults: source=tickets, target=tickets_test
 *
 * Copies all tables (structure + data) from source to target.
 * Drops and recreates target if it already exists.
 */

$sourceDb = $argv[1] ?? 'tickets';
$targetDb = $argv[2] ?? 'tickets_test';

// Load DB credentials from the tickets config
require_once __DIR__ . '/../incs/mysql.inc.php';

// Use root for CREATE DATABASE (the app user typically lacks this privilege)
$host = $mysql_host ?? 'localhost';
echo "MySQL root user for '{$host}'\n";
echo "Username [root]: ";
$user = trim(fgets(STDIN)) ?: 'root';
echo "Password: ";
// Hide password input on Windows
if (PHP_OS_FAMILY === 'Windows') {
    $pass = trim(shell_exec('powershell -Command "$p = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"'));
} else {
    system('stty -echo');
    $pass = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
}

echo "Cloning '{$sourceDb}' → '{$targetDb}'...\n";

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

// Create target database
$mysqli->query("DROP DATABASE IF EXISTS `{$targetDb}`");
$mysqli->query("CREATE DATABASE `{$targetDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Created database '{$targetDb}'\n";

// Get all tables from source
$tables = [];
$result = $mysqli->query("SHOW TABLES FROM `{$sourceDb}`");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}
echo count($tables) . " tables to copy\n\n";

foreach ($tables as $table) {
    // Copy structure
    $mysqli->query("CREATE TABLE `{$targetDb}`.`{$table}` LIKE `{$sourceDb}`.`{$table}`");
    // Copy data
    $mysqli->query("INSERT INTO `{$targetDb}`.`{$table}` SELECT * FROM `{$sourceDb}`.`{$table}`");

    $count = $mysqli->affected_rows;
    echo "  {$table}: {$count} rows\n";
}

$mysqli->close();
echo "\nDone. Test database '{$targetDb}' is ready.\n";
echo "To remove it later: DROP DATABASE `{$targetDb}`;\n";
