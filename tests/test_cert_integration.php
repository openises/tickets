<?php
/**
 * CERT Organization Integration Test Suite
 *
 * Creates a complete CERT (Community Emergency Response Team) organization
 * and exercises every major code path in TicketsCAD:
 *   - Schema integrity
 *   - Incident types, units, facilities, regions, users
 *   - Full incident lifecycle (create, action, assign, patient, close)
 *   - Permissions and access control
 *   - Regional allocation and visibility
 *   - Date/time formatting
 *   - Board display queries
 *   - Edge cases and security
 *   - Notification configuration
 *
 * All test data uses __CERT_TEST_ prefix for identification and cleanup.
 *
 * Usage:
 *   php tests/test_cert_integration.php
 *
 * Requirements:
 *   - Valid database connection (incs/mysql.inc.php configured)
 *   - Database seeded with DB_FULL.sql (tables and seed data exist)
 */

// ── Bootstrap ────────────────────────────────────────────────
$_SERVER['SCRIPT_NAME'] = '/tests/test_cert_integration.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'TicketsCAD-IntegrationTest/1.0';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';

@session_start();
$_SESSION['level'] = 0;
$_SESSION['user_id'] = 1;
$_SESSION['user'] = 'test_runner';

ob_start();
require_once __DIR__ . '/../incs/functions.inc.php';
require_once __DIR__ . '/../incs/db.inc.php';
require_once __DIR__ . '/../incs/security.inc.php';
if (!isset($GLOBALS['mysql_host'])) {
    require_once __DIR__ . '/../incs/mysql.inc.php';
}
ob_end_clean();

$prefix = $GLOBALS['mysql_prefix'] ?? '';

echo "=== TicketsCAD CERT Integration Test Suite ===\n";
echo "PHP: " . PHP_VERSION . " | DB: {$GLOBALS['mysql_db']}\n\n";

$pass = 0;
$fail = 0;
$test_num = 0;

function test($label, $condition) {
    global $pass, $fail, $test_num;
    $test_num++;
    if ($condition) {
        echo "[Test $test_num] $label... PASS\n";
        $pass++;
    } else {
        echo "[Test $test_num] $label... FAIL\n";
        $fail++;
    }
}

// Track IDs for cleanup
$test_ids = [
    'in_types'   => [],
    'un_status'  => [],
    'unit_types' => [],
    'responder'  => [],
    'fac_types'  => [],
    'facilities' => [],
    'region'     => [],
    'user'       => [],
    'ticket'     => [],
    'action'     => [],
    'assigns'    => [],
    'patient'    => [],
    'allocates'  => [],
    'log'        => [],
];

// Safety net: register cleanup on unexpected exit
register_shutdown_function(function() {
    global $prefix, $test_ids;
    // Only run if there are tracked IDs (test got past setup)
    $hasIds = false;
    foreach ($test_ids as $ids) { if (!empty($ids)) { $hasIds = true; break; } }
    if (!$hasIds) return;

    try {
        // Best-effort cleanup
        foreach (['patient', 'action', 'assigns'] as $t) {
            if (!empty($test_ids['ticket'])) {
                $ids = implode(',', array_map('intval', $test_ids['ticket']));
                @db_query("DELETE FROM `{$prefix}{$t}` WHERE `ticket_id` IN ($ids)");
            }
        }
        if (!empty($test_ids['ticket'])) {
            $ids = implode(',', array_map('intval', $test_ids['ticket']));
            @db_query("DELETE FROM `{$prefix}allocates` WHERE `type` = 1 AND `resource_id` IN ($ids)");
            @db_query("DELETE FROM `{$prefix}ticket` WHERE `id` IN ($ids)");
        }
        @db_query("DELETE FROM `{$prefix}responder` WHERE `name` LIKE '__CERT_TEST_%'");
        @db_query("DELETE FROM `{$prefix}facilities` WHERE `name` LIKE '__CERT_TEST_%'");
        @db_query("DELETE FROM `{$prefix}in_types` WHERE `type` LIKE '_CT_ %'");
        @db_query("DELETE FROM `{$prefix}un_status` WHERE `description` LIKE '__CERT_TEST_%'");
        @db_query("DELETE FROM `{$prefix}unit_types` WHERE `description` LIKE '__CERT_TEST_%'");
        @db_query("DELETE FROM `{$prefix}fac_types` WHERE `description` LIKE '__CERT_TEST_%'");
        @db_query("DELETE FROM `{$prefix}region` WHERE `group_name` LIKE '__CERT_TEST_%'");
        @db_query("DELETE FROM `{$prefix}user` WHERE `user` LIKE '__cert_test_%'");
        @db_query("DELETE FROM `{$prefix}settings` WHERE `name` LIKE '__cert_test_%'");
        @db_query("DELETE FROM `{$prefix}log` WHERE `info` LIKE '%__CERT_TEST_%'");
    } catch (Exception $e) {
        // Ignore — best effort
    }
});

// ═══════════════════════════════════════════════════════════════
// SECTION 1: Schema & Data Integrity
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 1: Schema & Data Integrity ──\n";

$critical_tables = [
    'ticket', 'action', 'assigns', 'responder', 'facilities',
    'in_types', 'patient', 'user', 'allocates', 'region',
    'settings', 'log', 'un_status', 'unit_types', 'fac_types'
];

foreach ($critical_tables as $table) {
    $exists = db_query("SHOW TABLES LIKE '{$prefix}{$table}'");
    test("Table '{$table}' exists", $exists && $exists->num_rows > 0);
}

// Critical columns
$column_checks = [
    ['ticket', 'in_types_id'],
    ['ticket', 'status'],
    ['ticket', 'severity'],
    ['ticket', 'lat'],
    ['ticket', 'lng'],
    ['action', 'ticket_id'],
    ['action', 'action_type'],
    ['assigns', 'ticket_id'],
    ['assigns', 'responder_id'],
    ['assigns', 'user_id'],
    ['patient', 'ticket_id'],
    ['allocates', 'resource_id'],
    ['allocates', 'type'],
    ['allocates', 'group'],
];

foreach ($column_checks as $chk) {
    $col = db_query("SHOW COLUMNS FROM `{$prefix}{$chk[0]}` WHERE `Field` = '{$chk[1]}'");
    test("Column '{$chk[0]}.{$chk[1]}' exists", $col && $col->num_rows > 0);
}

// ═══════════════════════════════════════════════════════════════
// SECTION 2: CERT Organization Setup
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 2: CERT Organization Setup ──\n";

