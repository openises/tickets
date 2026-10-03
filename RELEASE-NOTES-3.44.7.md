# TicketsCAD v3.44.7 Release Notes (DRAFT)

**Release Date:** not yet released
**Priority:** High — **security**: until now almost every script the pages call behind the scenes answered requests from people who were not logged in (see "Security" below); three reports crash whenever the period contains data; one bad setting can permanently destroy the incident numbering scheme. No schema changes.

> **Draft status.** This file covers the endpoint-authentication change and the report and
> incident-numbering fixes (GH #16 / PR #17 and GH #18 / PR #19, both contributed by Ron Jones,
> `rjonesbsink`).
> It does not describe the other commits on `main` since v3.44.6 (the September 2026
> security fixes), and `TICKETS_CURRENT_VERSION` in `incs/versions.inc.php` has **not**
> been bumped. Merge this text into the final notes when the release is cut.

---

## Security: scripts that did not ask who was calling

**What was wrong.** TicketsCAD protects its *pages* with a login check (`do_login()`), but the
scripts those pages call in the background -- the AJAX lists and popups, the report downloads, the
status and assignment updaters -- mostly did not check anything. A request with no session at all
was answered. In the tree as released in 3.44.6, **about 480 of the 697 PHP files answered a request that carried no session**
(452 data and page scripts with no check at all, 31 command-line tools, and 14 more whose check ran late or covered only
part of the script). Examples, each reproduced against a clean install by requesting it with no cookie:

- `ajax/reports.php` returned incident contact names, phone numbers, addresses, descriptions and
  action logs. It went unnoticed because `download_report.php` fetched it from the server to
  itself *without* cookies, which only worked while it asked for no login.
- `external.php` and `rss/external_XML.php` returned every unit and facility with all their contact
  columns (the second one also every incident row, closed ones too).
- State-changing scripts ran for anyone: `assign_del.php` (deleted an assignment),
  `as_up_un_status.php` (changed a unit's status), `chat_wr.php`, `do_send.php` (handed any recipient and
  text to the mail routine), `reset_regions.php` and `class_init.php` (dropped and rebuilt tables).
- `tools/reset-admin-password.php` listed every administrator and let a visitor set any password.
- `docker-autoinstall.php` (creates the schema and an `admin`/`admin` account) was an ordinary web-reachable
  file even though only the Docker entrypoint ever needs it; `top.php` embedded every user name for visitors when
  broadcast messaging was on.

**What changed.** Every PHP file now falls into exactly one class, and a test fails if a new file does not:

| Class | Behaviour |
|---|---|
| **Pages** | Call `do_login()` first: a visitor gets the login form. |
| **Data endpoints** (AJAX lists, fragments, images, downloads, status updaters) | Call the new `require_login_or_401()` first: a visitor gets `HTTP 401` and the one line `Authentication required.` No data is read, nothing is written, no session is created. |
| **Deliberately public** (16 files) | The login pages and their stylesheet, the "request login details" form and its confirmation link, the road-conditions RSS feed and its viewer, the GPS-device ingest `tracker.php`, the installer (own gate), and the script that starts the websocket relay. Each has its reason written down. |
| **Command line only** (29 files) | `tools/*`, the test scripts, `docker-autoinstall.php` and a few dead scratch scripts: a web request gets 404. |
| **Support tools** (2 files) | `tools/reset-admin-password.php` and `tools/diagnose.php` stay usable from a browser, because the guide tells you to run them when you cannot log in -- but only while you have created an empty file next to them (`reset-admin-password.enable` / `diagnose.enable`). The reset tool deletes its file again after a successful reset. |

"Logged in" means the session names a user **and** that user's `expires` time is still in the future,
exactly the test `do_login()` always applied to pages. Any account passes (guest, unit, portal
requester, operator, administrator); there is still no per-record access control. A page or screen
that polls these scripts (the Situation screen, full-screen displays, the mobile page) keeps its login
alive: a successful request pushes `expires` forward once less than half of `session_timeout` is left.
Without that, an unattended wall display would have stopped updating after an hour.

Six bulk tools now also require an administrator, because "any logged-in user" included the stock
`guest` account: `class_init.php`, `reset_regions.php`, `cleanse_regions.php`, `reset_responder_status.php`,
`quick_start.php`, and `loader.php`.

`download_report.php` and `download_report_csv.php` no longer fetch the report over HTTP from the server to itself;
they run `ajax/reports.php` inside their own request, so the logged-in caller's session authorizes the data (and no
self-request can hang on a single-threaded server or leak the web-server login). `top.php`, the frame above the login
form, no longer embeds the user-name and unit tables for visitors.

**What this might break -- check these.**

- **Anything of yours that called one of these scripts without logging in** (a cron `curl`, a wall display on a
  kiosk browser that never logged in, a dashboard that reads `external.php`, a monitoring probe of `ajax/...`) now gets
  401 or the login form. Give it a login (a normal session cookie works), or tell us which script it needs and why.
  The GPS-device script `tracker.php`, the road-conditions feed and the login pages are unchanged.
