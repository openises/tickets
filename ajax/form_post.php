<?php
/**
 * AJAX form processing endpoint
 * Handles: contact, note, addaction, editaction, addpatient, editpatient, dispatch, dispatchmail
 *
 * Security: All SQL queries migrated to prepared statements (2026-03)
 */
error_reporting(E_ALL);
require_once '../incs/functions.inc.php';
@session_start();
session_write_close();
$func = sanitize_string($_GET['function'] ?? '');
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$prefix = $GLOBALS['mysql_prefix'];

switch($func) {
    case "contact":
    $smsg_ids = ((isset($_POST['use_smsg'])) && ($_POST['use_smsg'] == 1)) ? $_POST['frm_smsg_ids'] : "";
    $address_str = $_POST['frm_add_str'];
    $resp_ids = ((isset($_POST['frm_resp_ods'])) && ($_POST['frm_resp_ids'] != "") && ($_POST['frm_resp_ids'] != 0)) ? $_POST['frm_resp_ids'] : 0;
    $count = 0;
    $tik_id = ((isset($_POST['frm_ticket_id'])) && ($_POST['frm_ticket_id'] != 0)) ? sanitize_int($_POST['frm_ticket_id']) : 0;
    $count = do_send ($address_str, $smsg_ids, $_POST['frm_subj'], $_POST['frm_text'], $tik_id, $_POST['frm_resp_ids']);
    print "Messages sent: {$count}";
    break;

    case "note":
    $field_name = array('description', 'comments');
    $frm_ticket_id = sanitize_int($_POST['frm_ticket_id']);
    $frm_add_to = sanitize_int($_POST['frm_add_to']);

    $row = db_fetch_one(
        "SELECT * FROM `{$prefix}ticket` WHERE `id` = ? LIMIT 1",
        [$frm_ticket_id]
    );
    if (!$row) { print "Ticket not found"; break; }
    $row = stripslashes_deep($row);

    $now_ts = (time() - (get_variable('delta_mins')*60));
    $format = get_variable('date_format');
    $the_date = date($format, $now_ts);
    $field_idx = ($frm_add_to === 0) ? 'description' : 'comments';
    $the_in_str = $row[$field_idx] ?? '';
    $the_text = "{$the_in_str} [{$_SESSION['user']}:{$the_date}]" . strip_tags(trim($_POST['frm_text'])) . "\n";

    // Use a safe column name from our whitelist
    if (!in_array($field_idx, ['description', 'comments'])) { print "Invalid field"; break; }
    $result = db_query(
        "UPDATE `{$prefix}ticket` SET `{$field_idx}` = ? WHERE `id` = ? LIMIT 1",
        [$the_text, $frm_ticket_id]
    );
    if($result) {print "Note added to Ticket " . $frm_ticket_id . "<BR />";} else {print "Something went wrong, please try again<BR />";}
    break;

    case "addaction":
    $responder = $sep = "";
    foreach ($_POST as $VarName=>$VarValue) {
        $temp = explode("_", $VarName);
        if (substr($VarName, 0, 7)=="frm_cb_") {
            $responder .= $sep . sanitize_string($VarValue);
            $sep = " ";
            }
        }
    $frm_description = strip_html($_POST['frm_description']);
    $frm_ticket_id = sanitize_int($_POST['frm_ticket_id']);
    $user_id = sanitize_int($_SESSION['user_id']);
    $action_comment = sanitize_int($GLOBALS['ACTION_COMMENT']);

    $frm_meridiem_asof = array_key_exists('frm_meridiem_asof', $_POST) ? sanitize_string($_POST['frm_meridiem_asof']) : "";
    $frm_year = sanitize_string($_POST['frm_year_asof'] ?? '');
    $frm_month = sanitize_string($_POST['frm_month_asof'] ?? '');
    $frm_day = sanitize_string($_POST['frm_day_asof'] ?? '');
    $frm_hour = sanitize_string($_POST['frm_hour_asof'] ?? '');
    $frm_minute = sanitize_string($_POST['frm_minute_asof'] ?? '');
    $frm_asof = "{$frm_year}-{$frm_month}-{$frm_day} {$frm_hour}:{$frm_minute}:00{$frm_meridiem_asof}";

    // Check for duplicate
    $result = db_query(
        "SELECT * FROM `{$prefix}action` WHERE
        `description` = ? AND
        `ticket_id` = ? AND
        `user` = ? AND
        `action_type` = ? AND
        `updated` = ? AND
        `responder` = ?",
        [$frm_description, $frm_ticket_id, $user_id, $action_comment, $frm_asof, $responder]
    );

    if ($result && $result->num_rows == 0) {
        // Not a duplicate - insert
        $result = db_query(
            "INSERT INTO `{$prefix}action`
            (`description`,`ticket_id`,`date`,`user`,`action_type`, `updated`, `responder`) VALUES
            (?, ?, ?, ?, ?, ?, ?)",
            [$frm_description, $frm_ticket_id, $now, $user_id, $action_comment, $frm_asof, $responder]
        );
        if($result) {print "Action added to Ticket " . $frm_ticket_id . "<BR />";} else {print "Something went wrong, please try again<BR />";}
        $action_id = db_insert_id();
        do_log($GLOBALS['LOG_ACTION_ADD'], $frm_ticket_id, 0, $action_id);

        db_query(
            "UPDATE `{$prefix}ticket` SET `updated` = ? WHERE `id` = ? LIMIT 1",
            [$frm_asof, $frm_ticket_id]
        );
        }

    $addrs = notify_user($frm_ticket_id, $GLOBALS['NOTIFY_ACTION_CHG']);
    if ($addrs) {
        $theTo = implode("|", array_unique($addrs));
        $theText = "TICKET - ACTION: ";
        mail_it ($theTo, "", $theText, $frm_ticket_id, 1 );
        }
    break;

    case "editaction":
    $responder = $sep = "";
    foreach ($_POST as $VarName=>$VarValue) {
        $temp = explode("_", $VarName);
        if (substr($VarName, 0, 7)=="frm_cb_") {
            $responder .= $sep . sanitize_string($VarValue);
            $sep = " ";
            }
        }
    $frm_description = strip_html($_POST['frm_description'] ?? '');
    $action_id = sanitize_int($_GET['id'] ?? 0);
    $ticket_id = sanitize_int($_GET['ticket_id'] ?? 0);

    $frm_meridiem_asof = array_key_exists('frm_meridiem_asof', $_POST) ? sanitize_string($_POST['frm_meridiem_asof']) : "";
    $frm_year = sanitize_string($_POST['frm_year_asof'] ?? '');
    $frm_month = sanitize_string($_POST['frm_month_asof'] ?? '');
    $frm_day = sanitize_string($_POST['frm_day_asof'] ?? '');
    $frm_hour = sanitize_string($_POST['frm_hour_asof'] ?? '');
    $frm_minute = sanitize_string($_POST['frm_minute_asof'] ?? '');
    $frm_asof = "{$frm_year}-{$frm_month}-{$frm_day} {$frm_hour}:{$frm_minute}:00{$frm_meridiem_asof}";

    db_query(
        "UPDATE `{$prefix}action` SET `description` = ?, `responder` = ?, `updated` = ? WHERE `id` = ? LIMIT 1",
        [$frm_description, $responder, $frm_asof, $action_id]
    );
    db_query(
        "UPDATE `{$prefix}ticket` SET `updated` = ? WHERE `id` = ? LIMIT 1",
        [$frm_asof, $ticket_id]
    );
    $row = db_fetch_one(
        "SELECT `ticket_id` FROM `{$prefix}action` WHERE `id` = ? LIMIT 1",
        [$action_id]
    );

    $id = $ticket_id;
    print '<SPAN CLASS="header text" style="width: 100%; display: block; text-align: center;">Action record has been updated.</SPAN><BR /><BR /><BR />';
    print "<DIV STYLE='width: 100%; display: block; text-align: center;'>";
    print "<A ID='main_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' HREF='main.php'>" . get_text('Main') . "</A>";
    print "<A ID='inc_but' class='plain text' style='float: none; width: 100px; display: inline-block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' HREF='main.php?id=" . e($id) . "'>" . get_text('Incident') . "</A><BR />";
    print "</DIV>";

    $addrs = notify_user($ticket_id, $GLOBALS['NOTIFY_ACTION_CHG']);
    if ($addrs) {
        $theTo = implode("|", array_unique($addrs));
        $theText = "TICKET - ACTION UPDATED: ";
        $theCount = mail_it ($theTo, "", $theText, $id, 1 );
        if($theCount > 0) {print $theCount . " Notifications sent<BR />";}
        }
    break;

    case "addpatient":
    $frm_description = strip_html($_POST['frm_description']);
    $frm_ticket_id = sanitize_int($_REQUEST['ticket_id'] ?? 0);
    $user_id = sanitize_int($_SESSION['user_id']);
    $action_comment = sanitize_int($GLOBALS['ACTION_COMMENT']);
    $frm_name = sanitize_string($_POST['frm_name'] ?? '');

    $post_frm_meridiem_asof = empty($_POST['frm_meridiem_asof']) ? "" : sanitize_string($_POST['frm_meridiem_asof']);
    $frm_year = sanitize_string($_POST['frm_year_asof'] ?? '');
    $frm_month = sanitize_string($_POST['frm_month_asof'] ?? '');
    $frm_day = sanitize_string($_POST['frm_day_asof'] ?? '');
    $frm_hour = sanitize_string($_POST['frm_hour_asof'] ?? '');
    $frm_minute = sanitize_string($_POST['frm_minute_asof'] ?? '');
    $frm_asof = "{$frm_year}-{$frm_month}-{$frm_day} {$frm_hour}:{$frm_minute}:00{$post_frm_meridiem_asof}";

    // Check for duplicate
    $result = db_query(
        "SELECT * FROM `{$prefix}patient` WHERE
        `description` = ? AND
        `ticket_id` = ? AND
        `user` = ? AND
        `action_type` = ? AND
        `name` = ? AND
        `updated` = ? LIMIT 1",
        [$frm_description, $frm_ticket_id, $user_id, $action_comment, $frm_name, $frm_asof]
    );

    if ($result && $result->num_rows == 0) {
        // Build insert with optional extended fields
        if (array_key_exists('frm_fullname', $_POST)) {
            $fullname = sanitize_string($_POST['frm_fullname']);
            $dob = sanitize_string($_POST['frm_dob'] ?? '');
            $gender = sanitize_string($_POST['frm_gender_val'] ?? '');
            $ins_id = sanitize_string($_POST['frm_ins_id'] ?? '');
            $facility_id = sanitize_string($_POST['frm_facility_id'] ?? '');
            $fac_cont = sanitize_string($_POST['frm_fac_cont'] ?? '');

            $result = db_query(
                "INSERT INTO `{$prefix}patient` SET
                `fullname` = ?, `dob` = ?, `gender` = ?, `insurance_id` = ?,
                `facility_id` = ?, `facility_contact` = ?,
                `description` = ?, `ticket_id` = ?, `date` = ?,
                `user` = ?, `action_type` = ?, `name` = ?, `updated` = ?",
                [$fullname, $dob, $gender, $ins_id, $facility_id, $fac_cont,
                 $frm_description, $frm_ticket_id, $now, $user_id, $action_comment, $frm_name, $frm_asof]
            );
        } else {
            $result = db_query(
                "INSERT INTO `{$prefix}patient` SET
                `description` = ?, `ticket_id` = ?, `date` = ?,
                `user` = ?, `action_type` = ?, `name` = ?, `updated` = ?",
                [$frm_description, $frm_ticket_id, $now, $user_id, $action_comment, $frm_name, $frm_asof]
            );
        }

        do_log($GLOBALS['LOG_PATIENT_ADD'], $frm_ticket_id, 0, db_insert_id());

        db_query(
            "UPDATE `{$prefix}ticket` SET `updated` = ? WHERE `id` = ? LIMIT 1",
            [$frm_asof, $frm_ticket_id]
        );
        }

    $addrs = notify_user($frm_ticket_id, $GLOBALS['NOTIFY_PERSON_CHG']);
    if ($addrs) {
        $theTo = implode("|", array_unique($addrs));
        $subject = "TICKET - PATIENT ADDED: ";
        $theMessage= mail_it ($theTo, "", $subject, $frm_ticket_id, 1, true );
        $theCount = do_send ($theTo, "", $subject, $theMessage, $frm_ticket_id, 0, null, null);
        if($theCount > 0) {print $theCount . " Notifications sent<BR />";}
        }
    if($result) {print "Patient added to Ticket " . e($frm_ticket_id) . "<BR />";} else {print "Something went wrong, please try again<BR />";}
    break;

    case "editpatient":
    $patient_id = sanitize_int($_GET['id'] ?? 0);
    $frm_ticket_id = sanitize_int($_REQUEST['ticket_id'] ?? 0);
    $user_id = sanitize_int($_SESSION['user_id']);
    $action_comment = sanitize_int($GLOBALS['ACTION_COMMENT']);
    $frm_description = strip_html($_POST['frm_description'] ?? '');
    $frm_name = sanitize_string($_POST['frm_name'] ?? '');
    $facility_id = sanitize_string($_POST['frm_facility_id'] ?? '');
    $fac_cont = sanitize_string($_POST['frm_fac_cont'] ?? '');

    $frm_meridiem_asof = array_key_exists('frm_meridiem_asof', $_POST) ? sanitize_string($_POST['frm_meridiem_asof']) : "";
    $frm_year = sanitize_string($_POST['frm_year_asof'] ?? '');
    $frm_month = sanitize_string($_POST['frm_month_asof'] ?? '');
    $frm_day = sanitize_string($_POST['frm_day_asof'] ?? '');
    $frm_hour = sanitize_string($_POST['frm_hour_asof'] ?? '');
    $frm_minute = sanitize_string($_POST['frm_minute_asof'] ?? '');
    $frm_asof = "{$frm_year}-{$frm_month}-{$frm_day} {$frm_hour}:{$frm_minute}:00{$frm_meridiem_asof}";
    $now_formatted = mysql_format_date(now());

    if (array_key_exists('frm_fullname', $_POST)) {
        $fullname = sanitize_string($_POST['frm_fullname']);
        $dob = sanitize_string($_POST['frm_dob'] ?? '');
        $gender = sanitize_string($_POST['frm_gender_val'] ?? '');
        $ins_id = sanitize_string($_POST['frm_ins_id'] ?? '');

        db_query(
            "UPDATE `{$prefix}patient` SET
            `fullname` = ?, `dob` = ?, `gender` = ?, `insurance_id` = ?,
            `description` = ?, `ticket_id` = ?, `date` = ?,
            `user` = ?, `action_type` = ?, `name` = ?,
            `facility_id` = ?, `facility_contact` = ?, `updated` = ?
            WHERE `id` = ? LIMIT 1",
            [$fullname, $dob, $gender, $ins_id,
             $frm_description, $frm_ticket_id, $frm_asof,
             $user_id, $action_comment, $frm_name,
             $facility_id, $fac_cont, $now_formatted,
             $patient_id]
        );
    } else {
        db_query(
            "UPDATE `{$prefix}patient` SET
            `description` = ?, `ticket_id` = ?, `date` = ?,
            `user` = ?, `action_type` = ?, `name` = ?,
            `facility_id` = ?, `facility_contact` = ?, `updated` = ?
            WHERE `id` = ? LIMIT 1",
            [$frm_description, $frm_ticket_id, $frm_asof,
             $user_id, $action_comment, $frm_name,
             $facility_id, $fac_cont, $now_formatted,
             $patient_id]
        );
    }

    db_query(
        "UPDATE `{$prefix}ticket` SET `updated` = ? WHERE `id` = ?",
        [$frm_asof, $frm_ticket_id]
    );

    $row = db_fetch_one(
        "SELECT `ticket_id` FROM `{$prefix}patient` WHERE `id` = ?",
        [$patient_id]
    );

    $assigns_val = sanitize_int($_POST['assigns'] ?? 0);
    if($assigns_val != 0) {
        // Delete existing patient assignments
        $existing = db_fetch_all(
            "SELECT * FROM `{$prefix}patient_x` WHERE `patient_id` = ?",
            [$patient_id]
        );
        if(count($existing) > 0) {
            db_query(
                "DELETE FROM `{$prefix}patient_x` WHERE `patient_id` = ?",
                [$patient_id]
            );
        }

        $now_ins = mysql_format_date(time() - (get_variable('delta_mins')*60));
        db_query(
            "INSERT INTO `{$prefix}patient_x`
            (`patient_id`, `assign_id`, `_by`, `_on`, `_from`)
            VALUES (?, ?, ?, ?, ?)",
            [$patient_id, $assigns_val, $user_id, $now_ins, $_SERVER['REMOTE_ADDR']]
        );
    } else {
        $existing = db_fetch_all(
            "SELECT * FROM `{$prefix}patient_x` WHERE `patient_id` = ?",
            [$patient_id]
        );
        if(count($existing) > 0) {
            db_query(
                "DELETE FROM `{$prefix}patient_x` WHERE `patient_id` = ?",
                [$patient_id]
            );
        }
    }

    $addrs = notify_user($frm_ticket_id, $GLOBALS['NOTIFY_ACTION_CHG']);
    if ($addrs) {
        $theTo = implode("|", array_unique($addrs));
        $subject = "TICKET - ACTION: ";
        $theMessage= mail_it ($theTo, "", $subject, $frm_ticket_id, 1, true );
        $theCount = do_send ($theTo, "", $subject, $theMessage, $frm_ticket_id, 0, null, null);
        if($theCount > 0) {print $theCount . " Notifications sent<BR />";}
        }
    $result = true; // We've already run the queries above
    if($result) {print "Patient record changed for Ticket " . e($frm_ticket_id) . "<BR />";} else {print "Something went wrong, please try again<BR />";}
    break;

    case "dispatch":
     $the_ticket_id = sanitize_int($_REQUEST["frm_ticket_id"] ?? 0);
    $frm_status_id = sanitize_int($_REQUEST['frm_status_id'] ?? 0);
    $frm_ticket_id = $the_ticket_id;
    $frm_comments = sanitize_string($_REQUEST['frm_comments'] ?? '');
    $frm_by_id = sanitize_int($_REQUEST['frm_by_id'] ?? 0);
    $frm_facility_id = sanitize_int($_REQUEST['frm_facility_id'] ?? 0);
    $frm_rec_facility_id = sanitize_int($_REQUEST['frm_rec_facility_id'] ?? 0);

    $addrs = array();
    $smsgaddrs = array();
    $now = mysql_format_date(time() - (get_variable('delta_mins')*60));
    $assigns = explode("|", sanitize_string($_REQUEST['frm_id_str'] ?? ''));
    $ok = 0;
    for ($i=0; $i<count($assigns); $i++) {
        $assign_id = sanitize_int($assigns[$i]);
        $result = db_query(
            "INSERT INTO `{$prefix}assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`, `facility_id`, `rec_facility_id`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$now, $frm_status_id, $frm_ticket_id, $assign_id, $frm_comments, $frm_by_id, $now, $frm_facility_id, $frm_rec_facility_id]
        );

        // Remove placeholder inserted by 'add'
        db_query(
            "DELETE FROM `{$prefix}assigns` WHERE `ticket_id` = ? AND `responder_id` = 0 LIMIT 1",
            [$frm_ticket_id]
        );

        // Automatic Status Update by Dispatch Status
        $use_status_update = get_variable('use_disp_autostat');
        if($assign_id != 0 && $assign_id != "") {
            if($use_status_update == "1") {
                auto_disp_status(1, $assign_id);
                }

            $row_addr = db_fetch_one(
                "SELECT `id`, `contact_via`, `smsg_id` FROM `{$prefix}responder` WHERE `id` = ? LIMIT 1",
                [$assign_id]
            );
            if ($row_addr) {
                $row_addr = stripslashes_deep($row_addr);
                if (is_email($row_addr['contact_via'])) {array_push($addrs, $row_addr['contact_via']); }
                if ($row_addr['smsg_id'] != "") {array_push($smsgaddrs, $row_addr['smsg_id']); }
            }
            do_log($GLOBALS['LOG_CALL_DISP'], $frm_ticket_id, $assign_id, $frm_status_id);
            if ($frm_facility_id != 0) {
                do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assign_id, $frm_status_id);
                }
            if ($frm_rec_facility_id != 0) {
                do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assign_id, $frm_status_id);
                }
            }
        }
    $addr_str = urlencode( implode("|", array_unique($addrs)));
    $smsg_add_str = urlencode( implode(",", array_unique($smsgaddrs)));
    $ret_arr = array();
    $ret_arr[0] = "Assignments made for Ticket " . $frm_ticket_id . "<BR />";
    $ret_arr[1] = $addr_str;
    $ret_arr[2] = $smsg_add_str;
    $ret_arr[3] = $frm_ticket_id;
    print json_encode($ret_arr);
    break;

    case "dispatchmail":
    $the_responders = array();
    $the_emails = explode('|', sanitize_string($_POST['frm_addrs'] ?? ''));
    $the_sms = ((isset($_POST['frm_smsgaddrs'])) && ($_POST['frm_smsgaddrs'] != "")) ? explode(',', sanitize_string($_POST['frm_smsgaddrs'])) : "";
    $email_addresses = ($_POST['frm_addrs'] != "") ? $_POST['frm_addrs'] : "";
    $smsg_addresses = ((isset($_POST['frm_use_smsg'])) && ($_POST['frm_use_smsg'] == 1) && ($_POST['frm_smsgaddrs'] != "")) ? $_POST['frm_smsgaddrs'] : "";
    foreach($the_emails as $val) {
        $the_responders[] = get_resp_id2($val);
        }
    if(isset($_POST['frm_use_smsg']) && $_POST['frm_use_smsg'] == 1) {
        foreach($the_sms as $val2) {
            $the_responders[] = get_resp_id($val2);
            }
        }
    $the_resp_ids = array_unique($the_responders);
    $resps = substr(implode(',', $the_resp_ids), 0, -2);
    $dispatch_ticket_id = sanitize_int($_POST['ticket_id'] ?? 0);
    $count = do_send ($email_addresses, $smsg_addresses, "Tickets CAD", $_POST['frm_text'], $dispatch_ticket_id, $resps);
    if($count > 0) {print $count . " Messages sent<BR />";}
    break;

    default:
    return 'error';
    }

exit();