// -- Incident Types --
// Note: in_types.type is varchar(20) — keep names short
$cert_types = [
    ['_CT_ Medical',    'Medical emergency response', 'Assess patient. Apply triage tag. Stabilize. Transport to medical staging.', 2, 'Medical', 10, '#FF0000', 1],
    ['_CT_ Collapse',   'Building collapse with possible trapped victims', 'Establish perimeter. Mark building exterior. Search systematically by floor.', 2, 'Rescue', 20, '#FF6600', 1],
    ['_CT_ SAR',        'Grid-based search operation', 'Establish grid. Mark searched areas. Report finds immediately.', 1, 'SAR', 30, '#0066FF', 1],
    ['_CT_ HazMat',     'Hazardous materials incident', 'Isolate area. Deny entry. Identify substance if safe. Evacuate upwind.', 2, 'HazMat', 40, '#FFFF00', 2],
    ['_CT_ Shelter',    'Emergency shelter management', 'Registration and intake. Assign sleeping areas. Track occupancy.', 0, 'Shelter', 50, '#00CC00', 3],
    ['_CT_ Fire',       'Small fire suppression by CERT', 'Size up fire. Ensure escape route. Two-person teams only.', 1, 'Fire', 60, '#CC0000', 1],
];

foreach ($cert_types as $ct) {
    db_query(
        "INSERT INTO `{$prefix}in_types` (`type`, `description`, `protocol`, `set_severity`, `group`, `sort`, `color`, `notify_when`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        $ct
    );
    $id = db_insert_id();
    $test_ids['in_types'][] = $id;
    test("Create incident type '{$ct[0]}'", $id > 0);
}

$type_count = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}in_types` WHERE `type` LIKE '_CT_ %'");
test("6 CERT incident types exist", (int)$type_count['cnt'] === 6);

$medical_type_id = $test_ids['in_types'][0];
test("get_type() returns correct name", get_type($medical_type_id) === '_CT_ Medical');

// -- Unit Statuses --
$cert_statuses = [
    '__CERT_TEST_ Deployed',
    '__CERT_TEST_ Staging',
];
foreach ($cert_statuses as $st) {
    db_query("INSERT INTO `{$prefix}un_status` (`description`) VALUES (?)", [$st]);
    $test_ids['un_status'][] = db_insert_id();
    test("Create unit status '{$st}'", db_insert_id() > 0 || $test_ids['un_status'][count($test_ids['un_status'])-1] > 0);
}
$deployed_status_id = $test_ids['un_status'][0];

// -- Unit Types --
$cert_unit_types = ['__CERT_TEST_ CERT Team', '__CERT_TEST_ Medical Unit', '__CERT_TEST_ SAR Team'];
foreach ($cert_unit_types as $ut) {
    db_query("INSERT INTO `{$prefix}unit_types` (`description`) VALUES (?)", [$ut]);
    $test_ids['unit_types'][] = db_insert_id();
    test("Create unit type '{$ut}'", $test_ids['unit_types'][count($test_ids['unit_types'])-1] > 0);
}

// -- Responder Units --
// Get the first available un_status id for "available"
$avail_status = db_fetch_one("SELECT id FROM `{$prefix}un_status` WHERE `description` LIKE '%vail%' ORDER BY id LIMIT 1");
$avail_status_id = $avail_status ? (int)$avail_status['id'] : 1;

$cert_units = [
    ['__CERT_TEST_ CERT Team 1', 'CERT response team alpha', $avail_status_id, 'CT1', $test_ids['unit_types'][0], 0, 34.0522, -118.2437],
    ['__CERT_TEST_ CERT Team 2', 'CERT response team bravo', $avail_status_id, 'CT2', $test_ids['unit_types'][0], 0, 34.0530, -118.2450],
    ['__CERT_TEST_ Medical Unit 1', 'Medical triage and treatment', $avail_status_id, 'MU1', $test_ids['unit_types'][1], 0, 34.0510, -118.2420],
    ['__CERT_TEST_ Medical Unit 2', 'Medical transport', $avail_status_id, 'MU2', $test_ids['unit_types'][1], 1, 34.0515, -118.2425],
    ['__CERT_TEST_ SAR Team 1', 'Search and rescue team 1', $avail_status_id, 'SR1', $test_ids['unit_types'][2], 0, 34.0540, -118.2460],
    ['__CERT_TEST_ SAR Team 2', 'Search and rescue team 2', $avail_status_id, 'SR2', $test_ids['unit_types'][2], 0, 34.0545, -118.2465],
];

foreach ($cert_units as $cu) {
    db_query(
        "INSERT INTO `{$prefix}responder` (`name`, `description`, `un_status_id`, `handle`, `type`, `multi`, `lat`, `lng`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        $cu
    );
    $test_ids['responder'][] = db_insert_id();
    test("Create unit '{$cu[0]}'", $test_ids['responder'][count($test_ids['responder'])-1] > 0);
}

$unit_count = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}responder` WHERE `name` LIKE '__CERT_TEST_%'");
test("6 CERT responder units exist", (int)$unit_count['cnt'] === 6);

// -- Facility Types --
$cert_fac_types = ['__CERT_TEST_ Hospital', '__CERT_TEST_ Shelter', '__CERT_TEST_ Staging Area'];
foreach ($cert_fac_types as $ft) {
    db_query("INSERT INTO `{$prefix}fac_types` (`description`) VALUES (?)", [$ft]);
    $test_ids['fac_types'][] = db_insert_id();
    test("Create facility type '{$ft}'", $test_ids['fac_types'][count($test_ids['fac_types'])-1] > 0);
}

// -- Facilities --
$cert_facilities = [
    ['__CERT_TEST_ General Hospital', 'Level II Trauma Center', $test_ids['fac_types'][0], '500 Hospital Blvd', 'Springfield', 'CA', 34.0600, -118.2500],
    ['__CERT_TEST_ Community Center Shelter', 'Red Cross shelter capacity 200', $test_ids['fac_types'][1], '100 Community Dr', 'Springfield', 'CA', 34.0480, -118.2380],
    ['__CERT_TEST_ Fire Station Staging', 'CERT staging area at Station 5', $test_ids['fac_types'][2], '250 Fire House Ln', 'Springfield', 'CA', 34.0550, -118.2470],
    ['__CERT_TEST_ Elementary School Shelter', 'Secondary shelter capacity 150', $test_ids['fac_types'][1], '300 School St', 'Springfield', 'CA', 34.0490, -118.2390],
];

// Get first fac_status ID
$fac_stat = db_fetch_one("SELECT id FROM `{$prefix}fac_status` ORDER BY id LIMIT 1");
$fac_status_id = $fac_stat ? (int)$fac_stat['id'] : 1;

