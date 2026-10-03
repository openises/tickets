# Legacy (v3.44) endpoint authentication inventory

Every PHP file in this repository that a web server can be asked for, with how it decides whether the caller may have what it returns. Written for release 3.44.7 after a report showed that `ajax/reports.php` returned incident contact names, addresses and descriptions to a request with no session at all. The v3 application authenticated its *pages* (`do_login()`), but almost none of the scripts those pages call: a script that was not a page simply never asked who was calling.

**This file is also a test fixture.** `tests/Unit/EndpointAuthTest.php` fails when a PHP file is missing from the table, when a file listed as gated does not call its gate before it outputs anything or touches the database, or when a file is listed as public without a reason. To add an endpoint, add the gate to the file and add its row here; to make one public, say why in the row.

## What "authenticated" means here

A request is authenticated when its PHP session names a user (`$_SESSION['user_id']`, set at login by `do_login()` or the mobile login) **and** that user's `expires` timestamp in the `user` table is still in the future. Nothing else is checked: like every `do_login()` page, any logged-in account passes (guest, unit, portal requester, operator, administrator). The legacy application has no per-record access control (any logged-in user may open any incident id) and this change does not add one.

Two gates, both in `incs/login.inc.php`:

* `do_login(basename(__FILE__));` — for **pages**. An anonymous visitor gets the login form (existing behaviour, now used everywhere a page is served).
* `require_login_or_401();` — for **data endpoints** (AJAX actions, JSON and HTML fragments, images, downloads). An anonymous caller gets `HTTP 401`, `Content-Type: text/plain` and the body `Authentication required.` Nothing else is read, written or sent, and no session is created for a visitor who has no session cookie. While a caller keeps passing the gate its `expires` is pushed forward (at most one `UPDATE` per half of `session_timeout`), so a screen that polls these endpoints stays logged in, as it did when they asked for nothing.

Both must be the first thing a script does after loading `incs/functions.inc.php`: before any output, query, file access or mail.

## Classes

| Class | Meaning |
|---|---|
| `LOGIN` | A page. Calls `do_login(basename(__FILE__));` before any output or data access. |
| `GATED` | A data endpoint or fragment. Calls `require_login_or_401();` (a few older ones use `do_login()`) before any output or data access. |
| `PUBLIC` | Deliberately reachable without a session. The reason is mandatory and says why that is safe. |
| `OWN` | Authenticated by a documented rule of its own (the mobile page and its fragments, which follow the `responder_mobile_forcelogin` setting). |
| `CLI` | Command-line script. Its first statement ends any web request with 404. |
| `TOOL` | Like `CLI`, but the administrator documentation tells people to run it in a browser when they cannot log in. Over the web it answers 404 unless an empty `<name>.enable` file exists next to it. |
| `INCLUDE` | Library file. Defines functions, classes and constants; prints nothing at top level; not an entry point. |
| `STATIC` | No PHP code at all (static HTML or text). |

The **Before** column is the state in 3.44.6: `open` = no authentication at all, `partly` = a gate or an own check existed but was late or incomplete, `gated` = already correct, `public` = deliberately anonymous, `n/a` = nothing to authenticate.

## Totals

| | Files |
|---|---|
| PHP files inventoried | 697 |
| behind the login: `LOGIN` pages + `GATED` data endpoints | 540 (168 + 372) |
| -- open in 3.44.6, now gated | 452 |
| -- partly protected in 3.44.6 (gate ran late, or an own check) | 14 |
| -- already correctly gated in 3.44.6 | 73 |
| -- new in 3.44.7 | 1 |
| `PUBLIC` (deliberately anonymous) | 16 |
| `OWN` | 3 |
| `CLI` / `TOOL` (command line; web 404) | 29 / 2 |
| `INCLUDE` | 102 |
| `STATIC` | 5 |

## Things the maintainer should decide

1. **`tracker.php`** accepts GPS positions from GPSGate/BT747/uTrack devices with no secret: anyone who knows or guesses a device name can overwrite that device's position. Devices cannot hold a session, so it stays public; a shared key in the settings table checked by the script would close it, but every deployed tracker would need reconfiguring.
2. **The websocket relay** (`socketserver/server.php`, started from the login pages) trusts a user id supplied by the client in its first message, including the administrator checks for shutdown and restart. Fixing that is a protocol change; the script that *starts* the relay stays public because the mobile login page calls it before anyone is logged in.
3. **`external.php`, `rss/external_XML.php`, `rss/external_RSS.php`** are the "XML interface for external systems": open incidents, every unit and every facility (and, for the `rss/` copies, every incident row including caller name and phone). They were anonymous; they now need a session. If an outside system reads them, it must log in, or the maintainer can add a keyed variant.
4. **Authorization, not just authentication.** The stock `guest` account (password `guest` on a fresh install) and portal requester accounts now pass every gate. Destructive bulk tools (`class_init.php`, `reset_regions.php`, `cleanse_regions.php`, `reset_responder_status.php`, `quick_start.php`, `loader.php`) additionally require an administrator; the rest of the application keeps its existing level checks, which are patchy.
5. **Dead scratch scripts** (`md5.php`, `nest.php`, `tern1.php`, `global.php`, `y.php`, `dirname.php`, `delfile.php`, `orphan.php`, `tb.php`, `read_pdf.php`, `wxalert.php`, `wxalert1.php`) have no caller in the application; they are now command-line only and could be deleted.

## Inventory

