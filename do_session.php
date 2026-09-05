<?php
/*
1/23/10 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
2026-09-05 GHSA-class finding (2026-09-04/05 follow-up sweep): this was a
completely generic, unauthenticated session-value writer -- $_SESSION[$_GET
['f_n']] = $_GET['v_n'] with zero restriction on either the key or the
value. Any request could set ANY session key to ANY string, which meant
(a) a real privilege-escalation path (setting $_SESSION['level'] or
$_SESSION['user_id'] directly), and (b) the vector behind a real,
independently-confirmed SQL injection chain across dozens of files that
concatenate $_SESSION['viewed_groups']/user_groups into query text (see
board.php's own fixed instances). The `sess_id` parameter every real caller
already sends (see get_sess_key() -- it's just the caller's own PHP session
id) was never actually checked here either.

Fixed with an explicit allowlist of the app's own real f_n keys/prefixes
(enumerated by grepping every "f_n=" literal across the whole tree), so an
attacker can no longer choose an arbitrary key -- and value handling still
needs a downstream fix per-consumer (already done for board.php; a broader
pass covers the rest), since the VALUE for a legitimate key like
viewed_groups is still attacker-supplied.
*/
//
// generic session value writer - note names, method
//
error_reporting(E_ALL);

@session_start();
require_once(isset($_SESSION['fip']) ? $_SESSION['fip'] : './incs/functions.inc.php');        //7/28/10

$fn = isset($_GET['f_n']) ? (string) $_GET['f_n'] : '';

// Exact literal keys the app's own JS ever sends (grepped from every
// f_n=... call site across *.php/*.js).
$doSessionExactAllowlist = [
    'viewed_groups', 'fullscr_sit', 'mobile_selected', 'show_hide_upper',
    'show_hide_unit', 'show_hide_cleared', 'list_type', 'fac_direct',
    'fac_sort', 'respresp_direct', 'respresp_sort', 'sitresp_direct',
    'sitresp_sort', 'layer_inuse', 'hide_controls', 'resp_list',
    'facs_list', 'incs_list',
];
// Dynamic prefixes (category name appended by JS, e.g. show_hide_<cat>) --
// the suffix after the prefix must itself be alphanumeric/underscore only.
$doSessionPrefixAllowlist = ['show_hide_', 'show_hide_bnds_', 'show_hide_fac_'];

$doSessionAllowed = in_array($fn, $doSessionExactAllowlist, true);
if (!$doSessionAllowed) {
    foreach ($doSessionPrefixAllowlist as $prefix) {
        if (strpos($fn, $prefix) === 0
            && preg_match('/^[a-zA-Z0-9_]+$/', substr($fn, strlen($prefix)))) {
            $doSessionAllowed = true;
            break;
            }
        }
    }

if ($doSessionAllowed) {
    $_SESSION[$fn] = isset($_GET['v_n']) ? (string) $_GET['v_n'] : '';
    }
session_write_close();
print "";
?>