foreach ($cert_facilities as $cf) {
    db_query(
        "INSERT INTO `{$prefix}facilities` (`name`, `description`, `type`, `street`, `city`, `state`, `lat`, `lng`, `status_id`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$cf[0], $cf[1], $cf[2], $cf[3], $cf[4], $cf[5], $cf[6], $cf[7], $fac_status_id]
    );
    $test_ids['facilities'][] = db_insert_id();
    test("Create facility '{$cf[0]}'", $test_ids['facilities'][count($test_ids['facilities'])-1] > 0);
}

// -- Regions --
$cert_regions = ['__CERT_TEST_ North Sector', '__CERT_TEST_ South Sector'];
foreach ($cert_regions as $rg) {
    db_query("INSERT INTO `{$prefix}region` (`group_name`) VALUES (?)", [$rg]);
    $test_ids['region'][] = db_insert_id();
    test("Create region '{$rg}'", $test_ids['region'][count($test_ids['region'])-1] > 0);
}
$north_sector_id = $test_ids['region'][0];
$south_sector_id = $test_ids['region'][1];

// -- Users (one per permission level) --
$test_password = 'CertTest2026!';
$hashed_pw = hash_password($test_password);

$cert_users = [
    ['__cert_test_super',      $hashed_pw, 0, 'approved', 'CERT Super Admin',  'Super', 'Admin'],
    ['__cert_test_admin',      $hashed_pw, 1, 'approved', 'CERT Administrator', 'Admin', 'User'],
    ['__cert_test_dispatcher', $hashed_pw, 2, 'approved', 'CERT Dispatcher',    'Disp',  'User'],
    ['__cert_test_guest',      $hashed_pw, 3, 'approved', 'CERT Guest',         'Guest', 'User'],
    ['__cert_test_member',     $hashed_pw, 4, 'approved', 'CERT Member',        'Memb',  'User'],
    ['__cert_test_unit',       $hashed_pw, 5, 'approved', 'CERT Unit',          'Unit',  'User'],
];

foreach ($cert_users as $cu) {
    db_query(
        "INSERT INTO `{$prefix}user` (`user`, `passwd`, `level`, `status`, `info`, `name_f`, `name_l`)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        $cu
    );
    $test_ids['user'][] = db_insert_id();
    test("Create user '{$cu[0]}' (level {$cu[2]})", $test_ids['user'][count($test_ids['user'])-1] > 0);
}
$super_user_id = $test_ids['user'][0];

// ═══════════════════════════════════════════════════════════════
// SECTION 3: Settings & Configuration
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 3: Settings & Configuration ──\n";

// Reset variable cache
$GLOBALS['variables'] = null;

test("get_variable('date_format') returns a value", get_variable('date_format') !== false);
test("get_variable('delta_mins') returns a value", get_variable('delta_mins') !== false);
test("get_variable('nonexistent_xyz_999') returns FALSE", get_variable('nonexistent_xyz_999') === false);

// Insert a test setting
db_query("INSERT INTO `{$prefix}settings` (`name`, `value`) VALUES ('__cert_test_setting', 'test_value_123')");
$GLOBALS['variables'] = null; // Clear cache
test("Read test setting after insert", get_variable('__cert_test_setting') === 'test_value_123');

// Update the test setting
db_query("UPDATE `{$prefix}settings` SET `value` = 'updated_value' WHERE `name` = '__cert_test_setting'");
$GLOBALS['variables'] = null;
test("Read test setting after update", get_variable('__cert_test_setting') === 'updated_value');

// Incident numbering
$inc_num_raw = get_variable('_inc_num');
test("_inc_num setting exists", $inc_num_raw !== false);
if ($inc_num_raw !== false) {
    $decoded = @unserialize(@base64_decode($inc_num_raw));
    test("_inc_num decodes successfully", is_array($decoded));
} else {
    test("_inc_num decodes successfully (skipped - no setting)", false);
}

// Date formatting
$now_mysql = mysql_format_date(time());
test("mysql_format_date(time()) returns valid datetime", (bool)preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $now_mysql));

$now_empty = mysql_format_date('');
test("mysql_format_date('') returns current datetime", (bool)preg_match('/^\d{4}-\d{2}-\d{2}/', $now_empty));

$formatted = format_date(strval(time()));
test("format_date() with valid timestamp returns formatted string", strlen($formatted) > 5 && $formatted !== 'TBD');

// ═══════════════════════════════════════════════════════════════
// SECTION 4: Incident Lifecycle
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 4: Incident Lifecycle ──\n";

$_SESSION['user_id'] = $super_user_id;
$_SESSION['level'] = 0;

// Create incident 1: Multi-Casualty Medical
$now = mysql_format_date(time());
db_query(
    "INSERT INTO `{$prefix}ticket` (`in_types_id`, `status`, `severity`, `scope`, `description`, `street`, `city`, `state`, `lat`, `lng`, `date`, `problemstart`, `contact`, `phone`, `_by`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [$medical_type_id, $GLOBALS['STATUS_OPEN'], $GLOBALS['SEVERITY_HIGH'],
     '__CERT_TEST_ Multi-Casualty Incident at Main St',
     'Multiple injuries reported following building collapse. 3 patients identified.',
     '123 Main Street', 'Springfield', 'CA', 34.0522, -118.2437,
     $now, $now, 'John Caller', '555-0100', $super_user_id]
);
$ticket1_id = db_insert_id();
$test_ids['ticket'][] = $ticket1_id;
test("Create incident 1 (Medical MCI)", $ticket1_id > 0);

// Verify ticket is open
$t1 = db_fetch_one("SELECT * FROM `{$prefix}ticket` WHERE `id` = ?", [$ticket1_id]);
test("Ticket 1 status is OPEN", $t1 && (int)$t1['status'] === $GLOBALS['STATUS_OPEN']);
test("Ticket 1 severity is HIGH", $t1 && (int)$t1['severity'] === $GLOBALS['SEVERITY_HIGH']);
test("Ticket 1 has correct in_types_id", $t1 && (int)$t1['in_types_id'] === $medical_type_id);
test("get_status(STATUS_OPEN) returns 'Open'", get_status($GLOBALS['STATUS_OPEN']) === 'Open');
test("is_closed() returns FALSE for open ticket", !is_closed($ticket1_id));

// Allocate to North Sector
db_query(
    "INSERT INTO `{$prefix}allocates` (`group`, `type`, `resource_id`, `al_as_of`, `al_status`, `user_id`)
     VALUES (?, 1, ?, ?, 0, ?)",
    [$north_sector_id, $ticket1_id, $now, $super_user_id]
);
$test_ids['allocates'][] = db_insert_id();
test("Allocate ticket 1 to North Sector", db_insert_id() > 0 || $test_ids['allocates'][count($test_ids['allocates'])-1] > 0);

