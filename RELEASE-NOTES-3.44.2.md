# TicketsCAD v3.44.2 Release Notes

**Release Date:** April 2, 2026
**Priority:** Critical Security Update — All users should upgrade immediately

---

## Security Fixes

This release addresses **88 security vulnerabilities** discovered through manual code review and automated scanning with SonarQube.

### Cross-Site Scripting (XSS) — 69 fixes across 22 files

All reflected XSS vulnerabilities have been patched. User-supplied input from GET and POST parameters is now properly escaped before rendering in HTML.

**Files patched:** single_unit.php, single.php, add_note.php, patient_JF.php, opena.php, add_facnote.php, street_view.php, routes_nm.php, do_unit_mail.php, search.php, patient.php, patient_w.php, routes_i.php, delete_module.php, ticketsmdb_import.php, os_watch.php, db_loader.php, add.php, add_nm.php, circle.php, landb.php, icons/buttons/landb.php

### SQL Injection — 19 fixes across 11 files

All SQL injection vulnerabilities have been patched. User input is now sanitized with `intval()`, `floatval()`, prepared statements, or identifier whitelisting as appropriate.

**Critical fixes:**
- `tables.php` — Table name, column names, and search terms from POST data were injected directly into SQL
- `ajax/fullsit_incidents.php`, `ajax/sit_incidents.php` — Raw `$_GET[offset]` in LIMIT clause
- `portal/ajax/list_requests.php` — `$_GET[sort]` and `$_GET[dir]` in ORDER BY clause
- `incs/remotes.inc.php` — External GPS API data used directly in SQL statements

### Hardcoded Secrets Removed — 5 files

API keys and database passwords that were hardcoded in source files have been replaced with environment variable lookups.

- `loader.php` — Hardcoded MySQL password removed
- `settings.inc.php`, `tables.php` — Google Maps API key removed
- `wp1.php` — WhitePages API key removed
- `import_mdb.php` — Database credentials externalized

**Action required:** If you were using these hardcoded keys, set the corresponding environment variables (`TICKETS_MAPS_API_KEY`, `TICKETS_WHITEPAGES_API_KEY`) or configure them in the admin panel.

### SSL/TLS Security — 4 files

SSL certificate verification is now **enabled by default** on all outbound HTTPS connections. Previously, `CURLOPT_SSL_VERIFYPEER` was set to `false` in several locations.

To disable verification (not recommended): set `verify_ssl` to `0` in the settings table.

### File Permissions — 6 files

All `mkdir()` and `chmod()` calls have been tightened from `0777` to `0755`.

---

## PHP Compatibility

### Supports PHP 7.0 through 8.4+

A new **compatibility layer** (`incs/compat.inc.php`) automatically polyfills functions removed in newer PHP versions:

| PHP Version | Issue | Fix |
|-------------|-------|-----|
| 8.2+ | `utf8_encode()` / `utf8_decode()` removed | Polyfilled via `mb_convert_encoding()` |
| 8.0+ | `each()` removed | Polyfilled |
| 8.0+ | `create_function()` removed | Polyfilled |
| 8.1+ | `FILTER_SANITIZE_STRING` removed | Mapped to `FILTER_DEFAULT` |
| 8.1+ | `strftime()` deprecated | Deprecation suppressed |
| 8.0+ | Null parameter deprecations | Suppressed |

**If you were getting 500 errors or white screens on PHP 8.2+, this update fixes them.**

---

## Password Compatibility

### All Legacy Password Formats Supported

The login system now recognizes **6 password hash formats** from any previous version of TicketsCAD:

1. **bcrypt** (`$2y$...`) — current standard
2. **MD5** (32-char hex) — versions 3.0–3.40
3. **MD5 case-insensitive** — some 3.x variants
4. **MySQL PASSWORD()** (`*SHA1(SHA1())`) — very old versions
5. **SHA1** (40-char hex) — some custom installs
6. **Plain text** — misconfigured installs

All legacy passwords are automatically upgraded to bcrypt on the next successful login. **No password resets are required after upgrading.**

---

## Map Tile Improvements

### OSM "Access Blocked" Fix

OpenStreetMap tile servers now require a Referer header. This release:
- Upgrades all tile URLs from `http://` to `https://`
- Adds `referrerPolicy: 'origin'` to Leaflet tile layers (for direct browser loading)
- Adds Referer header to the server-side tile proxy
- Docker deployments default to proxy mode (avoids browser Referer issues entirely)

---

## Docker Deployment (New)

TicketsCAD can now be deployed with Docker in 3 commands:

```bash
curl -LO https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml
docker compose up -d
# Open http://localhost:8080 — Login: admin / admin
```

Features:
- **Auto-install** on first run (creates tables, seeds data, provisions admin)
- **Persistent volumes** for database, uploads, tile cache, and config
- **PHP 8.2** with all required extensions
- **MariaDB 10.11** with health checks
- Configurable via environment variables (admin password, DB credentials, ports)

See the [Docker Deployment Guide](https://github.com/openises/tickets/wiki/Docker-Deployment) for full instructions.

---

## Installer Improvements

- Fixed `admin_user`/`admin_pass` undefined warnings during upgrade mode
- Added version mismatch detection with clear upgrade prompt
- Better error messages when database connection fails
- Supports both fresh install and upgrade from any previous version

---

## New Tools

- `tools/reset-admin-password.php` — Emergency password reset (handles all DB schema versions)
- `tools/diagnose.php` — Remote diagnostic tool (checks PHP, DB, tables, permissions)
- `tests/test_docker_deploy.php` — Deployment verification test suite
- `tests/docker-matrix-test.sh` — Multi-version compatibility test matrix

---

## Upgrade Instructions

### Docker
```bash
docker compose pull && docker compose up -d
```

### Traditional (XAMPP / Apache)
1. Backup your database: `mysqldump -u tickets -p tickets > backup.sql`
2. Download from https://github.com/openises/tickets/releases/tag/v3.44.2
3. Extract over your existing installation (preserve `incs/mysql.inc.php`)
4. Open `http://your-server/tickets/install.php` and run Upgrade
5. Done

### From Very Old Versions (3.30 and earlier)
Your existing passwords will work automatically — no reset needed. See the [Upgrade Guide](https://github.com/openises/tickets/wiki/Upgrade-Guide) for details.

---

## Contributors

- Security vulnerability report and testing by an independent researcher
- PR #7 installer improvements by community contributor
- PHP compatibility testing across 5 version combinations

---

## Recommendation

**All users should upgrade to v3.44.2 immediately.** This release contains critical security fixes for XSS and SQL injection vulnerabilities. The PHP compatibility layer also fixes 500 errors for users on PHP 8.2+.
