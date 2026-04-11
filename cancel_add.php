<?php
/*
6/9/11 initial release
*/
error_reporting(E_ALL);
require_once './incs/functions.inc.php';
//snap (basename(__FILE__), __LINE__);
$ticket_id = sanitize_int($_POST['ticket_id']);
if (strval(intval($ticket_id)) == $_POST['ticket_id']) {                // sanity check

    $query = "DELETE FROM `{$GLOBALS['mysql_prefix']}action` WHERE `ticket_id` = ?;";        // possibly none
    $result = db_query($query, [$ticket_id]);

    $query = "DELETE FROM `{$GLOBALS['mysql_prefix']}patient` WHERE `ticket_id` = ?;";
    $result = db_query($query, [$ticket_id]);

    $query = "DELETE FROM `{$GLOBALS['mysql_prefix']}ticket` WHERE `id` = ? AND `status` = ? LIMIT 1;";
    $result = db_query($query, [$ticket_id, $GLOBALS['STATUS_RESERVED']]);
    }
else {
    snap (basename(__FILE__), implode (";", $_POST));
    }

//snap (__LINE__, $query);
?>