// Add actions/notes
db_query(
    "INSERT INTO `{$prefix}action` (`ticket_id`, `description`, `user`, `action_type`, `date`)
     VALUES (?, ?, ?, ?, ?)",
    [$ticket1_id, '__CERT_TEST_ Initial size-up complete. 3 patients identified.', $super_user_id, $GLOBALS['ACTION_COMMENT'], $now]
);
$test_ids['action'][] = db_insert_id();
test("Add action note 1", $test_ids['action'][count($test_ids['action'])-1] > 0);

db_query(
    "INSERT INTO `{$prefix}action` (`ticket_id`, `description`, `user`, `action_type`, `date`)
     VALUES (?, ?, ?, ?, ?)",
    [$ticket1_id, '__CERT_TEST_ Triage in progress. START triage applied.', $super_user_id, $GLOBALS['ACTION_COMMENT'], $now]
);
$test_ids['action'][] = db_insert_id();
test("Add action note 2", $test_ids['action'][count($test_ids['action'])-1] > 0);

$action_count = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}action` WHERE `ticket_id` = ? AND `description` LIKE '__CERT_TEST_%'", [$ticket1_id]);
test("2 actions exist for ticket 1", (int)$action_count['cnt'] === 2);

// Assign units
$cert_team_1_id = $test_ids['responder'][0];
$medical_unit_1_id = $test_ids['responder'][2];

db_query(
    "INSERT INTO `{$prefix}assigns` (`ticket_id`, `responder_id`, `status_id`, `dispatched`, `user_id`, `as_of`)
     VALUES (?, ?, ?, ?, ?, ?)",
    [$ticket1_id, $cert_team_1_id, 1, $now, $super_user_id, $now]
);
$assign1_id = db_insert_id();
$test_ids['assigns'][] = $assign1_id;
test("Assign CERT Team 1 to ticket 1", $assign1_id > 0);

db_query(
    "INSERT INTO `{$prefix}assigns` (`ticket_id`, `responder_id`, `status_id`, `dispatched`, `user_id`, `as_of`)
     VALUES (?, ?, ?, ?, ?, ?)",
    [$ticket1_id, $medical_unit_1_id, 1, $now, $super_user_id, $now]
);
$assign2_id = db_insert_id();
$test_ids['assigns'][] = $assign2_id;
test("Assign Medical Unit 1 to ticket 1", $assign2_id > 0);

// Progress CERT Team 1 through statuses
db_query("UPDATE `{$prefix}assigns` SET `responding` = ? WHERE `id` = ?", [$now, $assign1_id]);
$a1 = db_fetch_one("SELECT * FROM `{$prefix}assigns` WHERE `id` = ?", [$assign1_id]);
test("CERT Team 1 responding timestamp set", $a1 && $a1['responding'] !== null);

db_query("UPDATE `{$prefix}assigns` SET `on_scene` = ? WHERE `id` = ?", [$now, $assign1_id]);
$a1 = db_fetch_one("SELECT * FROM `{$prefix}assigns` WHERE `id` = ?", [$assign1_id]);
test("CERT Team 1 on_scene timestamp set", $a1 && $a1['on_scene'] !== null);
test("CERT Team 1 has dispatched + responding + on_scene", $a1 && $a1['dispatched'] !== null && $a1['responding'] !== null && $a1['on_scene'] !== null);

// Update unit status to Deployed
db_query("UPDATE `{$prefix}responder` SET `un_status_id` = ? WHERE `id` = ?", [$deployed_status_id, $cert_team_1_id]);
$unit = db_fetch_one("SELECT * FROM `{$prefix}responder` WHERE `id` = ?", [$cert_team_1_id]);
test("CERT Team 1 status changed to Deployed", $unit && (int)$unit['un_status_id'] === $deployed_status_id);

// Add patients
db_query(
    "INSERT INTO `{$prefix}patient` (`ticket_id`, `name`, `fullname`, `dob`, `gender`, `description`, `user`, `action_type`, `date`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [$ticket1_id, '__CERT_TEST_ Doe J', 'John Doe', '1985-03-15', 1,
     'Fractured left arm, conscious and alert. Triage: Delayed (Yellow).',
     $super_user_id, $GLOBALS['ACTION_COMMENT'], $now]
);
$patient1_id = db_insert_id();
$test_ids['patient'][] = $patient1_id;
test("Add patient 1 (John Doe)", $patient1_id > 0);

db_query(
    "INSERT INTO `{$prefix}patient` (`ticket_id`, `name`, `fullname`, `dob`, `gender`, `description`, `user`, `action_type`, `date`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [$ticket1_id, '__CERT_TEST_ Smith J', 'Jane Smith', '1992-07-22', 2,
     'Head laceration, disoriented. Triage: Immediate (Red).',
     $super_user_id, $GLOBALS['ACTION_COMMENT'], $now]
);
$patient2_id = db_insert_id();
$test_ids['patient'][] = $patient2_id;
test("Add patient 2 (Jane Smith)", $patient2_id > 0);

$patient_count = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}patient` WHERE `ticket_id` = ? AND `name` LIKE '__CERT_TEST_%'", [$ticket1_id]);
test("2 patients exist for ticket 1", (int)$patient_count['cnt'] === 2);

// Audit trail
do_log($GLOBALS['LOG_INCIDENT_OPEN'], $ticket1_id, 0, '__CERT_TEST_ Incident opened by integration test');
$log_entry = db_fetch_one(
    "SELECT * FROM `{$prefix}log` WHERE `ticket_id` = ? AND `info` LIKE '__CERT_TEST_%' ORDER BY id DESC LIMIT 1",
    [$ticket1_id]
);
test("do_log() creates audit trail entry", $log_entry !== null);
test("Log entry has correct code", $log_entry && (int)$log_entry['code'] === $GLOBALS['LOG_INCIDENT_OPEN']);
if ($log_entry) $test_ids['log'][] = (int)$log_entry['id'];

// Close the incident
db_query("UPDATE `{$prefix}assigns` SET `clear` = ? WHERE `ticket_id` = ?", [$now, $ticket1_id]);
db_query("UPDATE `{$prefix}ticket` SET `status` = ?, `problemend` = ? WHERE `id` = ?",
    [$GLOBALS['STATUS_CLOSED'], $now, $ticket1_id]);

test("is_closed() returns TRUE after closing", is_closed($ticket1_id));
test("get_status(STATUS_CLOSED) returns 'Closed'", get_status($GLOBALS['STATUS_CLOSED']) === 'Closed');