- **A browser tab left open past `session_timeout` without ever being used** used to keep refreshing from the server
  anonymously; it now stops, like any other expired login. Using the application (or the tab polling, which now
  counts) keeps it alive.
- If you used the emergency tools from a browser, create the `.enable` file first (see the table above and
  *Part 8* of the Installation and Administration Guide).

**Not fixed here, deliberately.** `tracker.php` accepts a position for any device name without a secret (devices cannot
hold a session). The websocket relay trusts a user id the client sends. See
`docs/security/legacy-endpoint-auth-inventory.md` -- it lists every PHP file with its class, whether it was open in 3.44.6,
and the reasoning, and it is the list the test checks.
## Summary

Bug fixes (the section above is the one behaviour change). Every item was reproduced against a scratch database on PHP 8.2 (and the
report matrix re-run on PHP 7.4 and 8.0) before being fixed.

### 1. Incident Report, Incident Summary and Station Log hang (GH #16)

`ajax/reports.php` passed `MYSQL_ASSOC` to four `stripslashes_deep()` calls. That constant
belonged to the `ext/mysql` extension, removed in PHP 7, so on any current PHP it is a fatal
"Undefined constant" error. The fatal sits inside the "this period has incidents" branch, which
is why a period with no data looked fine and the bug hid for years. `ajax/` endpoints run with
`display_errors` off, so the browser received an empty HTTP 500 and the loading spinner never
cleared.

| Report / period | Before | After |
|---|---|---|
| Incident Report, Incident Summary, Station Log; any period with data | Fatal, empty HTTP 500, spinner forever | Renders |
| All 11 report types x 4 periods (44 renders) | 12 fail | 0 fail, nothing in the PHP error log |

Three related defects in the same code are fixed with it:

- Filtering either incident report to a single incident read `$_Get['tick_sel']` (a mis-cased
  superglobal, always undefined), producing `AND ticket_id = ` and a SQL syntax error. The
  reports now use the sanitized `$tick_sel`, and so does the Dispatch report, which read
  `$_GET` directly and logged "Undefined array key" when a parameter was missing.
- The Code column of the incident log (and so of the After-action report) rendered blank and
  logged four warnings per row: the log-code names are built by `log_codes.inc.php` at file scope
  and `show_log()` never declared the variable global. The same latent defect is fixed in
  `show_unit_log()`, `log_details()` (mobile) and `my_show_log()` (Log page).
- `parsedate()` (four copies) used `%` on a floating-point average, which PHP 8.1+ deprecates.
  Only the operands of `%` are now cast, so the rendered text is byte-identical to before.

### 2. A bad `_inc_num` setting destroys the incident numbering scheme (GH #18)

The incident numbering scheme is stored in the `_inc_num` setting. Five places read it without
checking it, and `add.php` wrote its copy back after every incident. A value that does not
unserialize (empty, truncated, hand-edited) reads as `false`, and `serialize(false)` is itself a
valid stored value, so one bad read replaced the operator's scheme for good: every later incident
lost its serial number and the setting could only be repaired by hand. `add_nm.php` was worse: it
never decoded the base64 storage form at all, so it destroyed the scheme on **every** use.

| Stored `_inc_num` | Before | After |
|---|---|---|
| A valid scheme (labelled, year with rollover, pre-2011 plain serialized) | Counts up | Unchanged (same text, same counter) |
| `''` (empty), `MA==`, `YjowOw==`, or a row that is missing | PHP warnings, overwritten with `YjowOw==` | No warnings, no serial numbers, **stored value left untouched** |
| A scheme whose style is not 0-3 | Fatal inside `add.php`, incident left without a name | Treated as unreadable, as above |

What changed in behaviour:

- A fallback is never written back. `incs/inc_num.inc.php` reads the stored value defensively and
  reports whether it was a real scheme; only a real scheme whose counter actually moved is saved.
- `add_nm.php` now stores the scheme in the same base64 form as everything else (it stored the
  plain form, which the Configuration page could not read back).
- **Configuration > Incident Numbers** was a fifth unchecked read. It now shows the default and
  says "The stored numbering scheme could not be read" when the stored value is unusable, so
  choosing a style and saving repairs it.
- **Configuration > Reset Database** with "Reset settings" ticked re-seeded `_inc_num` with an
  empty string, which no reader could parse. It now seeds a valid default.
- A stored object is never instantiated, and a scheme containing an object or a nested array is
  treated as unreadable.

An install that was already hit (its `_inc_num` reads `YjowOw==`) is not repaired automatically:
open **Configuration > Incident Numbers**, choose the style you want and save.

### 3. Internal: a regression that never shipped

Unmodified `main` after the September 4 injection sweep fatals on the New Incident form, the Edit
Incident form, Configuration, `main.php` and `full_scr.php`: `get_sess_boundaries()` in
`incs/status_cats.inc.php` ran three of its four queries without their bound parameters. It is
fixed, with a static test for the whole class (a `?` fragment executed without its parameters). It
was never in a tagged release; v3.44.6 is unaffected.

