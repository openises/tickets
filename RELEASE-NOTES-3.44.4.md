# TicketsCAD v3.44.4 Release Notes

**Release Date:** July 16, 2026
**Priority:** Medium — fixes a fatal 500 error when adding a unit in local-map (no-internet) mode. No security or schema changes.

---

## Summary

Single-purpose bug-fix release. On installs running in **local / no-internet
map mode**, submitting the **Add Unit** form failed with an HTTP 500 and this
fatal in the Apache log:

```
PHP Fatal error: Cannot redeclare get_roster()
  (previously declared in units_nm.php:62)
  in incs/functions.inc.php on line 5333
```

Two helper functions — `get_roster()` and `get_user_details()` — were defined
**twice**: once in `units_nm.php` (the no-internet units screen) and once in the
shared `incs/functions.inc.php`. `units_nm.php` already loads
`functions.inc.php` at the top, so PHP 8 saw the second definition and stopped
with a fatal "Cannot redeclare" error. It is a leftover from when those two
functions were consolidated into the shared file — the copies in `units_nm.php`
should have been removed at the same time.

The online (internet-map) `units.php` screen was never affected; it carries no
local copies of these functions and uses the shared ones.

Reported by **Gerald "Jay" Hurt** (2026-07-16), seen on both a Linux Mint /
PHP 8.3 box and a Raspberry Pi 5 / PHP 8.4 box.

## What changed

| File | Change |
|---|---|
| `units_nm.php` | Removed the duplicate local `get_roster()` and `get_user_details()` definitions. The screen now uses the single shared copies in `incs/functions.inc.php` (which it already loads). |
| `incs/versions.inc.php` | `TICKETS_CURRENT_VERSION` bumped `v3.44.3` → `v3.44.4` so existing installs are alerted that an update is available. |
| `RELEASE-NOTES-3.44.4.md` | This file. |

## Upgrade path

### Traditional installs (non-Docker)

```bash
git pull
# Then visit /install.php in your browser to run the upgrade flow.
```

Because the version constant moved to `v3.44.4`, the next admin login detects
the mismatch and redirects to `install.php`. There are **no schema changes** in
this release, so the upgrade simply records the new version and returns you to
the app.

### Docker users

```bash
docker compose pull
docker compose up -d
```

### Manual one-file patch (if you only want the fix, not a full update)

Edit `units_nm.php` and delete the two function definitions near the top of the
file — `get_roster()` and `get_user_details()`, roughly lines 62–91, everything
from `function get_roster($current=null) {` down to the closing brace of
`get_user_details()`, stopping just before the `?>` line. Leave the `?>` in
place. Both functions remain defined in `incs/functions.inc.php`, which
`units_nm.php` loads, so the screen keeps working.

## Verification

```bash
# The duplicate PHP definitions should be gone from units_nm.php:
grep -c 'function get_roster(' units_nm.php        # -> 0
# And still present (once) in the shared file:
grep -c 'function get_roster(' incs/functions.inc.php   # -> 1
# Version constant:
grep TICKETS_CURRENT_VERSION incs/versions.inc.php | head -1
# -> define('TICKETS_CURRENT_VERSION', 'v3.44.4');
```

Then, in local-map mode, open **Units → Add** and submit — the unit saves
instead of returning a 500.

## What did NOT change

- No security fixes
- No schema changes
- No new features
- No breaking changes
- No PHP / MariaDB / Apache compatibility changes

## Recommendation

**Recommended** for anyone running in local / no-internet map mode (the Add Unit
form is broken without it). **Optional** for internet-map installs, which were
not affected — but harmless to apply, and keeps your version current.