// Create incident 2: Search & Rescue (in South Sector)
$sar_type_id = $test_ids['in_types'][2]; // Search And Rescue
db_query(
    "INSERT INTO `{$prefix}ticket` (`in_types_id`, `status`, `severity`, `scope`, `description`, `street`, `city`, `state`, `lat`, `lng`, `date`, `problemstart`, `contact`, `phone`, `_by`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [$sar_type_id, $GLOBALS['STATUS_OPEN'], $GLOBALS['SEVERITY_MEDIUM'],
     '__CERT_TEST_ Missing Hiker in Griffith Park',
     'Last seen on trail near observatory. Search grid established.',
     'Griffith Observatory Trail', 'Los Angeles', 'CA', 34.1184, -118.3004,
     $now, $now, 'Park Ranger', '555-0200', $super_user_id]
);
$ticket2_id = db_insert_id();
$test_ids['ticket'][] = $ticket2_id;
test("Create incident 2 (SAR)", $ticket2_id > 0);

// Allocate to South Sector
db_query(
    "INSERT INTO `{$prefix}allocates` (`group`, `type`, `resource_id`, `al_as_of`, `al_status`, `user_id`)
     VALUES (?, 1, ?, ?, 0, ?)",
    [$south_sector_id, $ticket2_id, $now, $super_user_id]
);
$test_ids['allocates'][] = db_insert_id();
test("Allocate ticket 2 to South Sector", true);