| File | Class | Before | Reason |
|---|---|---|---|
| `about.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `action.php` | LOGIN | gated | Was already gated by do_login(). writes: insert action, update ticket, delete action, update action; reads: unit_types, ticket, action, assigns, responder, ... |
| `action_w.php` | LOGIN | gated | Was already gated by do_login(). writes: insert action, update ticket, delete action, update action; reads: unit_types, ticket, action, codes, assigns, ... |
| `add.php` | LOGIN | gated | Was already gated by do_login(). writes: update caller_id, insert ticket, update ticket, insert requests, ...; reads: states_translator, caller_id, facilities, in_types, major_incidents, ...; accepts uploads |
| `add_facnote.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: delete facnotes, insert facnotes; reads: facnotes, fac_case_cat |
| `add_nm.php` | LOGIN | gated | Was already gated by do_login(). writes: insert ticket, update ticket; reads: facilities, ticket, the, in_types, codes |
| `add_note.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: update ticket; reads: codes, ticket |
| `ajax/action_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, action, assigns, responder, un_status, ... |
| `ajax/ajax_board.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, un_status, assigns, ticket, allocates, ... |
| `ajax/alertlist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: roadinfo, conditions |
| `ajax/basic_statistics.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocates, ticket, assigns, in_types, responder, ... |
| `ajax/bulk_add_allocations.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert allocations; reads: member, allocations, capability_types, training_packages, equipment_types, ... |
| `ajax/capabilities_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, capability_types |
| `ajax/captions.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/check_all_ringfences.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, mmarkup |
| `ajax/check_exclzone.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, mmarkup |
| `ajax/check_memory.php` | GATED | open | Server memory and disk figures (shown in the top frame). Was anonymous; now 401 without a session. |
| `ajax/check_ringfence.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, mmarkup |
| `ajax/clothing_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, clothing_types |
| `ajax/create_image.php` | PUBLIC | public | CAPTCHA image for the public contact form. Draws the text it is given; reads nothing. |
| `ajax/db_summary.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member_status, member_types, member, training_packages |
| `ajax/dedupe.php` | LOGIN | gated | Was already gated by do_login(). writes: delete captions, insert captions, delete states_translator, insert states_translator, ...; reads: captions, states_translator, codes, hints, insurance |
| `ajax/del_message.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert messages_bin, delete messages; reads: messages |
| `ajax/del_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert messages_bin, truncate messages; reads: messages |
| `ajax/del_sel_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert messages_bin, delete messages; reads: messages |
| `ajax/delfile.php` | LOGIN | partly | Gate existed but ran after output or a query; moved to the top. Was already gated by do_login(). writes: delete files, delete files_x; reads: files, files_x |
| `ajax/deltile.php` | LOGIN | gated | Was already gated by do_login().  |
| `ajax/deltiles.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/dispatchmail_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket |
| `ajax/do_day_night_swap.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/do_error.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert ajax_log |
| `ajax/do_log.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/download.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/empty_wastebasket.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: truncate messages_bin |
| `ajax/equipment_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, equipment_types |
| `ajax/facboard_incidents.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: patient, assigns, patient_x, insurance, facnotes, ... |
| `ajax/facs_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: fac_status, fac_types, facilities, allocates |
| `ajax/file_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: files_x, files |
| `ajax/fileman_list.php` | LOGIN | gated | Was already gated by do_login(). reads: files, files_x |
| `ajax/form_post.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- sends mail |
| `ajax/fs_facs_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: fac_status, fac_types, facilities, allocates |
| `ajax/fs_inc_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: action, patient, assigns, ticket, allocates, ... |
| `ajax/fs_resp_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, assigns, un_types, ticket, un_status, ... |
| `ajax/full_scr_assignments.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, ticket, user, responder, allocates, ... |
| `ajax/full_screen_facilities.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: fac_status, fac_types, facilities, allocates |
| `ajax/full_screen_incidents.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: action, patient, ticket, allocates, in_types, ... |
| `ajax/full_screen_responders.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, assigns, un_types, ticket, un_status, ... |
| `ajax/fullsit_incident_options.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, ticket |
| `ajax/fullsit_incidents.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: action, patient, ticket, allocates, in_types, ... |
| `ajax/gen_file_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: files |
| `ajax/geo_loc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_addrs.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_alerticon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: conditions |
| `ajax/get_assignments.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, responder, ticket |
| `ajax/get_assigns.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, responder, call |
| `ajax/get_bldg_details.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: places |
| `ajax/get_callboard.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, responder, ticket, un_status, in_types |
| `ajax/get_contact_via.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_fac_status_control.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: fac_status, facilities |
| `ajax/get_fac_status_controls.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: fac_status, facilities |
| `ajax/get_fields.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: wizard_settings |
| `ajax/get_final_dispatch_details.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_latest_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `ajax/get_loc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder |
| `ajax/get_member_contact_email.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_member_contactdetails.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_member_fulldetails.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_member_photo.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_message_totals.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages, messages_bin |
| `ajax/get_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_mmarkup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocates, mmarkup, region, responder, facilities |
| `ajax/get_poly_buttons.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocates, mmarkup, region, responder, facilities |
| `ajax/get_regions_control.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/get_replacetext.php` | LOGIN | gated | Was already gated by do_login(). reads: replacetext_order, ticket, warnings, user, responder, ... |
| `ajax/get_roster_details.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: personnel |
| `ajax/get_signal.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: codes |
| `ajax/get_status_bgcolors.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: un_status |
| `ajax/get_status_control.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: un_status, responder |
| `ajax/get_status_control_new.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, un_status |
| `ajax/get_status_controls.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: un_status, responder |
| `ajax/get_status_textcolors.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: un_status |
| `ajax/get_unit_assignments.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, responder, ticket |
| `ajax/get_unit_categories.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder |
| `ajax/get_user_details.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user |
| `ajax/gettiles.php` | LOGIN | partly | Gate existed but ran after output or a query; moved to the top. Was already gated by do_login(). makes outbound requests |
| `ajax/inc_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: action, patient, assigns, ticket, allocates, ... |
| `ajax/lines_a.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/list_all_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `ajax/list_arch_msgs.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/list_certs_due.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member, training_packages |
| `ajax/list_files.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: major_incidents, files_x, files |
| `ajax/list_member_allocations.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member |
| `ajax/list_message_totals.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `ajax/list_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `ajax/list_mi_total.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: major_incidents |
| `ajax/list_mmarkup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup_cats, mmarkup |
| `ajax/list_packages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/list_sent_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `ajax/list_tables.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/list_warnlocations.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: warnings |
| `ajax/list_waste_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages_bin |
| `ajax/loc_warn_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: warnings |
| `ajax/m_capabilities_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, capability_types |
| `ajax/m_clothing_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, clothing_types |
| `ajax/m_equipment_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, equipment_types |
| `ajax/m_event_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, events |
| `ajax/m_file_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mdb_files |
| `ajax/m_training_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, training_packages |
| `ajax/m_vehicle_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, vehicles, vehicle_types |
| `ajax/mail_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, assigns, responder, un_status, codes |
| `ajax/main_memberlist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, member_types, member_status, team, user |
| `ajax/mdb_reports.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: defined_fields, training_packages, events, capability_types, equipment_types, ... |
| `ajax/member_capabilities.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, training_packages, capability_types, equipment_types, clothing_types |
| `ajax/member_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member_types, member_status, member, team, user |
| `ajax/member_remote.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: date, member, member_types, member_status |
| `ajax/memberlist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user, member, member_types, member_status, team |
| `ajax/mi_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup_cats, mmarkup, major_incidents, mi_types |
| `ajax/mi_tic_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: action, patient, ticket, allocates, in_types, ... |
| `ajax/mob_list_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `ajax/mob_message.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `ajax/mob_messagelist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, responder, user, messages |
| `ajax/mobile_buttons.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user, responder, ticket, assigns, unit_types, ... |
| `ajax/mobile_main.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, log, responder, un_status, user, ... |
| `ajax/mobile_tktlist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user, responder, ticket, assigns, unit_types, ... |
| `ajax/msg_status.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update messages; reads: user, messages |
| `ajax/mt_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup_cats, mmarkup, ticket, places, major_incidents, ... |
| `ajax/new_inc_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update caller_id, insert ticket; reads: states_translator, caller_id, facilities, in_types, major_incidents, ... |
| `ajax/note_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: codes |
| `ajax/optimize_table.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/patient_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, patient_x, patient, insurance, facilities, ... |
| `ajax/reports.php` | GATED | open | THE REPORTED LEAK: returned incident contact names, phone numbers, addresses, descriptions and station logs to a request with no session. Anonymous callers now get 401; the two download scripts run it inside their own request. |
| `ajax/resp_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, assigns, un_types, ticket, un_status, ... |
| `ajax/responder_data.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder |
| `ajax/responder_infowindows.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, assigns, un_types, un_status, responder, ... |
| `ajax/restore_message.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert messages, delete messages_bin; reads: messages_bin |
| `ajax/restore_sel_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert messages, delete messages_bin; reads: messages_bin |
| `ajax/routes_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, allocations, training_packages, capability_types, equipment_types, ... |
| `ajax/show_ticket.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, in_types, facilities |
| `ajax/sidebar_list_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mi_x, messages |
| `ajax/sit_facilities.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update facilities; reads: fac_status, fac_types, facilities, allocates |
| `ajax/sit_incidents.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: action, patient, ticket, allocates, in_types, ... |
| `ajax/sit_log.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: log |
| `ajax/sit_responders.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update responder; reads: unit_types, assigns, un_types, ticket, un_status, ... |
| `ajax/sit_scheduled.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, allocates |
| `ajax/statistics.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: stats_settings, ticket, allocates, assigns, in_types, ... |
| `ajax/stats_type.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: stats_type |
| `ajax/training_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, training_packages |
| `ajax/twitter_direct_send.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/twitter_send.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ajax/update_assigns.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update assigns |
| `ajax/update_localmap_boundary.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update settings; reads: settings |
| `ajax/update_localmaps.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update settings; reads: settings |
| `ajax/update_responder_location.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update responder; reads: responder, facilities |
| `ajax/vehicle_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, vehicles, vehicle_types |
| `ajax/view_arch_msg.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder |
| `ajax/view_event.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: events |
| `ajax/view_training_package.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: training_packages |
| `ajax/view_vehicle_details.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: vehicles, vehicle_types, member |
| `ajax/waste_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: waste_basket_m |
| `ajax/wastebasket.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: waste_basket_m |
| `ajax/wb_delete.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete waste_basket_f, delete waste_basket_m; reads: waste_basket_m, waste_basket_f |
| `ajax/wb_restore.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert member, update member, insert allocations, delete waste_basket_m, ...; reads: waste_basket_m, waste_basket_f |
| `ajax/write_debuglog.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `archive_message.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: contacts, user; sends mail |
| `areas_sc.php` | GATED | partly | Had its own session or administrator check that the rest of the application did not share; now uses the common gate. Data/fragment endpoint, now returns 401 to anonymous callers |
| `as_up_fac_status.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update facilities |
| `as_up_un_status.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update responder |
| `assign_del.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete assigns; reads: assigns |
| `assign_res.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update assigns; reads: assigns |
| `assigns_t.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update assigns |
| `audio.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `auto_disp_status.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: update auto_disp_status, insert auto_disp_status; reads: un_status, auto_disp_status |
| `auto_status.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: update auto_status, insert auto_status; reads: un_status, codes, auto_status |
| `baaChart.php` | INCLUDE | n/a | Chart-drawing class library (included by the graph pages); defines classes and constants only. |
| `banner.php` | LOGIN | gated | Was already gated by do_login(). reads: mmarkup_cats |
| `board.php` | LOGIN | partly | Had its own session or administrator check that the rest of the application did not share; now uses the common gate. Page, now shows the login form to anonymous visitors -- writes: insert assigns, update ticket, update responder, update assigns, ...; reads: ticket, allocates, assigns, responder, unit_types, ... |
| `bulk_admin/bulk_add_allocation.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert allocations; reads: member, allocations, training_packages |
| `bulk_admin/bulk_add_capab.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert allocations; reads: member, allocations, capability_types, training_packages, equipment_types, ... |
| `call_hist.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: ticket, in_types, facilities |
| `cancel_add.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete action, delete patient, delete ticket; reads: action, patient, ticket |
| `capts.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: update captions, update applied; reads: captions |
| `cb.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `chat.php` | LOGIN | gated | Was already gated by do_login(). writes: delete chat_messages; reads: chat_messages, codes, user |
| `chat_invite.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete chat_invites, insert chat_invites; reads: chat_invites |
| `chat_rd.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete chat_messages; reads: chat_messages, user |
| `chat_wl.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user |
| `chat_wr.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert chat_messages |
| `checkports.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- makes outbound requests |
| `circle.php` | LOGIN | gated | Was already gated by do_login(). reads: mmarkup_cats |
| `city_graph.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket |
| `class_init.php` | LOGIN | open | Page, now shows the login form to anonymous visitors; also administrators only (destructive bulk operation) -- writes: drop courses, create courses, update current_timestamp, drop courses_taken, ... |
| `cleanse_regions.php` | LOGIN | open | Page, now shows the login form to anonymous visitors; also administrators only (destructive bulk operation) -- writes: delete allocates, insert allocates; reads: region, ticket, user, responder, facilities, ... |
| `close_in.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: update ticket, update allocates, update assigns; reads: ticket, codes, assigns, user, responder, ... |
| `config.php` | LOGIN | gated | Was already gated by do_login(). writes: delete notify, update complete, update notify, insert notify, ...; reads: user, organisations, ticket, notify, mailgroup, ... |
| `contact.php` | PUBLIC | public | Public "request login details" form linked from the login page. Stores a pending request that only becomes a request to the administrator after the e-mail confirmation link is used. |
| `contact_response.php` | PUBLIC | public | E-mail confirmation link for the contact form. Guarded by a per-request secret (sec_code) that only the requester's mailbox receives; deletes the pending row and notifies the administrator. |
| `course_report.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: courses_taken, user, courses |
| `csv_import_scripts/clothing_csv_import.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert clothing_types; reads: clothing_types; accepts uploads |
| `csv_import_scripts/equipment_csv_import.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert equipment_types; reads: equipment_types; accepts uploads |
| `csv_import_scripts/member_csv_import.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert member; reads: member, team, member_types, member_status; accepts uploads |
| `csv_import_scripts/training_csv_import.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert training_packages; reads: training_packages; accepts uploads |
| `csv_import_scripts/vehicles_csv_import.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert vehicles; reads: member, vehicles; accepts uploads |
| `db_loader.php` | LOGIN | partly | Had its own session or administrator check that the rest of the application did not share; now uses the common gate. Page, now shows the login form to anonymous visitors -- reads: css_day, settings |
| `delete_module.php` | LOGIN | gated | Was already gated by do_login(). writes: delete modules; reads: modules |
| `delfile.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `destroy.php` | PUBLIC | public | Clears the caller's own session and redirects to the login page; touches nothing else. |
| `dirname.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `do_all_mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: user, codes; sends mail |
| `do_aprs.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `do_cid.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete ticket, insert ticket; reads: constituents, ticket |
| `do_color_checker.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: unit_types, responder |
| `do_day_night_swap.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update responder |
| `do_direcs_mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: responder; sends mail |
| `do_fac_direcs_mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: responder; sends mail |
| `do_fac_mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: facilities; sends mail |
| `do_get_line_ct.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns |
| `do_get_sess_vbl.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `do_indiv_mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: responder; sends mail |
| `do_member_mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: member, assigns, ticket, member_status; sends mail |
| `do_send.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- sends mail |
| `do_send_mail.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- sends mail |
| `do_session.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `do_session_get.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `do_unit_mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: assigns, ticket, responder, codes, un_status; sends mail |
| `docker-autoinstall.php` | CLI | open | Docker first-run installer: creates the schema and the admin account (admin/admin by default). The entrypoint runs it from the command line, so it never needed to be a web page; it is now refused over HTTP. |
| `download_report.php` | GATED | open | Report download (.doc/.xls). Used to fetch ajax/reports.php over HTTP from the server to itself without cookies (which is why nobody noticed the report endpoint was open); now runs it inside its own request, so the caller's session authorizes the data. Anonymous callers get 401. |
| `download_report_csv.php` | GATED | open | Report download (.csv). Same change as download_report.php: no cookieless HTTP self-fetch, the caller's session authorizes the data. Anonymous callers get 401. |
| `edit.php` | LOGIN | gated | Was already gated by do_login(). writes: update ticket, insert requests, insert files, update allocates, ...; reads: ticket, requests, files, allocates, mi_x, ...; accepts uploads |
| `edit_nm.php` | LOGIN | partly | Gate existed but ran after output or a query; moved to the top. Was already gated by do_login(). writes: update ticket, update assigns, delete ticket, delete action; reads: assigns, ticket, action, in_types, constituents, ... |
| `email_lists.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: delete mailgroup_x, update mailgroup_x, insert mailgroup_x; reads: mailgroup, contacts, responder, mailgroup_x, the |
| `error.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `external.php` | GATED | open | XML interface for external systems: open incidents plus every column of every unit and facility (names, phones, pagers, e-mail). Was anonymous, now needs a session. MAINTAINER DECISION 3 below. |
| `fac_routes.php` | LOGIN | gated | Was already gated by do_login(). reads: fac_types, fac_status, responder, facilities, allocates, ... |
| `fac_routes_nm.php` | LOGIN | gated | Was already gated by do_login(). reads: fac_types, fac_status, responder, facilities, allocates, ... |
| `faccategories.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert fac_case_cat, update fac_case_cat; reads: fac_case_cat |
| `facilities.php` | LOGIN | gated | Was already gated by do_login(). writes: delete facilities, update facilities, insert allocates, delete allocates, ...; reads: fac_types, facilities, mailgroup, database, allocates, ...; accepts uploads |
| `facilities_nm.php` | LOGIN | gated | Was already gated by do_login(). writes: delete facilities, update facilities, insert allocates, delete allocates, ...; reads: fac_types, facilities, mailgroup, fac_status, allocates, ...; accepts uploads |
| `facility_board.php` | LOGIN | gated | Was already gated by do_login(). reads: user |
| `file_upload.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert files, insert files_x; reads: responder, ticket, facilities, user, files; accepts uploads |
| `fileman.php` | LOGIN | gated | Was already gated by do_login().  |
| `forms/add_capability.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, capability_types |
| `forms/add_clothing.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, clothing_types |
| `forms/add_equipment.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, equipment_types |
| `forms/add_event.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, events |
| `forms/add_file.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member |
| `forms/add_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/add_training.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, training_packages |
| `forms/add_vehicle.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: member, vehicles |
| `forms/callboard.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/edit_capability.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member, capability_types |
| `forms/edit_clothing.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member, clothing_types |
| `forms/edit_equipment.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member, equipment_types |
| `forms/edit_event.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member, events |
| `forms/edit_file.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mdb_files, member |
| `forms/edit_form.php` | LOGIN | gated | Was already gated by do_login().  |
| `forms/edit_training.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member, training_packages |
| `forms/edit_vehicle.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, member, vehicles |
| `forms/facilities_add_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, fac_status, mailgroup |
| `forms/facilities_add_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, fac_status, mailgroup |
| `forms/facilities_edit_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: facilities, mmarkup, fac_status, mailgroup |
| `forms/facilities_edit_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: facilities, mmarkup, fac_status, mailgroup |
| `forms/facilities_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: states_translator |
| `forms/facilities_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/facilities_view_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocates, region, facilities, fac_status |
| `forms/facilities_view_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocates, region, facilities, fac_status |
| `forms/full_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: states_translator |
| `forms/full_sit_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: states_translator |
| `forms/full_sit_screen_alt.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: states_translator |
| `forms/inc_wizard.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: wizard_settings, in_types |
| `forms/mdb_reports_print_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/mi_add_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mi_types, mmarkup, user, ticket |
| `forms/mi_add_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mi_types, mmarkup, user, ticket |
| `forms/mi_edit_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, mmarkup_cats, major_incidents, mi_x, mi_types, ... |
| `forms/mi_edit_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, mmarkup_cats, major_incidents, mi_x, mi_types, ... |
| `forms/mi_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/mi_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/mi_view_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, mmarkup_cats, major_incidents, mi_x, assigns, ... |
| `forms/mi_view_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, mmarkup_cats, major_incidents, mi_x, assigns, ... |
| `forms/mmarkup_add_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup_cats |
| `forms/mmarkup_edit_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, mmarkup_cats |
| `forms/mmarkup_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/mmarkup_view_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, mmarkup_cats |
| `forms/mobile_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user, responder |
| `forms/no_permission_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/reports_print_screen.php` | LOGIN | gated | Was already gated by do_login().  |
| `forms/sit_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns |
| `forms/sit_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns |
| `forms/ticket_after_edit.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, in_types, facilities, fac_types, fac_status, ... |
| `forms/ticket_after_edit_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, in_types, facilities |
| `forms/ticket_print_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, un_status, assigns, ticket, in_types, ... |
| `forms/ticket_view_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, un_status, assigns, ticket, in_types, ... |
| `forms/ticket_view_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, un_status, assigns, ticket, in_types, ... |
| `forms/units_add_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: facilities, mmarkup, un_status, fac_types |
| `forms/units_add_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mmarkup, un_status, facilities, fac_types |
| `forms/units_edit_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, facilities, responder_x_member, responder, mmarkup, ... |
| `forms/units_edit_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, responder_x_member, responder, mmarkup, un_status, ... |
| `forms/units_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/units_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/units_view_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocates, region, responder, mmarkup, un_status, ... |
| `forms/units_view_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocates, region, responder, mmarkup, un_status, ... |
| `forms/view_capability.php` | LOGIN | gated | Was already gated by do_login(). reads: allocations, member, capability_types |
| `forms/view_clothing.php` | LOGIN | gated | Was already gated by do_login(). reads: allocations, member, clothing_types |
| `forms/view_equipment.php` | LOGIN | gated | Was already gated by do_login(). reads: allocations, member, equipment_types |
| `forms/view_event.php` | LOGIN | gated | Was already gated by do_login(). reads: allocations, member, events |
| `forms/view_file.php` | LOGIN | gated | Was already gated by do_login(). reads: files, member |
| `forms/view_form.php` | LOGIN | gated | Was already gated by do_login().  |
| `forms/view_training.php` | LOGIN | gated | Was already gated by do_login(). reads: allocations, member, training_packages |
| `forms/view_vehicle.php` | LOGIN | gated | Was already gated by do_login(). reads: allocations, member, vehicles |
| `forms/wl_add_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/wl_add_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/wl_edit_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: warnings |
| `forms/wl_edit_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: warnings |
| `forms/wl_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/wl_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `forms/wl_view_screen.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: warnings |
| `forms/wl_view_screen_NM.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: warnings |
| `full_scr.php` | LOGIN | gated | Was already gated by do_login().  |
| `full_sit_scr.php` | LOGIN | gated | Was already gated by do_login().  |
| `get_all_emails.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `get_current_user_id.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `get_latest_chat.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: chat_invites |
| `get_latest_id.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: chat_invites, ticket, allocates, responder, assigns, ... |
| `get_latest_ticket.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket |
| `get_latest_unit.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder |
| `get_messages.php` | LOGIN | partly | Gate existed but ran after output or a query; moved to the top. Was already gated by do_login().  |
| `get_php_aprs.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `get_tiles.php` | LOGIN | gated | Was already gated by do_login().  |
| `get_un_status.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder |
| `getinfo.php` | LOGIN | gated | Was already gated by do_login().  |
| `global.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `gtrack.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- makes outbound requests |
| `help.php` | LOGIN | partly | Had its own session or administrator check that the rest of the application did not share; now uses the common gate. Page, now shows the login form to anonymous visitors |
| `hints_config.php` | LOGIN | gated | Was already gated by do_login(). reads: user, hints |
| `icons/buttons/landb.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: create if |
| `icons/gen_fac_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `icons/gen_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ics.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: create ics, alter ics; reads: ics |
| `ics/ics.css.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ics/ics202.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics/ics205.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics/ics205a.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics/ics206.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics/ics213.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics/ics213rr.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, finance, user, contacts; sends mail |
| `ics/ics214.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics/ics214a.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics/ics221.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert ics, update ics, delete ics; reads: ics, user, contacts; sends mail |
| `ics202.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: user; sends mail |
| `ics205.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: user; sends mail |
| `ics205a.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: user; sends mail |
| `ics213.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: contacts, user; sends mail |
| `ics213rr.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert settings; reads: settings, finance; sends mail |
| `ics214.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: user; sends mail |
| `iframe1.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `import_mdb.php` | GATED | partly | Had its own session or administrator check that the rest of the application did not share; now uses the common gate. Data/fragment endpoint, now returns 401 to anonymous callers |
| `inc_types_graph.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, in_types |
| `incident_popup.php` | LOGIN | gated | Was already gated by do_login(). reads: unit_types, fac_types, un_status, fac_status, assigns, ... |
| `incident_popup_nm.php` | LOGIN | gated | Was already gated by do_login(). reads: ticket, assigns, responder |
| `incs/MySQLDump.class.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/addrlkup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `incs/all_forms_js_variables.inc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: in_types, states_translator, un_status, fac_status, assigns, ... |
| `incs/alt_graph.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: tracks |
| `incs/bits.class.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/browser.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/capts.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/cell_addrs.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/cells.php` | STATIC | n/a | Static list of carrier e-mail-to-SMS gateway domains; contains no PHP code, no data of this installation. |
| `incs/cells_x.php` | STATIC | n/a | Static list of carrier e-mail-to-SMS gateway domains; contains no PHP code, no data of this installation. |
| `incs/compat.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/config.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/config.setcenter.inc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update settings, insert settings; reads: states_translator |
| `incs/config.tiles.inc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert settings, update settings |
| `incs/db.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/do_top.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `incs/exportcsv.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/full_scr.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/full_sit_comms.inc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user |
| `incs/functions.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/functions_major.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/functions_major_nm.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/functions_nm.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/gd_image.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/get_aprs_poll.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `incs/html2text.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/html_to_doc.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/inc_num.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/install_schema.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/istest.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpg-config.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_bar.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_errhandler.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_error.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_gradient.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_line.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_mgraph.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_plotband.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_plotmark.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_regstat.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_ttf.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/jpgraph_utils.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/landb.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/lang/en.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/links.inc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `incs/log_codes.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/login.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/mail_form.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, assigns, responder, un_status, codes |
| `incs/member.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/messaging.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/modules.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/mysql_ tickets_apostrophe.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/page_addrs.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/phonelkup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- makes outbound requests |
| `incs/phpcoord.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/release_version.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/remotes.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/report_inline.inc.php` | GATED | new | NEW in 3.44.7. Runs ajax/reports.php inside the download scripts (replaces their cookieless HTTP self-request); gated itself, so a direct request gets 401. |
| `incs/routes_inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/security.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/set_cook_exp.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `incs/smtp.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/socket2me.inc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user |
| `incs/status_cats.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/stemmer.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/tables.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/usng.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/variables.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `incs/versions.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `index.php` | PUBLIC | public | Frameset entry point and version gate. It shows no data; the login form is served by main.php, which calls do_login(). |
| `install.php` | PUBLIC | public | Installer. Has its own gate (commits b6b5ca6 and 929e8f8): on an installed system every action needs an administrator session; a fresh install has no accounts to log in with. |
| `install_module.php` | LOGIN | gated | Was already gated by do_login(). writes: create if; reads: modules; makes outbound requests |
| `instam.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert tracks_hh, update responder; makes outbound requests |
| `landb.php` | LOGIN | partly | Gate existed but ran after output or a query; moved to the top. Was already gated by do_login(). writes: create if; reads: mmarkup_cats |
| `latitude.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- makes outbound requests |
| `lib/mime_types.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/EasyPeasyICS.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/OAuth.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/POP3.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/autoload.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/get_oauth_token.php` | PUBLIC | public | Vendored PHPMailer helper for obtaining an OAuth token. Placeholder credentials, needs the composer vendor/ autoloader, touches no application data. |
| `lib/phpmailer/htmlfilter.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/src/Exception.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/src/PHPMailer.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/phpmailer/src/SMTP.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/twitter/OAuth.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/twitter/Twitter.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/twitter/twitter.class.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/FUNC.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/MAIL.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/MIME.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/PHP5/FUNC5.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/PHP5/MAIL5.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/PHP5/MIME5.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/PHP5/POP35.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/PHP5/SMTP5.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/POP3.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lib/xpm/SMTP.php` | INCLUDE | n/a | Third-party library; defines classes/functions only. |
| `lines_a.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `load.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `loader.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers; also administrators only (destructive bulk operation) |
| `locatea.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- makes outbound requests |
| `log.php` | LOGIN | partly | Had its own session or administrator check that the rest of the application did not share; now uses the common gate. Page, now shows the login form to anonymous visitors -- writes: delete log; reads: responder, log, user, ticket |
| `logs.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: log |
| `logs1.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: log |
| `mail.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: ticket, contacts; sends mail |
| `mail_all.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: user; sends mail |
| `mail_edit.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: ticket; sends mail |
| `mail_it.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `main.php` | LOGIN | gated | Was already gated by do_login().  |
| `maj_inc.php` | LOGIN | gated | Was already gated by do_login(). writes: delete major_incidents, update major_incidents, insert mi_x, delete mi_x, ...; reads: user, places, major_incidents, database, mi_x, ...; accepts uploads |
| `map_popup.php` | LOGIN | gated | Was already gated by do_login(). reads: the, ticket, unit_types, fac_types, un_status, ... |
| `md5.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `mdb_print_report.php` | LOGIN | gated | Was already gated by do_login().  |
| `mdb_reports.php` | LOGIN | gated | Was already gated by do_login(). reads: team, member |
| `mdb_search.php` | LOGIN | gated | Was already gated by do_login(). reads: member, allocations, training_packages, capability_types, equipment_types, ... |
| `member.php` | LOGIN | gated | Was already gated by do_login(). writes: insert waste_basket_m, delete member, delete allocations, insert waste_basket_f, ...; reads: member_types, member_status, allocations, member, mdb_files, ...; sends mail; accepts uploads |
| `member_capabilities.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: allocations, training_packages, capability_types, equipment_types, clothing_types, ... |
| `message.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert assigns, update messages; reads: messages, contacts, responder, user, ticket, ...; sends mail |
| `messages.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: ticket |
| `mmarkup.php` | LOGIN | partly | Gate existed but ran after output or a query; moved to the top. Was already gated by do_login(). writes: create if; reads: database |
| `mobile.php` | LOGIN | gated | Was already gated by do_login().  |
| `msg_archive.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: messages |
| `native.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- sends mail |
| `nearby.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: ticket |
| `nest.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `ntp2.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- makes outbound requests |
| `opena.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `opengts.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: update settings; makes outbound requests |
| `orphan.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `os_map.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: unit_types, assigns, ticket, responder, un_status, ... |
| `os_watch.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: update ticket; reads: assigns, ticket, responder, in_types, user, ...; sends mail |
| `our_icons/gen_fac_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `our_icons/gen_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `our_icons/gen_mi_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `our_icons/gen_portal_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `our_icons/icons.php` | STATIC | n/a | Static HTML icon gallery; contains no PHP code. |
| `path.inc.php` | INCLUDE | n/a | Variable definitions written by the installer or read by tables.php; prints nothing. |
| `patient.php` | LOGIN | gated | Was already gated by do_login(). writes: insert patient, update ticket, delete patient, update patient, ...; reads: responder, ticket, patient, patient_x, insurance, ... |
| `patient_JF.php` | LOGIN | gated | Was already gated by do_login(). writes: insert patient, update ticket, delete patient, update patient; reads: ticket, patient |
| `patient_w.php` | LOGIN | gated | Was already gated by do_login(). writes: insert patient, update ticket, delete patient, update patient, ...; reads: responder, ticket, patient, patient_x, insurance, ... |
| `persist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `persist2.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `persist3.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `portal.php` | LOGIN | gated | Was already gated by do_login(). reads: user, facilities |
| `portal/ajax/cancel_request.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update requests, update ticket; reads: user, requests |
| `portal/ajax/decline.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update requests; reads: user, requests |
| `portal/ajax/download.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `portal/ajax/file_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: files_x, files |
| `portal/ajax/insert_request.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert requests; reads: user, facilities |
| `portal/ajax/insert_ticket.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert ticket, update requests, update ticket, insert allocates; reads: user, facilities, allocates, requests, portal |
| `portal/ajax/insert_ticket_tentative.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert ticket, update requests, update ticket, insert allocates; reads: user, facilities, allocates, requests, portal |
| `portal/ajax/list_requests.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update requests; reads: user, requests, assigns, ticket |
| `portal/ajax/list_requests_admin.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update requests; reads: user, requests, assigns, ticket |
| `portal/ajax/list_ticketsandresponders.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: requests, ticket, assigns, responder |
| `portal/ajax/requests_summary.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: requests |
| `portal/ajax/requests_wallboard.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: requests |
| `portal/css/stylesheet.php` | PUBLIC | public | CSS built from the colour settings, as stylesheet.php. No data. |
| `portal/csv_export.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: facilities, user, requests, assigns |
| `portal/import_requests.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: insert requests; reads: user, requests; sends mail; accepts uploads |
| `portal/incs/exportcsv.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `portal/incs/portal.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `portal/markers/gen_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `portal/new_request.php` | LOGIN | gated | Was already gated by do_login(). reads: user, facilities |
| `portal/profile.php` | LOGIN | gated | Was already gated by do_login(). writes: update user; reads: user |
| `portal/request.php` | LOGIN | gated | Was already gated by do_login(). writes: update requests, update ticket; reads: user, facilities, ticket, requests, assigns |
| `portal/requests.php` | LOGIN | gated | Was already gated by do_login(). reads: user |
| `portal/up_status.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update requests |
| `print_screen.php` | LOGIN | gated | Was already gated by do_login().  |
| `quick_start.php` | LOGIN | open | Page, now shows the login form to anonymous visitors; also administrators only (destructive bulk operation) -- writes: update settings, insert in_types, insert unit_types, insert un_status, ...; reads: 1, responder, settings |
| `rc_redirect.php` | STATIC | n/a | Static page that posts a form to tables.php (which is gated); no data. |
| `read_pdf.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `rec_fac_t.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update ticket, update assigns |
| `reports.php` | LOGIN | gated | Was already gated by do_login(). reads: unit_types, log, responder, ticket, organisations, ... |
| `reports_print.php` | LOGIN | gated | Was already gated by do_login().  |
| `reset_regions.php` | LOGIN | open | Page, now shows the login form to anonymous visitors; also administrators only (destructive bulk operation) -- writes: drop allocates, create allocates, insert allocates; reads: ticket, responder, facilities, user |
| `reset_responder_status.php` | LOGIN | open | Page, now shows the login form to anonymous visitors; also administrators only (destructive bulk operation) -- writes: update responder; reads: un_status, responder |
| `resp_popup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: unit_types, assigns, un_types, ticket, un_status, ... |
| `revisable_fields.php` | LOGIN | gated | Was already gated by do_login(). writes: update defined_fields |
| `rm/ajax/advert.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: adverts |
| `rm/ajax/alert_detail.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: roadinfo |
| `rm/ajax/alertlist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: roadinfo |
| `rm/ajax/chat_invite.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete chat_invites, insert chat_invites; reads: chat_invites |
| `rm/ajax/chat_invite_del.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete chat_invites; reads: chat_invites |
| `rm/ajax/chat_rd.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete chat_messages; reads: chat_messages, user |
| `rm/ajax/chat_wl.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user |
| `rm/ajax/chat_wr.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert chat_messages |
| `rm/ajax/geo_loc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `rm/ajax/get_latest_ids.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: chat_invites, user, assigns, ticket |
| `rm/ajax/get_latest_messages.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `rm/ajax/get_recfac.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, facilities |
| `rm/ajax/infolist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: roadinfo, conditions |
| `rm/ajax/infolist2.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: roadinfo, conditions |
| `rm/ajax/message_detail.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `rm/ajax/message_detail2.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: messages |
| `rm/ajax/messagelist.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, messages |
| `rm/ajax/messagelist2.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: responder, messages |
| `rm/ajax/send_email.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert messages |
| `rm/ajax/submit_entry.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete roadinfo, insert roadinfo; reads: user, conditions, roadinfo |
| `rm/ajax/ticket_detail.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket, in_types, facilities, assigns |
| `rm/ajax/ticket_list.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, ticket, in_types |
| `rm/ajax/ticket_markers.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: assigns, ticket |
| `rm/ajax/update_assigns.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update assigns |
| `rm/ajax/update_message_read.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update messages; reads: user, messages |
| `rm/ajax/update_mileage.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update assigns |
| `rm/ajax/update_notes.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update ticket; reads: ticket |
| `rm/ajax/update_position.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: delete tracks, update responder, delete tracks_hh, insert tracks_hh, ...; reads: tracks, tracks_hh |
| `rm/ajax/update_recfac.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: update ticket |
| `rm/chat.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- writes: delete chat_messages; reads: chat_messages, codes, user |
| `rm/css/stylesheet.php` | PUBLIC | public | CSS built from the colour settings, as stylesheet.php. No data. |
| `rm/forms/mapindex.php` | OWN | public | Fragment of rm/index.php. Gated by require_login_or_401() whenever responder_mobile_forcelogin is on (the default), exactly like the page that includes it. |
| `rm/forms/nomapindex.php` | OWN | public | Fragment of rm/index.php. Gated by require_login_or_401() whenever responder_mobile_forcelogin is on (the default), exactly like the page that includes it. |
| `rm/images/gen_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `rm/images/rotate_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `rm/incs/mob_sockets.inc.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: user |
| `rm/incs/mobile_login.inc.php` | INCLUDE | n/a | Library include: defines functions/constants; produces no output of its own when requested directly. |
| `rm/index.php` | OWN | public | Mobile entry page. Default setting responder_mobile_forcelogin=1 shows the mobile login form before any data (do_mobile_login); an administrator may deliberately set it to 0. |
| `rm/pos_markers/rotate_icon.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `rm/unit_popup.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: un_status, assigns, ticket, responder |
| `road_conditions.php` | LOGIN | gated | Was already gated by do_login(). writes: delete roadinfo, update roadinfo, insert roadinfo; reads: conditions, roadinfo, database |
| `routes.php` | LOGIN | gated | Was already gated by do_login(). writes: insert assigns, delete assigns; reads: unit_types, responder, assigns, ticket, in_types, ... |
| `routes_i.php` | LOGIN | gated | Was already gated by do_login(). writes: insert assigns, delete assigns, update responder; reads: assigns, ticket, responder, un_status, tracks |
| `routes_nm.php` | LOGIN | gated | Was already gated by do_login(). writes: insert assigns, delete assigns, update responder; reads: unit_types, assigns, responder, ticket, in_types, ... |
| `rss/external_RSS.php` | GATED | open | RSS of incidents with caller-supplied text. Was anonymous, now needs a session. MAINTAINER DECISION 3 below. |
| `rss/external_XML.php` | GATED | open | XML dump of EVERY incident row (caller name, phone, address, description, comments), every unit and every facility. Was anonymous, now needs a session. MAINTAINER DECISION 3 below. |
| `rss/road_conditions.php` | PUBLIC | public | Road-conditions GeoRSS feed, public by design: road alerts only (title, text, place, time); no incident, unit, caller or user data. |
| `rss/view.php` | PUBLIC | public | Per-item page that the road-conditions feed links to; shows one road alert. Same data as the public feed. |
| `search.php` | LOGIN | gated | Was already gated by do_login(). reads: ticket, patient, action |
| `server_details.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `set_cook_exp.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `settings.inc.php` | INCLUDE | n/a | Variable definitions written by the installer or read by tables.php; prints nothing. |
| `sever_graph.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: ticket |
| `show_tables_and_indexes.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `single.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: ticket, in_types, facilities |
| `single_unit.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: responder, un_status, unit_types |
| `sit_screen_NM.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: assigns |
| `sitemap/gwsitemap.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `smtp_test.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- sends mail |
| `socketserver/deletefile.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `socketserver/portchecker.php` | CLI | open | Websocket relay helper or backup copy; run from the command line. |
| `socketserver/portchecker2.php` | CLI | open | Websocket relay helper or backup copy; run from the command line. |
| `socketserver/server.php` | PUBLIC | public | Starts the websocket relay when it is not running. The login pages call it before anyone is logged in; it returns nothing. MAINTAINER DECISION: the relay trusts a client-supplied user id. |
| `socketserver/server_BU.php` | CLI | open | Websocket relay helper or backup copy; run from the command line. |
| `socketserver/users.php` | INCLUDE | n/a | Websocket library classes; defines classes only. |
| `socketserver/websockets.php` | INCLUDE | n/a | Websocket library classes; defines classes only. |
| `stats_scr.php` | LOGIN | gated | Was already gated by do_login(). writes: update stats_settings, insert stats_settings; reads: stats_settings, stats_type |
| `street_view.php` | LOGIN | open | Page, now shows the login form to anonymous visitors |
| `stylesheet.php` | PUBLIC | public | CSS built from the colour settings. The login page needs it before anyone is logged in; no personal or operational data. |
| `t_tracker.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: remote_devices |
| `tables.php` | LOGIN | partly | Gate existed but ran after output or a query; moved to the top. Was already gated by do_login(). reads: unit_types, fac_types |
| `tables/c_conditions.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/c_courses_taken.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: courses, user |
| `tables/c_fac_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/c_in_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mailgroup |
| `tables/c_personnel.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/c_places.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: alter places |
| `tables/c_region.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: region_type, mmarkup |
| `tables/c_roadinfo.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: conditions |
| `tables/c_un_status.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/c_unit_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/u_conditions.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/u_fac_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/u_in_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mailgroup |
| `tables/u_personnel.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/u_places.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/u_region.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: region_type, mmarkup |
| `tables/u_roadinfo.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: conditions |
| `tables/u_un_status.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/u_unit_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/v_conditions.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/v_fac_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/v_in_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: mailgroup |
| `tables/v_personnel.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tables/v_roadinfo.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: conditions |
| `tables/v_unit_types.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `tb.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `tern1.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `tests/Unit/CodeHygieneTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/DatabaseLayerTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/EndpointAuthRuntimeTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/EndpointAuthScanner.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/EndpointAuthTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/IncNumSchemeTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/InstallerSafetyTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/NullFetchGuardTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/NullSafeHelpersTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/Php8CompatRegressionTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/SanitizeCoordinateTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/SecurityHelpersTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/SessionSecurityTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/SqlInjectionAuditTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/SqlInjectionFixesTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/SqlPlaceholderBindingTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/Unit/XssOutputTest.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/bootstrap.php` | INCLUDE | n/a | Test-suite file; defines classes only. |
| `tests/test_cert_integration.php` | CLI | open | Test script; command line only. |
| `tests/test_docker_deploy.php` | CLI | open | Test script; command line only. |
| `tests/test_password_formats.php` | CLI | open | Test script; command line only. |
| `tests/test_phpmailer_upgrade.php` | CLI | open | Test script; command line only. |
| `text.php` | STATIC | n/a | Static HTML (a Google Maps v2 test page); no PHP, no data. |
| `ticketsmdb_import.php` | LOGIN | gated | Was already gated by do_login(). writes: update member, update mdb_files, truncate table, truncate responder_x_member; reads: member, mdb_files, responder_x_member |
| `tile_proxy.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- makes outbound requests |
| `tools/clone_db.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/diagnose.php` | TOOL | open | Reports PHP, database and configuration details with no login. Off for the web unless the administrator creates tools/diagnose.enable; always works from the command line. |
| `tools/fix_affected_rows.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/fix_in_types_schema.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/fix_intval_parens.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/fix_null_fetch.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/reset-admin-password.php` | TOOL | open | Lists every administrator account and sets any password with no login: anyone could take over an install whose tools/ directory was served. Off for the web unless the administrator creates tools/reset-admin-password.enable (single use: a successful reset removes it); always works from the command line. |
| `tools/security_scan.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/semgrep_gate.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/semgrep_to_sonar.php` | CLI | open | Developer/maintenance script; command line only. |
| `tools/sqli_audit.php` | CLI | open | Developer/maintenance script; command line only. |
| `top.php` | PUBLIC | public | Persistent top frame shown above the login form. Renders a logged-out state (no menu buttons) for visitors; the unit-icon table and the user-name table (socket2me) it used to embed are now withheld until a session exists. |
| `track_u.php` | LOGIN | partly | Had its own session or administrator check that the rest of the application did not share; now uses the common gate. Page, now shows the login form to anonymous visitors -- reads: responder, tracks |
| `tracker.php` | PUBLIC | public | GPS device ingest (GPSGate, BT747, uTrack). Tracking devices cannot hold a session and send a device name only. MAINTAINER DECISION: anyone can write a position for any device name. |
| `tracks.php` | LOGIN | gated | Was already gated by do_login(). reads: unit_types, responder, tracks, un_status |
| `tracks_hh.php` | LOGIN | gated | Was already gated by do_login(). reads: unit_types, responder, tracks_hh, un_status |
| `unit_log_view.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: log, ticket, responder, un_status, user |
| `unit_popup.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: un_status, assigns, ticket, responder |
| `unit_ticket_log.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: ticket, allocates |
| `units.php` | LOGIN | gated | Was already gated by do_login(). writes: delete responder, update responder, insert allocates, delete allocates, ...; reads: unit_types, responder, database, facilities, allocates, ...; accepts uploads |
| `units_nm.php` | LOGIN | gated | Was already gated by do_login(). writes: delete responder, update responder, insert allocates, delete allocates, ...; reads: unit_types, assigns, ticket, un_status, responder, ...; accepts uploads |
| `wait.php` | PUBLIC | public | Static "wait for login" page; prints the time only. |
| `warn_locations.php` | LOGIN | gated | Was already gated by do_login(). writes: delete warnings, update warnings, insert warnings; reads: warnings, database |
| `warnloc_popup.php` | LOGIN | gated | Was already gated by do_login(). reads: warnings |
| `warnloc_popup_nm.php` | LOGIN | gated | Was already gated by do_login(). reads: warnings |
| `wastebasket.php` | LOGIN | gated | Was already gated by do_login().  |
| `wp1.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- makes outbound requests |
| `wp_lkup.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: constituents, ticket; makes outbound requests |
| `wp_lkup_cid.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- writes: insert caller_id; reads: constituents, ticket; makes outbound requests |
| `wp_lkup_rev.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers -- reads: constituents, ticket; makes outbound requests |
| `wr_invite.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `ws_monitor.php` | LOGIN | open | Page, now shows the login form to anonymous visitors -- reads: user |
| `wx.php` | GATED | open | Data/fragment endpoint, now returns 401 to anonymous callers |
| `wxalert.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `wxalert1.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
| `y.php` | CLI | open | Developer scratch script with no caller in the application; command line only. |
