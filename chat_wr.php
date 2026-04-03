<?php

@session_start();
session_write_close();
require_once(isset($_SESSION['fip']) ? $_SESSION['fip'] : './incs/functions.inc.php');        //7/28/10
/*
    chat_messages : // id message  when chat_room_id user_id from
*/
    $frm_message = sanitize_string($_GET['frm_message']);
    $frm_room = sanitize_int($_GET['frm_room']);
    $frm_user = sanitize_int($_GET['frm_user']);
    $frm_from = sanitize_string($_GET['frm_from']);
    $now = mysql_format_date(time() - (get_variable('delta_mins')*60));
    $query  = "INSERT INTO `{$GLOBALS['mysql_prefix']}chat_messages` (`when`, `message`, `chat_room_id`, `user_id`, `from`)
                    VALUES (?, ?, ?, ?, ?)";

    $result    = db_query($query, [$now, $frm_message, $frm_room, $frm_user, $frm_from]);
    print db()->insert_id;
?>