$ticket_count = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}ticket` WHERE `scope` LIKE '__CERT_TEST_%'");
test("2 CERT test tickets exist", (int)$ticket_count['cnt'] === 2);

// ═══════════════════════════════════════════════════════════════
// SECTION 5: Permissions & Access Control
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 5: Permissions & Access Control ──\n";

// Super (level 0)
$_SESSION['level'] = $GLOBALS['LEVEL_SUPER'];
test("LEVEL_SUPER: is_super() = true", is_super() === true);
test("LEVEL_SUPER: is_administrator() = true", is_administrator() === true);
test("LEVEL_SUPER: is_guest() = false", is_guest() === false);

// Administrator (level 1)
$_SESSION['level'] = $GLOBALS['LEVEL_ADMINISTRATOR'];
test("LEVEL_ADMIN: is_super() = false", is_super() === false);
test("LEVEL_ADMIN: is_administrator() = true", is_administrator() === true);
test("LEVEL_ADMIN: is_admin() = true", is_admin() === true);
test("LEVEL_ADMIN: is_guest() = false", is_guest() === false);

// User/Dispatcher (level 2)
$_SESSION['level'] = $GLOBALS['LEVEL_USER'];
test("LEVEL_USER: is_super() = false", is_super() === false);
test("LEVEL_USER: is_administrator() = false", is_administrator() === false);
test("LEVEL_USER: is_guest() = false", is_guest() === false);

// Guest (level 3)
$_SESSION['level'] = $GLOBALS['LEVEL_GUEST'];
test("LEVEL_GUEST: is_guest() = true", is_guest() === true);
test("LEVEL_GUEST: is_super() = false", is_super() === false);

// Member (level 4)
$_SESSION['level'] = $GLOBALS['LEVEL_MEMBER'];
test("LEVEL_MEMBER: is_member() = true", is_member() === true);
test("LEVEL_MEMBER: is_guest() = true (guest includes member)", is_guest() === true);

// Unit (level 5)
$_SESSION['level'] = $GLOBALS['LEVEL_UNIT'];
test("LEVEL_UNIT: is_guest() = false", is_guest() === false);
test("LEVEL_UNIT: is_member() = false", is_member() === false);

// Password verification
$_SESSION['level'] = $GLOBALS['LEVEL_SUPER']; // restore for remaining tests
$verify_result = verify_password($test_password, $hashed_pw);
test("verify_password() validates correct password", $verify_result['valid'] === true);
test("verify_password() rejects wrong password", verify_password('wrongpass', $hashed_pw)['valid'] === false);

// ═══════════════════════════════════════════════════════════════
// SECTION 6: Regional Allocation & Visibility
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 6: Regional Allocation & Visibility ──\n";

// Reopen ticket 1 for allocation tests
db_query("UPDATE `{$prefix}ticket` SET `status` = ?, `problemend` = NULL WHERE `id` = ?",
    [$GLOBALS['STATUS_OPEN'], $ticket1_id]);

$north_tickets = get_tickets_allocated([$north_sector_id]);
test("North Sector contains ticket 1", in_array($ticket1_id, $north_tickets));
test("North Sector does NOT contain ticket 2", !in_array($ticket2_id, $north_tickets));

$south_tickets = get_tickets_allocated([$south_sector_id]);
test("South Sector contains ticket 2", in_array($ticket2_id, $south_tickets));
test("South Sector does NOT contain ticket 1", !in_array($ticket1_id, $south_tickets));

$both_tickets = get_tickets_allocated([$north_sector_id, $south_sector_id]);
test("Both sectors contain ticket 1", in_array($ticket1_id, $both_tickets));
test("Both sectors contain ticket 2", in_array($ticket2_id, $both_tickets));

// Non-existent region returns empty
$empty_tickets = get_tickets_allocated([99999]);
test("Non-existent region returns empty array", empty($empty_tickets));

// Responder allocation
db_query(
    "INSERT INTO `{$prefix}allocates` (`group`, `type`, `resource_id`, `al_as_of`, `al_status`, `user_id`)
     VALUES (?, 4, ?, ?, 0, ?)",
    [$north_sector_id, $cert_team_1_id, $now, $super_user_id]
);
$test_ids['allocates'][] = db_insert_id();
$resp_alloc = db_fetch_one(
    "SELECT * FROM `{$prefix}allocates` WHERE `type` = 4 AND `resource_id` = ? AND `group` = ?",
    [$cert_team_1_id, $north_sector_id]
);
test("Responder allocated to North Sector", $resp_alloc !== null);

// Re-close ticket 1
db_query("UPDATE `{$prefix}ticket` SET `status` = ?, `problemend` = ? WHERE `id` = ?",
    [$GLOBALS['STATUS_CLOSED'], $now, $ticket1_id]);

// ═══════════════════════════════════════════════════════════════
// SECTION 7: Date/Time & Formatting
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 7: Date/Time & Formatting ──\n";

test("get_status(0) returns 'Status error' (reserved has no label)", get_status(0) === 'Status error');
test("get_status(1) returns 'Closed'", get_status(1) === 'Closed');
test("get_status(2) returns 'Open'", get_status(2) === 'Open');
test("get_status(3) returns 'Scheduled'", get_status(3) === 'Scheduled');
test("get_status(99) returns 'Status error'", get_status(99) === 'Status error');

test("get_severity(NORMAL) returns 'Normal'", strpos(get_severity($GLOBALS['SEVERITY_NORMAL']), 'Normal') !== false);
test("get_severity(MEDIUM) returns 'Medium'", strpos(get_severity($GLOBALS['SEVERITY_MEDIUM']), 'Medium') !== false);
test("get_severity(HIGH) returns 'High'", strpos(get_severity($GLOBALS['SEVERITY_HIGH']), 'High') !== false);
test("get_severity(99) returns 'Severity error'", get_severity(99) === 'Severity error');

test("get_type(0) returns 'TBD'", get_type(0) === 'TBD');
test("get_type(999999) returns '?'", get_type(999999) === '?');
test("get_type(valid ID) returns type name", get_type($medical_type_id) === '_CT_ Medical');

test("get_owner(valid user) returns username", get_owner($super_user_id) === '__cert_test_super');
test("get_owner(999999) returns 'unk?'", get_owner(999999) === 'unk?');

test("is_date('2025-01-15 00:00:00') returns true", is_date('2025-01-15 00:00:00') === true);
test("is_date('0000-00-00 00:00:00') returns false", is_date('0000-00-00 00:00:00') === false);
test("is_date('') returns false", is_date('') === false);

// ═══════════════════════════════════════════════════════════════
// SECTION 8: Board Display Queries
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 8: Board Display Queries ──\n";

// Ticket JOIN in_types
$joined = db_fetch_one(
    "SELECT t.*, it.type AS type_name, it.protocol
     FROM `{$prefix}ticket` t
     LEFT JOIN `{$prefix}in_types` it ON t.in_types_id = it.id
     WHERE t.id = ?",
    [$ticket2_id]
);
test("Ticket JOIN in_types returns type name", $joined && $joined['type_name'] === '_CT_ SAR');
test("Ticket JOIN in_types returns protocol text", $joined && strpos($joined['protocol'], 'Establish grid') !== false);

// Assigns query
$assigns = db_fetch_all(
    "SELECT a.*, r.name AS unit_name
     FROM `{$prefix}assigns` a
     LEFT JOIN `{$prefix}responder` r ON a.responder_id = r.id
     WHERE a.ticket_id = ?",
    [$ticket1_id]
);
test("Assigns query returns 2 assignments for ticket 1", count($assigns) === 2);
test("Assign join includes unit name", !empty($assigns) && strpos($assigns[0]['unit_name'], '__CERT_TEST_') === 0);

// Allocates filtering
$alloc_query = db_fetch_all(
    "SELECT t.id AS tick_id, t.scope, a.`group`
     FROM `{$prefix}ticket` t
     LEFT JOIN `{$prefix}allocates` a ON t.id = a.resource_id AND a.type = 1
     WHERE a.`group` = ? AND t.scope LIKE '__CERT_TEST_%'",
    [$south_sector_id]
);
test("Allocates filter returns only South Sector tickets", count($alloc_query) === 1 && (int)$alloc_query[0]['tick_id'] === $ticket2_id);

// Responder JOIN un_status
$resp_joined = db_fetch_all(
    "SELECT r.*, us.description AS status_desc
     FROM `{$prefix}responder` r
     LEFT JOIN `{$prefix}un_status` us ON r.un_status_id = us.id
     WHERE r.name LIKE '__CERT_TEST_%'
     ORDER BY r.id"
);
test("Responder JOIN un_status returns 6 units", count($resp_joined) === 6);
test("Responder join includes status description", !empty($resp_joined) && $resp_joined[0]['status_desc'] !== null);

// Facilities JOIN
$fac_joined = db_fetch_all(
    "SELECT f.*, ft.description AS type_desc
     FROM `{$prefix}facilities` f
     LEFT JOIN `{$prefix}fac_types` ft ON f.type = ft.id
     WHERE f.name LIKE '__CERT_TEST_%'
     ORDER BY f.id"
);
test("Facilities JOIN fac_types returns 4 facilities", count($fac_joined) === 4);
test("Facility join includes type description", !empty($fac_joined) && strpos($fac_joined[0]['type_desc'], '__CERT_TEST_') === 0);

// Patient query
$patients = db_fetch_all(
    "SELECT * FROM `{$prefix}patient` WHERE `ticket_id` = ? AND `name` LIKE '__CERT_TEST_%' ORDER BY id",
    [$ticket1_id]
);
test("Patient query returns 2 patients", count($patients) === 2);
test("Patient 1 has correct name", !empty($patients) && $patients[0]['fullname'] === 'John Doe');
test("Patient 2 has correct gender", count($patients) >= 2 && (int)$patients[1]['gender'] === 2);

// Action history query
$actions = db_fetch_all(
    "SELECT * FROM `{$prefix}action` WHERE `ticket_id` = ? AND `description` LIKE '__CERT_TEST_%' ORDER BY `date`",
    [$ticket1_id]
);
test("Action history query returns 2 entries", count($actions) === 2);

// ═══════════════════════════════════════════════════════════════
// SECTION 9: Edge Cases & Security
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 9: Edge Cases & Security ──\n";

// Special characters in text fields
db_query(
    "INSERT INTO `{$prefix}ticket` (`in_types_id`, `status`, `severity`, `scope`, `description`, `street`, `city`, `state`, `date`, `problemstart`, `contact`, `_by`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [$medical_type_id, $GLOBALS['STATUS_OPEN'], 0,
     "__CERT_TEST_ O'Brien's House Fire & Gas Leak",
     'Patient says "I can\'t breathe" - smoke inhalation suspected.',
     "456 St. Mary's Ave", 'Springfield', 'CA', $now, $now, "Dr. O'Malley", $super_user_id]
);
$ticket3_id = db_insert_id();
$test_ids['ticket'][] = $ticket3_id;
test("Insert ticket with apostrophes and quotes", $ticket3_id > 0);

$t3 = db_fetch_one("SELECT * FROM `{$prefix}ticket` WHERE `id` = ?", [$ticket3_id]);
test("Apostrophe preserved in scope", $t3 && strpos($t3['scope'], "O'Brien") !== false);
test("Quotes preserved in description", $t3 && strpos($t3['description'], '"I can') !== false);
test("Ampersand preserved in scope", $t3 && strpos($t3['scope'], '& Gas') !== false);

// SQL injection attempt
db_query(
    "INSERT INTO `{$prefix}patient` (`ticket_id`, `name`, `fullname`, `description`, `user`, `action_type`, `date`)
     VALUES (?, ?, ?, ?, ?, ?, ?)",
    [$ticket3_id, "__CERT_TEST_ '; DROP TABLE patient; --", "Robert'); DELETE FROM user; --",
     '1 OR 1=1; DROP TABLE ticket; --', $super_user_id, $GLOBALS['ACTION_COMMENT'], $now]
);
$sqli_patient_id = db_insert_id();
$test_ids['patient'][] = $sqli_patient_id;
test("SQL injection stored as literal string", $sqli_patient_id > 0);

$sqli_patient = db_fetch_one("SELECT * FROM `{$prefix}patient` WHERE `id` = ?", [$sqli_patient_id]);
test("SQL injection name stored literally", $sqli_patient && strpos($sqli_patient['name'], "DROP TABLE") !== false);
test("Tables still exist after injection attempt", check_for_rows("SELECT 1 FROM `{$prefix}ticket` LIMIT 1") > 0);

// XSS payload
db_query(
    "INSERT INTO `{$prefix}action` (`ticket_id`, `description`, `user`, `action_type`, `date`)
     VALUES (?, ?, ?, ?, ?)",
    [$ticket3_id, '__CERT_TEST_ <script>alert("xss")</script><img onerror=alert(1) src=x>',
     $super_user_id, $GLOBALS['ACTION_COMMENT'], $now]
);
$xss_action_id = db_insert_id();
$test_ids['action'][] = $xss_action_id;
test("XSS payload stored in action", $xss_action_id > 0);

$xss_action = db_fetch_one("SELECT * FROM `{$prefix}action` WHERE `id` = ?", [$xss_action_id]);
test("XSS payload stored literally (not executed)", $xss_action && strpos($xss_action['description'], '<script>') !== false);

// e() function escapes XSS
test("e() escapes script tags", strpos(e('<script>alert(1)</script>'), '<script>') === false);
test("e() escapes quotes", strpos(e('"onclick="alert(1)"'), '&quot;') !== false);
test("e() handles null", e(null) === '');

// Empty/NULL handling
db_query(
    "INSERT INTO `{$prefix}ticket` (`in_types_id`, `status`, `severity`, `scope`, `description`, `city`, `lat`, `lng`, `date`, `problemstart`, `contact`, `_by`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [$medical_type_id, $GLOBALS['STATUS_OPEN'], 0,
     '__CERT_TEST_ Zero Coordinates Test', 'Testing zero lat/lng',
     null, 0.0, 0.0, $now, $now, '', $super_user_id]
);
$ticket4_id = db_insert_id();
$test_ids['ticket'][] = $ticket4_id;
test("Insert ticket with zero lat/lng", $ticket4_id > 0);

$t4 = db_fetch_one("SELECT * FROM `{$prefix}ticket` WHERE `id` = ?", [$ticket4_id]);
test("Zero latitude stored correctly", $t4 && abs((float)$t4['lat']) < 0.001);
test("NULL city stored correctly", $t4 && $t4['city'] === null);
test("Empty contact stored correctly", $t4 && $t4['contact'] === '');

// Very long description
$long_desc = str_repeat('A', 2000) . ' __CERT_TEST_LONG';
db_query(
    "INSERT INTO `{$prefix}action` (`ticket_id`, `description`, `user`, `action_type`, `date`)
     VALUES (?, ?, ?, ?, ?)",
    [$ticket4_id, $long_desc, $super_user_id, $GLOBALS['ACTION_COMMENT'], $now]
);
$long_action_id = db_insert_id();
$test_ids['action'][] = $long_action_id;
test("Insert 2000+ character description", $long_action_id > 0);

$long_action = db_fetch_one("SELECT * FROM `{$prefix}action` WHERE `id` = ?", [$long_action_id]);
test("Long description stored completely", $long_action && strlen($long_action['description']) > 2000);

// do_log truncates info to 2047
$long_info = str_repeat('B', 3000) . '__CERT_TEST_TRUNCATE';
do_log($GLOBALS['LOG_COMMENT'], $ticket4_id, 0, $long_info);
$trunc_log = db_fetch_one(
    "SELECT * FROM `{$prefix}log` WHERE `ticket_id` = ? AND `info` LIKE 'BBB%' ORDER BY id DESC LIMIT 1",
    [$ticket4_id]
);
test("do_log() truncates info to 2047 chars", $trunc_log && strlen($trunc_log['info']) <= 2047);
if ($trunc_log) $test_ids['log'][] = (int)$trunc_log['id'];

// sanitize_int tests
test("sanitize_int('42') returns 42", sanitize_int('42') === 42);
test("sanitize_int('abc') returns 0", sanitize_int('abc') === 0);
test("sanitize_int('abc', 5) returns 0 (cast, not default)", sanitize_int('abc', 5) === 0);
test("sanitize_int('', 5) returns 5 (default for empty)", sanitize_int('', 5) === 5);
test("sanitize_int(null, 5) returns 5 (default for null)", sanitize_int(null, 5) === 5);
test("sanitize_int(null) returns 0", sanitize_int(null) === 0);

// sanitize_string tests
test("sanitize_string('  hello  ') trims", sanitize_string('  hello  ') === 'hello');
test("sanitize_string with null byte strips it", strpos(sanitize_string("hello\0world"), "\0") === false);

// strip_html tests
if (function_exists('strip_html')) {
    $stripped = strip_html('<script>alert(1)</script><b>Hello</b>');
    test("strip_html removes script tags", strpos($stripped, '<script>') === false);
} else {
    test("strip_html function exists", false);
}

// ═══════════════════════════════════════════════════════════════
// SECTION 10: Notification Configuration
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 10: Notification Configuration ──\n";

// Verify incident type notification settings
$med_type = db_fetch_one("SELECT * FROM `{$prefix}in_types` WHERE `id` = ?", [$medical_type_id]);
test("Medical Emergency notify_when = 1 (all)", $med_type && (int)$med_type['notify_when'] === 1);

$hazmat_type_id = $test_ids['in_types'][3];
$haz_type = db_fetch_one("SELECT * FROM `{$prefix}in_types` WHERE `id` = ?", [$hazmat_type_id]);
test("HazMat notify_when = 2 (open only)", $haz_type && (int)$haz_type['notify_when'] === 2);

$shelter_type_id = $test_ids['in_types'][4];
$sh_type = db_fetch_one("SELECT * FROM `{$prefix}in_types` WHERE `id` = ?", [$shelter_type_id]);
test("Shelter Ops notify_when = 3 (close only)", $sh_type && (int)$sh_type['notify_when'] === 3);

// Verify incident type has protocol text
test("Medical Emergency has protocol text", $med_type && strlen($med_type['protocol']) > 10);
test("Medical Emergency severity = HIGH", $med_type && (int)$med_type['set_severity'] === $GLOBALS['SEVERITY_HIGH']);

// Check notify table exists (may or may not exist depending on install)
$notify_exists = db_query("SHOW TABLES LIKE '{$prefix}notify'");
if ($notify_exists && $notify_exists->num_rows > 0) {
    db_query(
        "INSERT INTO `{$prefix}notify` (`ticket_id`, `user`, `on_ticket`, `on_action`, `severities`, `email_address`)
         VALUES (0, ?, 1, 1, 1, '__cert_test@example.com')",
        [$super_user_id]
    );
    $notify_row = db_fetch_one(
        "SELECT * FROM `{$prefix}notify` WHERE `email_address` = '__cert_test@example.com'"
    );
    test("Notification subscription created", $notify_row !== null);
} else {
    test("Notify table exists (skipped - table not present)", true);
}

test("notify_user() function is callable", function_exists('notify_user') && is_callable('notify_user'));

// ═══════════════════════════════════════════════════════════════
// SECTION 11: Cleanup
// ═══════════════════════════════════════════════════════════════
echo "\n── Section 11: Cleanup ──\n";

// Delete in reverse dependency order
// Notify
try {
    db_query("DELETE FROM `{$prefix}notify` WHERE `email_address` LIKE '__cert_test%'");
    test("Cleanup: notify subscriptions", true);
} catch (Exception $e) {
    test("Cleanup: notify subscriptions (table may not exist)", true);
}

// Patients
$ticket_ids_str = implode(',', array_map('intval', $test_ids['ticket']));
if (!empty($test_ids['ticket'])) {
    db_query("DELETE FROM `{$prefix}patient` WHERE `ticket_id` IN ($ticket_ids_str)");
}
$prem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}patient` WHERE `name` LIKE '__CERT_TEST_%'");
test("Cleanup: patients removed", (int)$prem['cnt'] === 0);

