<?php
/**
 * TicketsCAD Docker Auto-Install Script
 *
 * Called by docker-entrypoint.sh on first run when the database has no tables.
 * Reads configuration from environment variables.
 *
 * Usage: php docker-autoinstall.php [check|install]
 *   check   - Returns "yes" if tables exist, "no" if empty, "error" on failure
 *   install - Creates all tables, seeds data, provisions admin user
 */

$action = $argv[1] ?? 'check';

$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'tickets';
$pass = getenv('DB_PASS') ?: 'tickets';
$db   = getenv('DB_NAME') ?: 'tickets';

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    if ($action === 'check') { echo 'error'; exit(1); }
    echo "Database connection failed: " . $mysqli->connect_error . "\n";
    exit(1);
}

// ── Check mode ──
if ($action === 'check') {
    $r = $mysqli->query('SHOW TABLES LIKE "settings"');
    echo ($r && $r->num_rows > 0) ? 'yes' : 'no';
    $mysqli->close();
    exit(0);
}

// ── Install mode ──
if ($action !== 'install') {
    echo "Usage: php docker-autoinstall.php [check|install]\n";
    exit(1);
}

echo "TicketsCAD: Auto-installing...\n";

// Load the schema
require_once '/var/www/html/incs/compat.inc.php';
require_once '/var/www/html/incs/security.inc.php';
require_once '/var/www/html/incs/install_schema.inc.php';

// Create tables
$created = 0;
$errors = 0;
foreach ($INSTALL_SCHEMA_TABLES as $name => $sql) {
    if (!$mysqli->query($sql)) {
        echo "  Warning: $name — " . $mysqli->error . "\n";
        $errors++;
    } else {
        $created++;
    }
}
echo "Created $created tables ($errors warnings).\n";

// Seed data
$seeded = 0;
foreach ($INSTALL_SCHEMA_SEED as $sql) {
    if ($mysqli->query($sql)) $seeded++;
}
echo "Seeded $seeded data sets.\n";

// Create admin user
$adminUser = getenv('ADMIN_USER') ?: 'admin';
$adminPass = hash_password(getenv('ADMIN_PASS') ?: 'admin');
$adminName = getenv('ADMIN_NAME') ?: 'Super Administrator';

$mysqli->query("DELETE FROM user WHERE id = 1");
$stmt = $mysqli->prepare("INSERT INTO user (id, user, passwd, info, level, status, open_at, sort_desc, reporting) VALUES (1, ?, ?, ?, 0, 'approved', 'd', 1, 0)");
$stmt->bind_param('sss', $adminUser, $adminPass, $adminName);
$stmt->execute();
$stmt->close();
echo "Admin user '$adminUser' created.\n";

// Create guest user
$guestPass = md5('guest');
$mysqli->query("INSERT IGNORE INTO user (id, user, passwd, info, level, status, open_at, sort_desc, reporting) VALUES (2, 'guest', '" . $mysqli->real_escape_string($guestPass) . "', 'Guest', 3, 'approved', 'd', 1, 0)");
echo "Guest user created.\n";

// Set version
$mysqli->query("UPDATE settings SET value = '3.44.1' WHERE name = '_version'");
if ($mysqli->affected_rows === 0) {
    $mysqli->query("INSERT INTO settings (name, value) VALUES ('_version', '3.44.1')");
}

// Set tile mode to proxy for Docker
$mysqli->query("UPDATE settings SET value = 'proxy' WHERE name = 'tile_mode'");
if ($mysqli->affected_rows === 0) {
    $mysqli->query("INSERT INTO settings (name, value) VALUES ('tile_mode', 'proxy')");
}

$mysqli->close();
echo "Auto-install complete.\n";
