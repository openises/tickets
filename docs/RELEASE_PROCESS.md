# Release Process & Versioning

This document covers how versioning works in TicketsCAD, what developers must
do when a release includes database changes, and the steps for building and
publishing a release.

---

## How Versioning Works

TicketsCAD tracks two version values that must stay in sync:

| Value | Where it lives | Purpose |
|-------|---------------|---------|
| **Installer version** | `incs/versions.inc.php` constant `TICKETS_CURRENT_VERSION` | The version the code expects |
| **Installed version** | `settings` table, row `_version` | The version the database was last upgraded to |

On every admin login, `login.inc.php` compares these two values. If they
differ, the admin is redirected to `install.php` to run the upgrade. Non-admin
users are not redirected.

The comparison is a strict string match (`!==`), so the format must be
identical (e.g., `v3.44.0` vs `v3.44.0`).

### Version format

Use semantic versioning prefixed with `v`:

    v<major>.<minor>.<patch>

Examples: `v3.44.0`, `v3.45.0`, `v4.0.0`

---

## When a Release Includes Database Changes

If your release adds, removes, or modifies tables, columns, indexes, or seed
data, you must do **all** of the following:

### 1. Bump the version constant

Edit `incs/versions.inc.php` and update the version:

```php
define('TICKETS_CURRENT_VERSION', 'v3.45.0');
$tickets_current_version = TICKETS_CURRENT_VERSION;
```

Both lines must reflect the same value. The `define()` is the authoritative
source; the variable exists for backward compatibility.

### 2. Add upgrade SQL to the installer

In `install.php`, the step-based upgrade flow handles schema changes. Add your
migration SQL to the appropriate step or create a new step if needed. The
`version` step (case `'version'`) records the new version in the `settings`
table after all schema changes are applied.

If you are adding new settings rows, use `ensure_setting()`:

```php
case 'version':
    // ... existing version update ...
    ensure_setting($mysqli, $cfg['prefix'], 'my_new_setting', 'default_value');
    break;
```

### 3. Update the schema seed arrays

If you added new tables or columns, update the `$INSTALL_SCHEMA_CREATE` and
`$INSTALL_SCHEMA_SEED` arrays in `install.php` so that fresh installs get the
complete schema.

### 4. Test both upgrade and fresh install paths

- **Upgrade**: Start with the previous version's database, log in as admin,
  confirm you are redirected to `install.php`, run the upgrade, verify the
  `_version` setting is updated.
- **Fresh install**: Delete `incs/mysql.inc.php`, navigate to the app, confirm
  `install.php` runs and creates a working database.

---

## When a Release Has No Database Changes

If your release is code-only (bug fixes, UI changes, etc.) with no schema or
seed data modifications:

- **Do NOT bump the version.** The version constant should only change when
  the database schema changes. This avoids unnecessary installer redirects.
- If you need to distinguish code releases, use git tags.

---

## Release Checklist

### Before release

- [ ] All changes committed and pushed
- [ ] Run the full Selenium test suite: `run_tests.bat full`
- [ ] Manually verify any UI changes in supported browsers
- [ ] If database changes: version bumped in `versions.inc.php`
- [ ] If database changes: upgrade SQL added to `install.php`
- [ ] If database changes: schema seed arrays updated for fresh installs
- [ ] If database changes: tested both upgrade and fresh install paths
- [ ] `KNOWN_ISSUES.md` updated if any issues remain open
- [ ] `BACKLOG.md` updated to reflect completed items

### Building the release

1. Create a git tag matching the version:
   ```
   git tag v3.45.0
   git push origin v3.45.0
   ```

2. If distributing a zip/tarball, exclude development files:
   - `docs/` (optional, depending on audience)
   - `.phpunit.result.cache`
   - `mdb_pictures/` test data
   - Any `.env` or local config files

### After release

- [ ] Verify the download/update works on a clean test environment
- [ ] Confirm admin login triggers installer redirect (if version bumped)
- [ ] Confirm installer completes and records correct `_version`
- [ ] Confirm subsequent logins go directly to the app (no redirect loop)

---

## Key Files

| File | Role |
|------|------|
| `incs/versions.inc.php` | Defines `TICKETS_CURRENT_VERSION` constant and `tickets_get_versions()` function |
| `install.php` | Handles fresh install, upgrade, and version recording |
| `incs/login.inc.php` | Compares installer vs installed version on admin login |
| `index.php` | Redirects to `install.php` if no database is configured |

---

## Common Pitfalls

- **Forgetting to bump the version** when adding schema changes means admins
  won't be prompted to run the upgrade, and the new columns/tables won't exist.

- **Bumping the version without schema changes** causes unnecessary redirects
  to the installer on every admin login until they run through install.php.

- **Mismatched version strings** (e.g., `v3.44.0` in code vs `3.44.0` in the
  database) will cause a permanent redirect loop. Always include the `v` prefix.

- **Using `$GLOBALS` for the version** inside functions is unreliable due to
  PHP scope rules with `require_once`. Always use the `TICKETS_CURRENT_VERSION`
  constant.
