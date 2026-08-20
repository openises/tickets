# TicketsCAD v3.44.6 Release Notes

**Release Date:** August 20, 2026
**Priority:** High — fixes a hard fatal that has been blocking dispatch from the Call Board. No security or schema changes.

---

## Summary

Three bug fixes, none of them feature work — the v3 line only takes bug and
security changes. All three were reproduced against an unpatched control run
before being fixed.

### 1. Dispatching from the Call Board fatals — `Column 'user_id' cannot be null`

`board.php`'s `case 'add_db'` reads seven `$frm_*` variables and, until this
release, assigned none of them. They used to come from a file-scope
`extract($_POST)` that an earlier cleanup commit removed on the assumption
that only one variable was actually needed from it — but every case in that
switch depended on it. A follow-up commit restored explicit assignments for
two of the three affected cases and stopped short of the third, leaving
`add_db` reading names that no longer existed anywhere in the file.

`assigns`.`user_id` is `int NOT NULL` with no default, so binding a null value
raised MySQL error 1048 and the INSERT was rejected outright. **Dispatching a
unit from the Call Board has not worked since the original extract() cleanup
— this is a hard failure, not a degraded one.**

| | `assigns` table | Result |
|---|---|---|
| Before this release | 9 rows → still 9 | 7 PHP warnings + a fatal error; nothing written |
| After this release | 9 rows → 10 | Clean write; correct `user_id`, `ticket_id`, `responder_id`, `comments`, and all four mileage values |

### 2. Scheduled incidents silently lose their booked date

`add_nm.php` read `$frm_do_scheduled` from a variable that was assigned
nowhere in the file — and the read happens inside a function, so even the old
file-scope `extract()` couldn't have reached it. `intval(null)` evaluates to
`0`, so the check that decides whether to save the scheduled date always took
the "not scheduled" branch and silently discarded it.

This was not extract()-cleanup fallout like issue 1 — it reads as leftover
from PHP's old `register_globals` behavior, which stopped working years ago.
The equivalent medical-incident form already read this value correctly from
`$_POST`; this release brings the general-incident form in line with it.

| | `ticket`.`booked_date` |
|---|---|
| Before this release | `NULL` |
| After this release | Correctly saved, e.g. `2026-09-15 14:30:00` |

### 3. Notification "Links:" line was not a usable URL

The link included in SMS/email incident notifications
(`incs/functions.inc.php` and the identical logic in
`incs/functions_nm.inc.php`) rendered as `Links: HTTP//:8081?id=10` — not a
working URL — and could additionally raise an `Undefined array key` warning
on servers (IIS in particular) that don't populate `SERVER_ADDR`.

Three separate problems combined to produce this: the code was parsing
`SERVER_PROTOCOL` (`"HTTP/1.1"`) for a scheme, which yields the bare word
`HTTP` with no colon and can't indicate HTTPS either way; `SERVER_ADDR` isn't
populated on every server configuration and, where it is, holds the server's
own IP rather than an address the recipient can necessarily reach; and the
trailing path segment was stripped in a way that resolved to the install's
directory rather than a specific page, so the incident id was silently
dropped and the link opened a login screen instead of the incident.

The link now derives its scheme from whether the request was HTTPS, prefers
the host the user's browser actually sent, and names the target page
explicitly.

| | `Links:` line |
|---|---|
| Before this release | `HTTP//:8081?id=10`, plus a PHP warning on some servers |
| After this release | A working link, e.g. `http://localhost:8081/ticketscad/main.php?id=10` |

## What changed

| File | Change |
|---|---|
| `board.php` | Restored the missing `$frm_*` assignments in the `add_db` case, reading from `$_POST` with the project's existing `sanitize_int()`/`sanitize_string()` helpers; the query itself was already parameterized and is unchanged |
| `add_nm.php` | `$frm_do_scheduled` now correctly read from `$_POST`, matching the medical-incident form's existing behavior |
| `incs/functions.inc.php`, `incs/functions_nm.inc.php` | Notification link construction rewritten to derive scheme from HTTPS status and host from the browser's own request, with a same-behavior fallback path for requests with no browser context (CLI/cron) |
| `incs/versions.inc.php` | `TICKETS_CURRENT_VERSION` bumped `v3.44.5` → `v3.44.6` |
| `RELEASE-NOTES-3.44.6.md` | This file |

## Upgrade path

### Traditional installs (non-Docker)

```bash
git pull
# Then visit /install.php in your browser to run the upgrade flow.
```

Because the version constant moved to `v3.44.6`, the next admin login detects
the mismatch and redirects to `install.php`. There are **no schema changes**
in this release, so the upgrade simply records the new version and returns
you to the app.

### Docker users

```bash
docker compose pull
docker compose up -d
```

## Verification

```bash
# Version constant:
grep TICKETS_CURRENT_VERSION incs/versions.inc.php | head -1
# -> define('TICKETS_CURRENT_VERSION', 'v3.44.6');
```

Then: from the Call Board, dispatch a unit to an open incident and confirm it
completes without a fatal error. Separately, create a scheduled incident with
a future date/time and confirm the booked date is saved and appears correctly
on reopening it. If SMS/email notifications are configured, confirm the
"Links:" line in a notification resolves to the actual incident rather than a
login page.

## What did NOT change

- No security fixes
- No schema changes
- No new features
- No breaking changes
- No PHP / MariaDB / Apache compatibility changes

## Recommendation

**Recommended for everyone.** Issue 1 is a hard fatal blocking a core dispatch
action for any install that has applied the earlier `extract()` cleanup
(which is to say, any current install) — this is not an edge case.