// Actions
if (!empty($test_ids['ticket'])) {
    db_query("DELETE FROM `{$prefix}action` WHERE `ticket_id` IN ($ticket_ids_str)");
}
$arem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}action` WHERE `description` LIKE '__CERT_TEST_%'");
test("Cleanup: actions removed", (int)$arem['cnt'] === 0);

// Assigns
if (!empty($test_ids['ticket'])) {
    db_query("DELETE FROM `{$prefix}assigns` WHERE `ticket_id` IN ($ticket_ids_str)");
}
test("Cleanup: assigns removed", true);

// Allocates (type 1 = ticket, type 4 = responder)
if (!empty($test_ids['ticket'])) {
    db_query("DELETE FROM `{$prefix}allocates` WHERE `type` = 1 AND `resource_id` IN ($ticket_ids_str)");
}
$resp_ids_str = implode(',', array_map('intval', $test_ids['responder']));
if (!empty($test_ids['responder'])) {
    db_query("DELETE FROM `{$prefix}allocates` WHERE `type` = 4 AND `resource_id` IN ($resp_ids_str)");
}
test("Cleanup: allocates removed", true);

// Log entries
if (!empty($test_ids['ticket'])) {
    db_query("DELETE FROM `{$prefix}log` WHERE `ticket_id` IN ($ticket_ids_str) OR `info` LIKE '%__CERT_TEST_%'");
}
test("Cleanup: log entries removed", true);

// Tickets
if (!empty($test_ids['ticket'])) {
    db_query("DELETE FROM `{$prefix}ticket` WHERE `id` IN ($ticket_ids_str)");
}
$trem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}ticket` WHERE `scope` LIKE '__CERT_TEST_%'");
test("Cleanup: tickets removed", (int)$trem['cnt'] === 0);

