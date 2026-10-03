# TicketsCAD v3.44.7 Release Notes (DRAFT)

**Release Date:** not yet released
**Priority:** High — three reports crash whenever the period contains data, and one bad setting can permanently destroy the incident numbering scheme. No schema changes.

> **Draft status.** This file covers only the report and incident-numbering fixes
> (GH #16 / PR #17 and GH #18 / PR #19, both contributed by Ron Jones, `rjonesbsink`).
> It does not describe the other commits on `main` since v3.44.6 (the September 2026
> security fixes), and `TICKETS_CURRENT_VERSION` in `incs/versions.inc.php` has **not**
> been bumped. Merge this text into the final notes when the release is cut.

---

## Summary

Bug fixes only. Every item was reproduced against a scratch database on PHP 8.2 (and the
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
