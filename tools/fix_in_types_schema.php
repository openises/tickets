<?php
/**
 * Fix in_types Table Schema
 *
 * Older TicketsCAD installations have the in_types table with many columns
 * marked as NOT NULL without defaults, which forces the form validation to
 * require all fields. This script changes optional fields to allow NULL
 * with sensible defaults, matching the current DB_FULL.sql schema.
 *
 * Only 'type' and 'description' should be truly required.
 *
 * Usage: Open in browser or run via CLI:
 *   php tools/fix_in_types_schema.php
 *
 * Safe to run multiple times — uses ALTER TABLE MODIFY which is idempotent.
 */

// Load TicketsCAD database connection
$self = 'fix_in_types_schema';
$no_session = true;
require_once dirname(__FILE__) . '/../incs/functions.inc.php';
require_once dirname(__FILE__) . '/../db.inc.php';

$prefix = isset($GLOBALS['mysql_prefix']) ? $GLOBALS['mysql_prefix'] : '';
$table = $prefix . 'in_types';

echo "<pre>\n";
echo "=== Fix in_types Schema — Make Optional Fields Nullable ===\n\n";

// These columns should allow NULL with defaults
// Format: column => [new_type, default, comment]
$fixes = [
    'protocol'         => ["TEXT DEFAULT NULL", "Protocol text (optional)"],
    'group'            => ["VARCHAR(20) DEFAULT NULL", "Incident group (optional)"],
    'sort'             => ["INT(11) DEFAULT NULL", "Sort order (optional)"],
    'radius'           => ["INT(4) DEFAULT NULL", "Map radius (optional)"],
    'color'            => ["VARCHAR(8) DEFAULT NULL", "Map color hex (optional)"],
    'opacity'          => ["INT(3) DEFAULT NULL", "Map opacity (optional)"],
    'notify_mailgroup' => ["INT(4) DEFAULT NULL", "Notification mail list ID (optional)"],
    'notify_email'     => ["VARCHAR(256) DEFAULT NULL", "Notification email (optional)"],
];

// These should stay NOT NULL (they are truly required)
// 'type' varchar(20) NOT NULL
// 'description' varchar(60) NOT NULL

// Check current schema
$result = db_query("SHOW COLUMNS FROM `$table`");
$current = [];
while ($row = $result->fetch_assoc()) {
    $current[$row['Field']] = $row;
}

$changed = 0;
foreach ($fixes as $col => $fix) {
    if (!isset($current[$col])) {
        echo "[SKIP] Column '$col' does not exist in table\n";
        continue;
    }

    $row = $current[$col];
    $isNullable = ($row['Null'] === 'YES');

    if ($isNullable) {
        echo "[OK]   '$col' already allows NULL\n";
        continue;
    }

    // Column is NOT NULL — fix it
    $typeDef = $fix[0];
    $comment = $fix[1];
    $sql = "ALTER TABLE `$table` MODIFY COLUMN `$col` $typeDef COMMENT '$comment'";

    try {
        db_query($sql);
        echo "[FIXED] '$col' changed to allow NULL ($typeDef)\n";
        $changed++;
    } catch (Exception $e) {
        echo "[ERROR] '$col': " . $e->getMessage() . "\n";
    }
}

// Also ensure protocol is TEXT not VARCHAR(255) for longer protocols
if (isset($current['protocol'])) {
    $currentType = strtolower($current['protocol']['Type']);
    if (strpos($currentType, 'varchar') !== false) {
        try {
            db_query("ALTER TABLE `$table` MODIFY COLUMN `protocol` TEXT DEFAULT NULL COMMENT 'Protocol text (optional)'");
            echo "[FIXED] 'protocol' changed from VARCHAR to TEXT\n";
            $changed++;
        } catch (Exception $e) {
            echo "[NOTE]  'protocol' type change: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n";
if ($changed > 0) {
    echo "Done. $changed column(s) updated.\n";
    echo "The incident type form should now only require 'Type' and 'Description'.\n";
} else {
    echo "No changes needed — schema is already correct.\n";
}
echo "</pre>\n";