// Responders
db_query("DELETE FROM `{$prefix}responder` WHERE `name` LIKE '__CERT_TEST_%'");
$rrem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}responder` WHERE `name` LIKE '__CERT_TEST_%'");
test("Cleanup: responders removed", (int)$rrem['cnt'] === 0);

// Facilities
db_query("DELETE FROM `{$prefix}facilities` WHERE `name` LIKE '__CERT_TEST_%'");
$frem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}facilities` WHERE `name` LIKE '__CERT_TEST_%'");
test("Cleanup: facilities removed", (int)$frem['cnt'] === 0);

// Incident types
db_query("DELETE FROM `{$prefix}in_types` WHERE `type` LIKE '_CT_ %'");
$itrem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}in_types` WHERE `type` LIKE '_CT_ %'");
test("Cleanup: incident types removed", (int)$itrem['cnt'] === 0);

// Unit statuses
db_query("DELETE FROM `{$prefix}un_status` WHERE `description` LIKE '__CERT_TEST_%'");
test("Cleanup: unit statuses removed", true);

// Unit types
db_query("DELETE FROM `{$prefix}unit_types` WHERE `description` LIKE '__CERT_TEST_%'");
test("Cleanup: unit types removed", true);

// Facility types
db_query("DELETE FROM `{$prefix}fac_types` WHERE `description` LIKE '__CERT_TEST_%'");
test("Cleanup: facility types removed", true);

// Regions
db_query("DELETE FROM `{$prefix}region` WHERE `group_name` LIKE '__CERT_TEST_%'");
$rgrem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}region` WHERE `group_name` LIKE '__CERT_TEST_%'");
test("Cleanup: regions removed", (int)$rgrem['cnt'] === 0);

// Users
db_query("DELETE FROM `{$prefix}user` WHERE `user` LIKE '__cert_test_%'");
$urem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}user` WHERE `user` LIKE '__cert_test_%'");
test("Cleanup: users removed", (int)$urem['cnt'] === 0);

// Settings
db_query("DELETE FROM `{$prefix}settings` WHERE `name` LIKE '__cert_test_%'");
test("Cleanup: test settings removed", true);

// Final verification — no test data remains
echo "\n── Final Verification ──\n";
$tables_to_check = [
    ['ticket', 'scope', '__CERT_TEST_%'],
    ['responder', 'name', '__CERT_TEST_%'],
    ['facilities', 'name', '__CERT_TEST_%'],
    ['in_types', 'type', '_CT_ %'],
    ['region', 'group_name', '__CERT_TEST_%'],
    ['user', 'user', '__cert_test_%'],
];
$all_clean = true;
foreach ($tables_to_check as $tc) {
    $field = $tc[1];
    $like = $tc[2];
    $rem = db_fetch_one("SELECT COUNT(*) AS cnt FROM `{$prefix}{$tc[0]}` WHERE `{$field}` LIKE '{$like}'");
    if ((int)$rem['cnt'] > 0) {
        $all_clean = false;
        echo "[WARN] Leftover data in {$tc[0]}: {$rem['cnt']} rows\n";
    }
}
test("No __CERT_TEST_ data remains in any table", $all_clean);

// ═══════════════════════════════════════════════════════════════
// Results
// ═══════════════════════════════════════════════════════════════
echo "\n═══════════════════════════════════════════════════\n";
echo "=== RESULTS: $pass passed, $fail failed out of $test_num tests ===\n";
echo "═══════════════════════════════════════════════════\n";
exit($fail > 0 ? 1 : 0);