## What changed

| File | Change |
|---|---|
| `incs/login.inc.php` | New `require_login_or_401()` (the gate for data endpoints) |
| ~465 PHP files (every endpoint that had no gate) | One line after the `functions.inc.php` include: `do_login(basename(__FILE__));` for pages, `require_login_or_401();` for data endpoints; fragments under `forms/` and `tables/` have it as their first statement |
| `download_report.php`, `download_report_csv.php`, `ajax/reports.php`, `incs/report_inline.inc.php` (new) | Reports run inside the download request instead of an HTTP self-fetch |
| `top.php` | No unit table or user names for visitors |
| `tools/*.php`, `tests/test_*.php`, `docker-autoinstall.php`, ten scratch scripts | First statement ends a web request with 404; `tools/diagnose.php` and `tools/reset-admin-password.php` open to the web only with a `.enable` file |
| `class_init.php`, `reset_regions.php`, `cleanse_regions.php`, `reset_responder_status.php`, `quick_start.php`, `loader.php` | Administrators only |
| `ajax/delfile.php`, `ajax/gettiles.php`, `landb.php`, `mmarkup.php`, `tables.php`, `log.php`, `edit_nm.php`, `routes.php`, `routes_nm.php`, `tile_proxy.php` | Existing login check moved ahead of the first output or query |
| `tools/semgrep_baseline.txt` | Line numbers renumbered for the files above (no entry added) |
| `docs/security/legacy-endpoint-auth-inventory.md`, `tests/Unit/EndpointAuthTest.php`, `EndpointAuthScanner.php`, `EndpointAuthRuntimeTest.php` | The inventory of all PHP files and the tests that enforce it |
| `ajax/reports.php` | Dropped the four `MYSQL_ASSOC` arguments; `$_Get['tick_sel']` replaced by the sanitized `$tick_sel`; Dispatch report uses `$tick_sel` / `$resp_sel`; `parsedate()` casts only its `%` operands |
| `ajax/basic_statistics.php`, `ajax/statistics.php`, `stats_scr.php` | `parsedate()` casts only its `%` operands |
| `incs/functions.inc.php`, `incs/functions_nm.inc.php`, `ajax/mobile_main.php`, `log.php` | `global $types` where `log_codes.inc.php` is loaded inside a function |
| `incs/inc_num.inc.php` | New: pure helpers `inc_num_default()`, `inc_num_parse()`, `inc_num_encode()`, `inc_num_advance()` |
| `incs/functions.inc.php` | `get_inc_num_ary(&$status)` reads the scheme through the helper |
| `add.php`, `add_nm.php` | Counter advanced with `inc_num_advance()`; written back only when it says so; `add_nm.php` writes base64 |
| `portal/ajax/insert_ticket.php`, `portal/ajax/insert_ticket_tentative.php` | Read the scheme through `get_inc_num_ary()` |
| `config.php`, `incs/config.inc.php` | Incident Numbers page reads through the helper and warns when unreadable; Reset settings seeds a valid scheme |
| `incs/status_cats.inc.php` | `get_sess_boundaries()` passes `$where2Params` to all four queries |
| `tests/Unit/Php8CompatRegressionTest.php`, `IncNumSchemeTest.php`, `SqlPlaceholderBindingTest.php` | New regression tests (no database) |

## Upgrade path

No schema changes and no new settings. Update the files as usual (`git pull`, or
`docker compose pull && docker compose up -d`). Nothing needs to be run.

## Verification

- Security: open `https://your-site/ajax/check_memory.php` in a private window (not logged in). Before: server
  memory and disk figures. Now: `Authentication required.` (HTTP 401). Log in, reload it in the normal window: the
  figures. `https://your-site/ajax/reports.php?report=l&tick_sel=1` must also answer 401 when logged out.
- Reports > any report > Download (Word, Excel, CSV) still delivers a file when logged in.
- The login screen still shows its styling, the Contact form still opens from it, and the Situation screen keeps
  updating for as long as you leave it open.
- Reports > Incident Report, pick the current year (any period that has incidents): the table
  renders instead of the spinner running forever. Repeat for Incident Summary and Station Log.
- Open an incident's log (or the After-action report): the **Code** column shows
  "Sign in", "Incident opened", "Unit dispatched", ... instead of blank cells.
- Configuration > Incident Numbers: with a numbering scheme in use, create two incidents and
  confirm the serial advances by one each time.

## What did NOT change

- No schema changes
- No new settings or features
- Incident serial numbers for installs with a working scheme are generated exactly as before
- The "0 Days:" prefix that the two statistics pages print for fractional averages is unchanged
  (a separate cosmetic quirk, left as it was)

## Recommendation

**Recommended for everyone on PHP 7 or later.** Issue 1 is a hard failure of three reports; issue 2
is silent, permanent data loss triggered by a routine setting value.
