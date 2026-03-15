# TicketsCAD Administrator Guide

Version 3.44.0

This guide covers system administration, configuration, and maintenance for TicketsCAD.

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [User Management](#user-management)
3. [Configuration Settings](#configuration-settings)
4. [Incident Types](#incident-types)
5. [Unit Types and Capabilities](#unit-types-and-capabilities)
6. [Facility Management](#facility-management)
7. [Notification and Email Setup](#notification-and-email-setup)
8. [Map and Tile Configuration](#map-and-tile-configuration)
9. [Day/Night Color Schemes](#daynight-color-schemes)
10. [Module Management](#module-management)
11. [Database Tables](#database-tables)
12. [Backup and Recovery](#backup-and-recovery)
13. [Troubleshooting](#troubleshooting)

---

## System Architecture

### Technology Stack
- **Server:** PHP 8.0+ on Apache (XAMPP)
- **Database:** MySQL/MariaDB
- **Frontend:** HTML/JavaScript with Leaflet maps
- **Real-time:** AJAX polling (5-second default cycle)

### File Structure
```
tickets/
├── index.php          # Entry point, frameset loader
├── top.php            # Navigation toolbar frame
├── main.php           # Main dispatch frame
├── config.php         # Administration panel
├── install.php        # Initial setup / schema updates
├── incs/              # PHP include libraries
│   ├── functions.inc.php          # Core utility functions
│   ├── functions_major.inc.php    # Internet-mode display functions
│   ├── functions_major_nm.inc.php # Non-internet display functions
│   ├── login.inc.php              # Authentication logic
│   ├── db.inc.php                 # Database abstraction layer
│   └── mysql.inc.php              # DB credentials (not in repo)
├── ajax/              # AJAX endpoint handlers
├── portal/            # Public service request portal
├── rm/                # Mobile/remote interface
├── js/                # JavaScript libraries
├── emd_cards/         # SOP document storage
├── docs/              # Documentation
└── tests/             # PHPUnit test suite
```

### Internet vs. Non-Internet Mode
The system has two rendering paths controlled by the `internet` setting:
- **Internet mode** — Full mapping with online tile providers, geocoding
- **Non-internet mode** (`_nm` files) — Operates without external network dependencies

### Authentication Flow
1. `index.php` loads frameset with `top.php` and `main.php`
2. `main.php` calls `do_login()` which presents login form if no session
3. On successful login, session variables are set (`user_id`, `level`, `user`)
4. `top.php` detects session and activates navigation buttons
5. All module pages call `do_login()` to enforce authentication

### User Roles and Permission Levels
| Level | Name | Access |
|-------|------|--------|
| 9 | Super-Admin | Full access, can manage other admins |
| 8 | Admin | Full access to all modules and configuration |
| 5 | Member | Standard dispatch operations |
| 2 | Guest | Read-only access to Situation, Search, Help |
| 1 | Unit | Field unit with restricted navigation |

---

## User Management

Accessed via **Config** > User Accounts.

### Creating a User
1. Navigate to Configuration
2. Select the User management section
3. Enter username, password, and assign a role level
4. Configure notification preferences

### Password Policy
Passwords are stored as MD5 hashes in the `user` table. There is no built-in password complexity enforcement.

> **Security Note:** MD5 password hashing is considered insecure by modern standards. A migration to bcrypt or Argon2 is planned as part of Phase 4 security modernization.

---

## Configuration Settings

All system settings are stored in the `settings` database table as name-value pairs. Access via **Config** in the navigation bar.

### Key Settings

| Setting Name | Purpose | Default |
|-------------|---------|---------|
| `host` | Display hostname in title bar | www.yourdomain.com |
| `title_string` | Custom title bar text | (empty = default) |
| `internet` | Internet connectivity mode (0/1/3) | 3 |
| `auto_poll` | AJAX polling interval | 5000 (ms) |
| `delta_mins` | Timezone offset in minutes | 0 |
| `use_messaging` | Enable messaging module (0-3) | 1 |
| `chat_time` | Chat timeout (0 = disabled) | 0 |
| `call_board` | Call board display mode (0/1/2) | 0 |
| `use_mdb` | Enable personnel module | 0 |
| `serial_no_ap` | Ticket number display format | 0 |
| `locale` | Regional settings for form requirements | 0 |
| `framesize` | Top frame height in pixels | 50 |
| `frameborder` | Frame border width | 0 |
| `use_responder_mobile` | Auto-redirect mobile devices | 0 |
| `tile_mode` | Map tile source mode (direct/proxy) | proxy |

---

## Incident Types

Configure incident categories via **Config** > Incident Types.

Each type has:
- **Name** — Display label
- **Icon** — Map marker icon
- **Category** — Grouping for reports

Incident types appear in the dropdown when creating or editing incidents.

---

## Unit Types and Capabilities

### Unit Types
Configure via **Config** > Unit Types.

Each unit type defines:
- Type name (e.g., "Ambulance", "Fire Engine", "Police")
- Default icon for map display
- Dispatch rules

### Capabilities
Units can have assigned capabilities (equipment, skills) used for filtered dispatch. Configure capability types in the Config panel, then assign them to individual units.

### Dispatch Settings
Each unit has a `dispatch` flag:
- **0** — Normal dispatch allowed
- **2** — Dispatch not allowed (unit is administrative only)

The `multi` flag controls whether a unit can be assigned to multiple incidents simultaneously.

---

## Facility Management

Facilities represent fixed locations (stations, hospitals, shelters, etc.).

### Configuration
- **Name and address** — Display and geocoding
- **Type** — Category from configured facility types
- **Status** — Current operational status
- **GPS coordinates** — Map placement

### Facility Allocation
Facilities can be allocated to regions/groups for filtered display.

---

## Notification and Email Setup

### Email Configuration
See `README-Mail.txt` for SMTP setup details.

Key settings:
- SMTP server address and port
- Authentication credentials
- From address and name
- TLS/SSL configuration

### Notification Rules
Configure which events trigger email notifications:
- New incident created
- Incident status change
- Unit assignment
- Chat invitation
- Message received

### Mail Groups
Create groups of recipients for bulk notifications. Manage via Config > Mail Groups.

---

## Map and Tile Configuration

See `docs/TILE_SETTINGS.md` for detailed tile provider setup.

### Tile Modes
- **Direct** — Browser fetches tiles directly from tile provider
- **Proxy** — Server-side `tile_proxy.php` fetches and serves tiles (for offline/restricted networks)

### Map Settings
- Default center coordinates (lat/lng)
- Default zoom level
- Tile provider URL template
- Attribution text

---

## Day/Night Color Schemes

The system supports dual color schemes for day and night operations.

### Customization
Colors are stored in `css_day` and `css_night` database tables. Each table maps CSS class names to color values.

Editable via Config panel or direct database table editing.

---

## Module Management

Optional modules can be installed and removed:
- `install_module.php` — Install a module package
- `delete_module.php` — Remove an installed module

Module definitions are stored in the `modules` table.

---

## Database Tables

### Table Count
The system uses approximately 110+ database tables.

### Key Operational Tables
| Table | Purpose |
|-------|---------|
| `ticket` | Incident records |
| `responder` | Response units |
| `facilities` | Fixed locations |
| `assigns` | Unit-to-incident assignments |
| `action` | Incident action log entries |
| `patient` | Patient records (EMS) |
| `user` | User accounts |
| `settings` | System configuration |
| `in_types` | Incident type definitions |
| `unit_types` | Unit type definitions |
| `tracks` | GPS position history |

### Database Maintenance

> **Current State:** 58 of 110 tables use MyISAM engine (should be InnoDB). 104 tables use latin1 charset (should be utf8mb4). No foreign key constraints exist. These are tracked as Phase 3 modernization tasks.

### Direct Table Editing
The `tables.php` page provides direct CRUD access to all database tables. Use with caution — no referential integrity checks are enforced.

---

## Backup and Recovery

### Database Backup
Use `mysqldump` or your database management tool to create regular backups:

```bash
mysqldump -u [username] -p [database_name] > backup_$(date +%Y%m%d).sql
```

### File Backup
Back up these directories:
- `emd_cards/` — SOP documents
- `files/` — Uploaded attachments
- `pictures/` — Uploaded images
- `mdb_files/` — Personnel database files
- `incs/mysql.inc.php` — Database credentials

### Recovery
1. Restore the database from backup
2. Restore file directories
3. Verify `incs/mysql.inc.php` points to correct database
4. Run `install.php` to verify/update schema if needed

---

## Troubleshooting

### Common Issues

**Blank or blurry map on first load**
The map may need a moment to initialize after the page opens in a popup window. If tiles don't load, press F5 to refresh. This is a known timing issue with the Leaflet mapping library initializing before the popup window has its final dimensions.

**Session timeout during active use**
Check the session expiry setting in Configuration. The system tracks activity and expires idle sessions.

**Navigation buttons not appearing**
Buttons are role-dependent. Guest and Unit roles see a reduced set of navigation options. Verify the user's role level in User Management.

**Form submission errors**
If you see "Undefined array key" warnings when submitting forms, clear your browser cache and reload the page. Some form fields are conditionally rendered and may not be present in all states.

**Database connection errors**
Verify `incs/mysql.inc.php` contains correct database credentials. Check that MySQL/MariaDB service is running.

---

## Known Issues and Backlog

See `docs/BACKLOG.md` for the current development backlog.

### Patient Form
- Red asterisk required-field indicators may not match actual validation for Gender and Insurance fields (depends on `locale` setting)
- Patient ID field purpose: This is a user-assigned tracking identifier, not auto-generated. Used to distinguish multiple patients on multi-casualty incidents.

### Legacy Code
- Password hashing uses MD5 (migration planned)
- Some pages use legacy `mysql_*` function shims
- No foreign key constraints in database schema

---

*This document is maintained as part of the TicketsCAD modernization project.*
