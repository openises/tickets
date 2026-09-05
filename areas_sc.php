<?php
//    areas server-side create script

@session_start();
session_write_close();
require_once(isset($_SESSION['fip']) ? $_SESSION['fip'] : './incs/functions.inc.php');        //7/28/10

// GHSA-5v45-76v3-9gm5: this endpoint is Regions-management admin config
// (config.php + areas_sc.php + reset_regions.php per docs/FEATURE-GAP-ANALYSIS.md),
// not a general-public action -- require the same admin gate other config
// panels use before touching the database at all.
if (!is_administrator()) {
    print "-NOT AUTHORIZED";
    exit;
    }

$istest=false;
// Replaced extract — explicit variable assignment (Phase 2 cleanup)
$theTable = $_POST['theTable'] ?? '';
if(empty($theTable)) {
    print "-TABLE NAME ERROR";
    exit;
    }

// GHSA-5v45-76v3-9gm5: `theTable` reached an INSERT INTO `{$theTable}` with no
// validation at all -- any request could target an arbitrary table. An
// identifier position cannot be secured by escaping the value; it needs an
// allowlist. These are the only two tables this endpoint's own purpose
// (Regions management, see incs/install_schema.inc.php's 'region' /
// 'region_type' definitions) ever legitimately writes to, each mapped to its
// real column set so a crafted `frm_*` POST KEY (not just the value) can't
// smuggle a second identifier-position injection through the column list
// either -- the original code built column names from user-supplied POST
// keys with the same complete absence of validation.
$allowedTables = [
    'region'      => ['group_name', 'category', 'description', 'owner',
                       'def_area_code', 'def_city', 'def_lat', 'def_lng',
                       'def_st', 'def_zoom', 'boundary'],
    'region_type' => ['name', 'description', '_on', '_from', '_by'],
    ];
if (!array_key_exists($theTable, $allowedTables)) {
    print "-TABLE NAME ERROR";
    exit;
    }
$allowedColumns = $allowedTables[$theTable];

$columns = [];
$placeholders = [];
$values = [];
foreach ($_POST as $VarName=>$VarValue) {
    if(substr($VarName, 0, 4)== "frm_" ) {            // substr(  string, start ,length )
        $col = substr($VarName, 4);
        if (!in_array($col, $allowedColumns, true)) {
            continue;        // not a real column on this table — drop it, don't inject it
            }
        $columns[] = "`" . $col . "`";
        $placeholders[] = "?";
        $values[] = trim($VarValue);
        }
    }        // end foreach () ...
if (empty($columns)) {
    print "-NO VALID COLUMNS";
    exit;
    }
                                                                    // build query
$query  = "INSERT INTO `{$mysql_prefix}{$theTable}` (" . implode(",", $columns) . ") VALUES (" . implode(",", $placeholders) . ")";

print ("-" . $query);
$result = db_query($query, $values);

$insert_id = db()->insert_id;

//$query = "UPDATE `{$mysql_prefix}sit_ago`  SET  `e` = NOW() WHERE `id` = 1 LIMIT 1";        //the map date column
//$result = mysql_query($query) or myerror(get_file(__file__), __line__, 'mysql_error', $query);
unset ($result);

?>
