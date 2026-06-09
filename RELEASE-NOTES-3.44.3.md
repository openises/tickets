# TicketsCAD v3.44.3 Release Notes

**Release Date:** June 8, 2026
**Priority:** Low — version-display fix only; no security or behaviour changes vs. 3.44.2

---

## Summary

Single-purpose patch release that corrects the **install-time version string** written by the Docker auto-installer.

Anyone running the `ghcr.io/openises/ticketscad:latest` container (or any release after v3.44.1 but before this one) has been seeing **"3.44.1"** in the Config screen even though the underlying code has been at v3.44.2 since April. The bug was that `docker-autoinstall.php` hardcoded the version string in its own SQL, and the string was never bumped when v3.44.2 shipped. The PHP source files in the container have been current the whole time; only the displayed version number was wrong.

**You are not missing any security fixes** if your container reported "3.44.1" — you already have the 88 v3.44.2 patches. This release just makes the version label honest.

Reported by **Benjamin Hatfield** (laconiacorp@gmail.com, 2026-06-08).

## What changed

| File | Change |
|---|---|
| `incs/versions.inc.php` | `TICKETS_CURRENT_VERSION` bumped from `v3.44.1` → `v3.44.3` |
| `docker-autoinstall.php` | No longer hardcodes the version string. Now reads `TICKETS_CURRENT_VERSION` from `incs/versions.inc.php` (the canonical source) and writes the bare semver (`3.44.3`) to the `settings._version` row. Prepared statements used instead of inline interpolation. Echoes the version it set so it's visible in container startup logs. |
| `RELEASE-NOTES-3.44.3.md` | This file. |

The structural improvement here matters more than the version bump: there's now ONE source of truth for the install version (`incs/versions.inc.php`). Future releases bump that one constant and the auto-installer picks it up automatically. The drift that caused this bug can't happen again.

## Upgrade path

### Docker users

```bash
docker compose pull
docker compose up -d
```

The CI workflow at `.github/workflows/docker-build.yml` automatically builds and pushes `ghcr.io/openises/ticketscad:3.44.3`, `:3.44`, and `:latest` on the `v3.44.3` tag.

After upgrade, new containers will write `3.44.3` to `settings._version` on first boot. **Existing containers** keep whatever value is already in the database — to update the displayed version on an in-place container without reinitializing the DB:

```sql
UPDATE settings SET value = '3.44.3' WHERE name = '_version';
```

### Traditional installs (non-Docker)

```bash
git pull
# Visit /install.php in your browser to run the upgrade flow
```

The legacy installer at `install.php` already reads from `TICKETS_CURRENT_VERSION` correctly, so it has always written the right value. This release does not change installer behaviour for non-Docker deployments.

## Verification

Check the version that's actually displayed:

```bash
# From inside the container (or anywhere with shell access on the host):
docker exec ticketscad grep TICKETS_CURRENT_VERSION /var/www/html/incs/versions.inc.php
# Should print:  define('TICKETS_CURRENT_VERSION', 'v3.44.3');

# What's in the database:
docker exec ticketscad_db mariadb -u tickets -ptickets tickets \
  -e "SELECT value FROM settings WHERE name='_version'"
# Should print:  3.44.3
```

## What did NOT change

- No security fixes (none needed since v3.44.2 covered the audit)
- No schema changes
- No new features
- No breaking changes
- No PHP / MariaDB / Apache compatibility changes

If your container was reporting "3.44.1" before this release, it had the v3.44.2 code and patches; you don't gain anything functional by upgrading. The only reason to upgrade is to have the Config screen and `settings._version` table show the truthful version.

---

## Recommendation

**Optional** for users who don't mind the misleading version label.

**Recommended** for anyone who audits their software inventory (so the displayed version matches what's actually running) and for fresh container deployments going forward.
