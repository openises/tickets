# TicketsCAD Installation and Administration Guide

**Version:** 2.0 -- Covers Legacy v3.44 and NewUI v4.0
**Date:** April 2026
**Replaces:** TicketsCAD Installation Manual (2011, v2.20a)

---

## Table of Contents

- [Part 1: Introduction](#part-1-introduction)
- [Part 2: Installation](#part-2-installation)
- [Part 3: Initial Configuration](#part-3-initial-configuration)
- [Part 4: Setting Up Your Organization](#part-4-setting-up-your-organization)
- [Part 5: User Management](#part-5-user-management)
- [Part 6: Communications Setup](#part-6-communications-setup)
- [Part 7: Map Configuration](#part-7-map-configuration)
- [Part 8: Maintenance](#part-8-maintenance)
- [Part 9: Troubleshooting](#part-9-troubleshooting)
- [Appendix A: Settings Reference](#appendix-a-settings-reference)
- [Appendix B: Environment Variables (Docker)](#appendix-b-environment-variables-docker)
- [Appendix C: Security Checklist](#appendix-c-security-checklist)

---

## Part 1: Introduction

### What Is TicketsCAD

TicketsCAD is a free, open-source Computer-Aided Dispatch (CAD) system for managing emergency incidents, tracking responder units, and coordinating resources. It runs in a web browser and requires only a PHP/MySQL server -- no special hardware or commercial licenses.

The project has been under active development for over 30 years. It started as a simple call-logging tool and has grown into a full-featured dispatch platform with mapping, messaging, scheduling, and reporting capabilities.

### Who Uses TicketsCAD

TicketsCAD is designed for organizations that need dispatch capability but cannot justify the cost of commercial CAD software:

- **Volunteer fire departments** -- Track apparatus, manage incidents, record patient data
- **ARES/RACES amateur radio groups** -- Coordinate net traffic, manage welfare checks, track field operators
- **CERT teams** -- Log damage assessments, assign search areas, manage volunteers
- **Small EMS agencies** -- Dispatch ambulances, record run times, track facility statuses
- **Campus security** -- Monitor building alarms, dispatch officers, log patrol activity

### System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 8.0 | 8.2 or 8.4 |
| MySQL/MariaDB | 5.7 / 10.3 | 8.0+ / 10.11 |
| Web Server | Apache 2.4 or nginx | Apache 2.4 with mod_rewrite |
| CPU | 1 core | 2 cores |
| RAM | 512 MB | 1 GB |
| Disk | 500 MB | 2 GB |
| Browser | Any modern browser (Chrome, Firefox, Edge, Safari) | Chrome or Firefox |

PHP 7.4 is supported on existing installations but has reached end-of-life. PHP 8.0 through 8.4 are fully tested and supported via a built-in compatibility layer.

### Two Versions: Legacy (v3.44) and NewUI (v4.0)

TicketsCAD ships as two separate applications:

**Legacy v3.44** (`/tickets/`)
- The original interface, using HTML framesets and jQuery 1.4.2
- Over 800 PHP files, battle-tested across hundreds of installations
- Supports all features documented in this guide
- Recommended for production use today

**NewUI v4.0** (`/newui/`)
- A ground-up rewrite with Bootstrap 5, Leaflet maps, and a modern dashboard
- Keyboard-first design optimized for dispatchers
- Currently in active development (v4.0.0-dev)
- Over 60 API endpoints, 30+ configuration panels, and full feature parity in progress
- Recommended for evaluation and testing; not yet considered production-ready

Both versions can run against the same database, though NewUI uses its own database by default during development.

### Choosing Which Version to Install

| Situation | Recommendation |
|-----------|---------------|
| New deployment, need production stability | Legacy v3.44 via Docker |
| Evaluating TicketsCAD for the first time | Legacy v3.44 (Docker or XAMPP) |
| Want to test the modern interface | Install both; use NewUI alongside Legacy |
| Existing v3.x installation | Upgrade to v3.44.2 (security critical) |

---

## Part 2: Installation

### Docker Installation (Recommended)

Docker is the easiest way to deploy TicketsCAD. It bundles PHP, Apache, and MariaDB into containers that run identically on any operating system. No manual server configuration is needed.

#### Prerequisites

- **Windows or macOS:** Install [Docker Desktop](https://www.docker.com/products/docker-desktop/) (includes Docker Compose)
- **Linux:** Install Docker Engine and the Compose plugin (see Linux instructions below)

#### Quick Start (3 Commands)

```bash
curl -LO https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml
docker compose up -d
# Open http://localhost:8080 -- Login: admin / admin
```

Wait about 30 seconds for the database to initialize on first run. The installer runs automatically, creates all 110 tables, and provisions an admin account.

**Change the default admin password immediately after first login.**

#### Custom Configuration

Set environment variables before starting to customize the deployment:

```bash
# Set a secure admin password and database password
ADMIN_PASS="YourSecurePassword" DB_PASS="YourDBPassword" docker compose up -d
```

Or create a `.env` file in the same directory as `docker-compose.yml`:

```
ADMIN_USER=admin
ADMIN_PASS=YourSecurePassword
ADMIN_NAME=John Smith
DB_PASS=YourDBPassword
DB_ROOT_PASS=YourRootPassword
WEB_PORT=8080
```

Then run `docker compose up -d` and the values will be applied automatically.

See [Appendix B](#appendix-b-environment-variables-docker) for the complete list of environment variables.

#### Windows-Specific Instructions

1. Download Docker Desktop from https://www.docker.com/products/docker-desktop/
2. Run the installer and accept defaults
3. When prompted, enable "Use WSL 2 based engine" (recommended)
4. Restart your computer if prompted
5. Start Docker Desktop from the Start menu
6. Wait for the system tray icon to show "Docker Desktop is running"

**Deploy from PowerShell:**

```powershell
mkdir C:\TicketsCAD
cd C:\TicketsCAD
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml" -OutFile "docker-compose.yml"
$env:ADMIN_PASS = "YourSecurePassword"
docker compose up -d
```

**Deploy from Git Bash:**

```bash
mkdir /c/TicketsCAD && cd /c/TicketsCAD
curl -LO https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml
ADMIN_PASS="YourSecurePassword" docker compose up -d
```

**Windows troubleshooting:**
- "Docker daemon not running" -- Start Docker Desktop from the Start menu
- Port 8080 in use -- Change the port: `WEB_PORT=9090 docker compose up -d`
- WSL error -- Run `wsl --install` in PowerShell as Administrator, then restart

#### Linux-Specific Instructions

**Install Docker (Debian/Ubuntu):**

```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER
sudo systemctl enable docker
sudo systemctl start docker
```

Log out and log back in for the group change to take effect.

**Install Docker (RHEL/CentOS/Fedora):**

```bash
sudo dnf install docker-ce docker-ce-cli containerd.io docker-compose-plugin
sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -aG docker $USER
```

**Deploy:**

```bash
mkdir ~/ticketscad && cd ~/ticketscad
curl -LO https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml

# Generate secure passwords
export DB_PASS="$(openssl rand -base64 16)"
export ADMIN_PASS="$(openssl rand -base64 12)"
echo "DB Password: $DB_PASS"
echo "Admin Password: $ADMIN_PASS"

docker compose up -d
```

**Production deployment with HTTPS (nginx reverse proxy):**

```bash
sudo apt install nginx certbot python3-certbot-nginx

sudo tee /etc/nginx/sites-available/ticketscad << 'EOF'
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

sudo ln -sf /etc/nginx/sites-available/ticketscad /etc/nginx/sites-enabled/
sudo certbot --nginx -d your-domain.com
sudo systemctl restart nginx
```

### Traditional Installation (XAMPP/LAMP)

Use this method if you prefer to manage your own web server, or if Docker is not available in your environment.

#### Installing XAMPP on Windows

1. Download XAMPP from https://www.apachefriends.org/
   - XAMPP 8.2.x is recommended (includes PHP 8.2, Apache 2.4, MariaDB 10.4)
2. Run the installer and accept defaults
3. Install to `C:\xampp` (or your preferred location)
4. Open the XAMPP Control Panel
5. Start the **Apache** and **MySQL** services

#### Installing LAMP on Linux (Debian/Ubuntu)

```bash
sudo apt update
sudo apt install apache2 mariadb-server php php-mysqli php-gd php-mbstring php-xml php-curl php-zip libapache2-mod-php
sudo systemctl enable apache2 mariadb
sudo systemctl start apache2 mariadb
sudo mysql_secure_installation
```

For CentOS/RHEL, replace `apt` with `dnf` or `yum` and install the equivalent packages. Enable the Remi repository for newer PHP versions if needed.

#### Creating the Database

**Using phpMyAdmin (XAMPP):**

1. Open http://localhost/phpmyadmin
2. Click "New" in the left sidebar
3. Enter database name: `tickets`
4. Select collation: `utf8mb4_general_ci`
5. Click "Create"

**Using the command line:**

```sql
mysql -u root -p

CREATE DATABASE tickets CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'tickets'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON tickets.* TO 'tickets'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Downloading TicketsCAD

**Option A: Download a release ZIP**

1. Go to https://github.com/openises/tickets/releases
2. Download the latest `.zip` file
3. Extract to your web server's document root:
   - Windows XAMPP: `C:\xampp\htdocs\tickets\`
   - Linux Apache: `/var/www/html/tickets/`

**Option B: Git clone**

```bash
cd /var/www/html     # or C:\xampp\htdocs on Windows
git clone https://github.com/openises/tickets.git
```

**Linux file ownership:**

```bash
sudo chown -R www-data:www-data /var/www/html/tickets
```

On CentOS/RHEL systems using SELinux, also run:

```bash
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/tickets/
sudo setsebool -P httpd_can_sendmail 1
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_can_network_connect_db 1
```

#### Running the Installer

1. Open `http://localhost/tickets/install.php` in your browser
2. Select **Install / Reinstall** mode
3. Fill in the database connection details:
   - **MySQL host:** `localhost`
   - **MySQL database:** `tickets`
   - **MySQL username:** `tickets` (or `root` with XAMPP defaults)
   - **MySQL password:** The password you set when creating the database user
   - **Table prefix:** Leave blank unless you need multiple installations in one database
4. Set the initial admin account:
   - **Super admin username:** `admin`
   - **Super admin password:** Choose a strong password (minimum 6 characters)
5. Click **Do It**
6. Wait for installation to complete (approximately 15 seconds)
7. Click **Go to TicketsCAD**

#### Post-Install Checklist

- [ ] Log in with your admin credentials
- [ ] Change the admin password if you used a weak one during install
- [ ] Set your organization name (Config > General)
- [ ] Set the default map location (Config > Map)
- [ ] Set the time zone (Config > General)
- [ ] Disable or rename `install.php` to prevent unauthorized re-installation:
  ```bash
  mv install.php install.php.disabled
  ```
- [ ] Verify the uploads directory is writable
- [ ] Check `tools/diagnose.php` for any configuration issues

### Upgrading from Previous Versions

#### Backup First

Before any upgrade, back up your database:

```bash
# Docker
docker exec ticketscad_db mariadb-dump -u tickets -pYOUR_DB_PASS tickets > backup_$(date +%Y%m%d).sql

# Traditional
mysqldump -u tickets -p tickets > backup_$(date +%Y%m%d).sql
```

Also back up your configuration file (`incs/mysql.inc.php`) and any custom files.

#### Upgrade from 3.42/3.43 to 3.44.2

**Docker:**

```bash
docker compose pull && docker compose up -d
```

**Traditional:**

1. Download v3.44.2 from https://github.com/openises/tickets/releases/tag/v3.44.2
2. Extract over your existing installation
3. **Preserve** these files (do not overwrite):
   - `incs/mysql.inc.php` (database configuration)
   - `uploads/` directory (user attachments)
   - `_tile_cache/` directory (cached map tiles)
4. Open `http://your-server/tickets/install.php`
5. Select **Upgrade** mode
6. Click **Do It**
7. Verify your data is intact

#### Upgrade from Very Old Versions (3.30 and Earlier)

1. **Passwords work automatically.** The login system recognizes all 6 legacy password formats: bcrypt, MD5 (case-sensitive and case-insensitive), MySQL `PASSWORD()`, SHA1, and plain text. Existing passwords are silently upgraded to bcrypt on the next successful login. No password resets are required.

2. **If login fails after upgrade,** use the emergency password reset tool:
   ```
   http://your-server/tickets/tools/reset-admin-password.php
   ```
   Delete this file after use -- it allows anyone to reset the admin password.

3. **PHP compatibility.** Version 3.44.2 includes a compatibility layer (`incs/compat.inc.php`) that polyfills functions removed in newer PHP versions. It supports PHP 7.0 through 8.4.

#### Password Compatibility (All 6 Formats Supported)

| Format | Hash Pattern | Versions |
|--------|-------------|----------|
| bcrypt | `$2y$...` (60 chars) | Current standard |
| MD5 | 32-char hex | v3.0 -- v3.40 |
| MD5 case-insensitive | 32-char hex | Some v3.x variants |
| MySQL PASSWORD() | `*` + 40-char hex | Very old versions |
| SHA1 | 40-char hex | Some custom installs |
| Plain text | Variable | Misconfigured installs |

All legacy formats are auto-upgraded to bcrypt on the next successful login.

#### PHP 8.2+ Compatibility Layer

The file `incs/compat.inc.php` is loaded automatically and polyfills:

| Function | Removed In | Polyfill Method |
|----------|-----------|----------------|
| `utf8_encode()` / `utf8_decode()` | PHP 8.2 | `mb_convert_encoding()` |
| `each()` | PHP 8.0 | Manual array iteration |
| `create_function()` | PHP 8.0 | Eval wrapper (BC only) |
| `FILTER_SANITIZE_STRING` | PHP 8.1 | Maps to `FILTER_DEFAULT` |
| `str_contains()` / `str_starts_with()` / `str_ends_with()` | Added in PHP 8.0 | Backported for PHP 7.x |

Deprecation warnings from `strftime()`, null parameter passing, and `${var}` string interpolation are suppressed.

#### MySQL 8.0 Strict Mode Fix

MySQL 8.0+ enables `ONLY_FULL_GROUP_BY` by default, which breaks legacy queries that use `SELECT *` with `GROUP BY`. Version 3.44.2 sets a compatible SQL mode at connection time. If you see blank pages on Situation, New, Units, or Config screens, update to v3.44.2.

MySQL 8.0 also rejects empty strings for DATETIME columns. The updated connection code disables `STRICT_TRANS_TABLES` for compatibility.

#### Common Upgrade Issues and Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| Blank pages | MySQL 8.0 `ONLY_FULL_GROUP_BY` | Update to v3.44.2 |
| White screen | PHP 8.2+ removed `utf8_encode()` | Update to v3.44.2 |
| "Field doesn't have a default value" | Legacy NOT NULL columns | Installer auto-fixes in v3.44.2 |
| 504 Gateway Timeout | Large database, slow upgrade | Increase `max_execution_time` to 300 |
| Installer parse error | PHP warnings before JSON | Re-download latest files |

---

## Part 3: Initial Configuration

After installation, configure these settings before putting the system into service. In Legacy, all settings are under **Config** in the top menu. In NewUI, settings are under **Config/Settings** in the sidebar.

### Changing the Admin Password (Do This FIRST)

**Legacy:** Config > Users > Click your admin username > Change password

**NewUI:** Config > Users > Edit the admin account

Choose a strong password. TicketsCAD hashes passwords with bcrypt (cost 12). There is no password recovery mechanism other than the emergency reset tool.

### Setting Your Organization Name and Location

**Legacy:** Config > General

| Setting | Description | Example |
|---------|-------------|---------|
| `login_banner` | Text shown on the login page | `Central County Fire Dispatch` |
| `title_string` | Browser title bar text | `CCFD CAD` |
| `host` | Your server's domain name | `dispatch.centralcounty.gov` |
| `def_city` | Default city for new incidents | `Springfield` |
| `def_st` | Default state abbreviation | `IL` |
| `def_area_code` | Default area code for phone fields | `217` |

### Setting the Default Map Center and Zoom

**Legacy:** Config > Map

| Setting | Description | Default |
|---------|-------------|---------|
| `def_lat` | Default map center latitude | `39.1` |
| `def_lng` | Default map center longitude | `-90.7` |
| `def_zoom` | Default zoom level (1-18) | `3` |
| `bounds` | Optional bounding box for geocoding bias | `0.0,0.0,0.0,0.0` |

Set these to your jurisdiction's center point and a zoom level that shows your full coverage area. Zoom level 12-14 is typical for a city; 10-11 for a county.

### Configuring the Tile Server (Proxy vs Direct)

| Setting | Description |
|---------|-------------|
| `tile_mode` | `online` (direct from browser), `proxy` (server-side), or `offline` |
| `tile_server_url` | Tile server URL pattern. Default: `https://tile.openstreetmap.org/{z}/{x}/{y}.png` |
| `tile_cache_days` | Days to cache tiles locally (proxy mode). Default: 60 |

**Recommended:** Use `proxy` mode. It avoids browser Referer header issues that cause "Access blocked" errors with OpenStreetMap. Docker deployments default to proxy mode.

### Time Zone and Date Format

| Setting | Description | Default |
|---------|-------------|---------|
| `timezone` | PHP timezone identifier | `America/New_York` |
| `date_format` | PHP date format string | `n/j/y H:i` |
| `military_time` | Use 24-hour time (1 = yes, 0 = no) | `1` |

Set the timezone to match your jurisdiction. A list of valid timezone identifiers is at https://www.php.net/manual/en/timezones.php.

### Incident Numbering Format

The `_inc_num` setting controls how incident numbers are generated. This is a base64-encoded serialized PHP array. Configure it through the Config > Incident Numbers panel in the UI rather than editing the database directly.

Options include:
- Sequential numbering (simple counter)
- Date-prefixed numbering (e.g., 2026-0001)
- Custom prefix with counter

---

## Part 4: Setting Up Your Organization

### Defining Incident Types (with Protocols)

Incident types categorize calls and can include response protocols -- step-by-step instructions the dispatcher reads to the caller or announces to responders.

**Legacy:** Config > Incident Types
**NewUI:** Config > Incident Types

Each incident type has:

| Field | Description |
|-------|-------------|
| Type | Short name (e.g., "Structure Fire", "Chest Pain") |
| Description | Longer description |
| Protocol | Response protocol text (displayed when this type is selected during dispatch) |
| Severity | Default severity level (0-5) |
| Group | Organization group this type belongs to |
| Radius | Alert radius in miles |
| Color | Display color for the map and situation board |
| Sort | Display order |

TicketsCAD ships with 50 demo incident types across 5 organizational templates: RACES, CERT, Medical Team, Volunteer Fire, and Campus PD.

### Defining Unit Types and Statuses

Units represent responders, apparatus, or teams that can be dispatched. Each unit has a status indicating its availability.

**Legacy:** Config > Unit Types and Config > Unit Statuses

Default dispatch statuses: `D/R/O/FE/FA/Clear` (Dispatched, Responding, On-Scene, Fire Extinguished, Fire Apparatus, Clear).

Customize statuses to match your organization's protocols. Each status can trigger different display colors on the situation board.

### Defining Facility Types and Statuses

Facilities represent fixed locations like hospitals, shelters, stations, and staging areas.

**Legacy:** Config > Facilities

Each facility record includes name, type, description, address, and lat/lng coordinates for map display. In NewUI, facilities also support bed/capacity tracking.

### Creating Regions

Regions divide your coverage area into zones for dispatching and visibility control.

**Legacy:** Config > Regions

Enable regions with the `regions_control` setting. When enabled, incidents can be assigned to regions, and users can be restricted to seeing only incidents in their region.

### Setting Up Signal Codes

Signal codes (10-codes, unit signals, etc.) provide shorthand references during dispatch.

**Legacy:** Config > Signal Codes

The signal codes table (`signals` or `hints`, depending on installation age) stores code-description pairs. These appear in dropdown menus when creating incidents.

---

## Part 5: User Management

### Creating User Accounts

**Legacy:** Config > Users > Add New User
**NewUI:** Config > Users

Each user account requires a username, password, display name, and access level.

### Access Levels (Legacy)

Legacy TicketsCAD uses a numeric access level system:

| Level | Name | Numeric Value | Capabilities |
|-------|------|--------------|-------------|
| Super | Super Administrator | 0 | Full access to all features including user management and system configuration |
| Admin | Administrator | 1 | Full access except user management; can modify config settings |
| Operator | Operator/Dispatcher | 2 | Create and manage incidents, dispatch units, view all data |
| Guest | Guest | 3 | View-only access to the situation board and public information |
| Member | Member | 4 | Limited access; can view incidents assigned to their region/group |
| Unit | Unit | 5 | Mobile unit interface; can update own status and view assigned incidents |

### RBAC Roles and Permissions (NewUI)

NewUI v4.0 adds a granular Role-Based Access Control system on top of the legacy levels. RBAC allows defining custom roles with specific permissions:

- `action.manage_config` -- Modify system configuration
- `action.manage_users` -- Create and edit user accounts
- `action.create_incident` -- Create new incidents
- `action.dispatch_units` -- Assign units to incidents
- And many more

The RBAC schema is in place; the full administration UI is under development.

### Two-Factor Authentication Setup (NewUI)

NewUI supports TOTP-based two-factor authentication (compatible with Google Authenticator, Authy, and similar apps).

1. Go to Config > Security > Two-Factor Authentication
2. Enable 2FA for the desired user accounts
3. Users scan the QR code with their authenticator app
4. On subsequent logins, users enter their password plus the 6-digit code

### Login Security (Lockout, Session Management)

| Setting | Description | Default |
|---------|-------------|---------|
| `session_timeout` | Minutes of inactivity before session expires | 60 |
| `login_userlist` | Show username dropdown on login page (0 = no, 1 = yes) | 0 |

NewUI adds:
- **Login attempt logging** -- All login attempts (success and failure) are recorded with IP address and timestamp
- **Active session monitoring** -- Admins can view all active sessions and force-logout any session
- **Account lockout** -- After repeated failed attempts, accounts are temporarily locked

---

## Part 6: Communications Setup

### Email Configuration

TicketsCAD can send email notifications for new incidents, assignments, and status changes.

| Setting | Description |
|---------|-------------|
| `email_from` | Sender address for outgoing emails |
| `email_reply_to` | Reply-to address |
| `smtp_acct` | SMTP account credentials (if using external relay) |
| `validate_email` | Validate email addresses on input (1 = yes) |

For Docker deployments, configure an external SMTP relay (SendGrid, Mailgun, Amazon SES, or your organization's mail server) since the container does not include a local mail server.

For traditional installations, PHP's built-in `mail()` function uses the local sendmail binary. On Linux, ensure `sendmail` or `postfix` is installed and running.

### SMS Configuration (NewUI)

NewUI supports multiple SMS providers through a generic REST adapter:

| Provider | Configuration |
|----------|--------------|
| **Generic REST** | Any HTTP API that accepts a URL, phone number, and message body |
| **Twilio** | Account SID, Auth Token, and From number |
| **BulkVS** | API key and endpoint |
| **Pushbullet** | API key (sends push notifications, not true SMS) |

Configure SMS providers in Config > Communications > SMS.

### Slack Integration (NewUI)

NewUI can post incident notifications to Slack channels using incoming webhooks.

1. Create an Incoming Webhook in your Slack workspace
2. Copy the webhook URL
3. In TicketsCAD, go to Config > Communications > Slack
4. Paste the webhook URL
5. Select which events trigger Slack notifications

### Chat System (NewUI)

NewUI includes a built-in chat system for real-time dispatcher-to-dispatcher communication. The chat system supports:

- Direct messages between users
- Channel-based group messaging
- Signal code / status code shortcuts
- Message history and search

The chat system uses Server-Sent Events (SSE) for real-time message delivery without requiring WebSocket infrastructure.

---

## Part 7: Map Configuration

### Tile Server Modes

TicketsCAD supports three map tile modes:

**Online (Direct):** The browser loads map tiles directly from the tile server (e.g., OpenStreetMap). Simplest configuration but may trigger "Access blocked" errors if the browser does not send the correct Referer header.

**Proxy:** The TicketsCAD server fetches tiles on behalf of the browser and caches them locally. This avoids Referer issues and reduces external bandwidth. Recommended for most deployments.

**Offline:** Tiles are served entirely from the local cache. Use this for air-gapped or disconnected deployments. You must pre-populate the tile cache before going offline.

| Setting | Description |
|---------|-------------|
| `tile_mode` | `online`, `proxy`, or `offline` |
| `tile_server_url` | URL template for the tile server |
| `tile_cache_days` | Number of days to cache tiles (proxy mode) |
| `local_maps` | Enable local map tile serving |

### Weather Overlays

TicketsCAD can display weather radar and alert overlays on the map using data from the National Weather Service and OpenWeatherMap.

| Setting | Description |
|---------|-------------|
| `openweathermaps_api` | API key for OpenWeatherMap (free tier available) |

NewUI includes a weather proxy (`api/weather-proxy.php`) that caches weather tiles to reduce API calls and improve performance.

### Map Markups and Zones (NewUI)

NewUI supports drawing on the map to mark hazard zones, search areas, staging locations, and other operational boundaries.

- **Draw tools:** Lines, polygons, circles, and markers
- **Categories:** Organize markups by category (hazard, search, staging, etc.)
- **Toggle visibility:** Show or hide markup categories independently
- **Persistence:** Markups are saved to the database and shared across all users

### Road Conditions (NewUI)

NewUI includes a road conditions overlay for displaying road closures, construction zones, and other travel advisories. Road condition data can be entered manually or imported via API.

---

## Part 8: Maintenance

### Database Backup and Restore

**Docker:**

```bash
# Backup
docker exec ticketscad_db mariadb-dump -u tickets -pYOUR_DB_PASS tickets > backup_$(date +%Y%m%d).sql

# Restore
docker exec -i ticketscad_db mariadb -u tickets -pYOUR_DB_PASS tickets < backup_20260401.sql
```

**Traditional:**

```bash
# Backup
mysqldump -u tickets -p tickets > backup_$(date +%Y%m%d).sql

# Restore
mysql -u tickets -p tickets < backup_20260401.sql
```

Schedule daily backups using cron (Linux) or Task Scheduler (Windows). Keep at least 7 days of backups.

### Docker: Volumes, Upgrades, and Monitoring

**Persistent volumes:**

| Volume | Contents |
|--------|----------|
| `db_data` | MariaDB database files (all incidents, users, settings) |
| `uploads` | File attachments uploaded by users |
| `tile_cache` | Cached OSM map tiles |
| `config` | Database connection configuration |

Your data is safe across container restarts and upgrades. Only `docker compose down -v` (with the `-v` flag) removes volumes -- never run this on production.

**Upgrading:**

```bash
cd ~/ticketscad
docker compose pull
docker compose up -d
```

The updated container detects version mismatches and syncs the database schema automatically.

**Monitoring:**

```bash
# Check container status
docker compose ps

# View web server logs
docker compose logs web

# View database logs
docker compose logs db

# Check web server responds
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/
```

### Traditional: File Backups, PHP Updates

Back up these files and directories:

- `incs/mysql.inc.php` -- Database configuration
- `uploads/` -- User file attachments
- `_tile_cache/` -- Cached map tiles
- The database (via mysqldump)

When updating PHP, use version 8.2 or 8.4. The compatibility layer handles differences between PHP versions, but always test on a staging copy first.

### Audit Log Review

TicketsCAD maintains an audit log of administrative actions. Review the log periodically for unauthorized changes.

**Legacy:** The `log` table records user actions with timestamps and IP addresses. View logs under Config > Log.

**NewUI:** The audit log (`api/audit-log.php`) provides a searchable interface with filtering by user, action type, and date range. A service health monitoring dashboard shows uptime and recent errors.

### Security Headers and HTTPS

TicketsCAD v3.44.2 sets the following security headers automatically:

- `X-Frame-Options: SAMEORIGIN` -- Prevents clickjacking
- `X-Content-Type-Options: nosniff` -- Prevents MIME type sniffing
- `X-XSS-Protection: 1; mode=block` -- Enables browser XSS filter
- `Referrer-Policy: same-origin` -- Prevents URL leakage to external sites
- `Cache-Control: no-store` -- Prevents caching of sensitive pages

The Docker image also sets these headers at the Apache level via `ServerTokens Prod` and `ServerSignature Off`.

**HTTPS is strongly recommended for production.** Use a reverse proxy (nginx with Let's Encrypt) as described in the Linux installation section. Session cookies are automatically marked `Secure` when HTTPS is detected, and `SameSite=Lax` is set to prevent CSRF via cross-site form POST.

---

## Part 9: Troubleshooting

### Blank Pages (MySQL 8.0 ONLY_FULL_GROUP_BY)

**Symptom:** Situation board, New Incident, Units, Facilities, or Config pages are blank.

**Cause:** MySQL 8.0+ enables `ONLY_FULL_GROUP_BY` by default. Legacy queries use `SELECT *` with `GROUP BY`, which strict mode rejects.

**Fix:** Update to v3.44.2. The updated database connection code sets a compatible SQL mode automatically. If you cannot update, add this to `incs/db.inc.php` after the connection:

```php
$mysqli->query("SET sql_mode = ''");
```

### White Screen (PHP 8.2+ utf8_encode)

**Symptom:** Blank white page, no error visible in browser.

**Cause:** PHP 8.2 removed `utf8_encode()` and `utf8_decode()`. Legacy code calls these functions.

**Fix:** Update to v3.44.2, which includes the compatibility polyfill in `incs/compat.inc.php`. Check the PHP error log for the specific fatal error:

```bash
# Linux
tail -50 /var/log/php_errors.log

# XAMPP Windows
type C:\xampp\php\logs\php_error_log

# Docker
docker exec ticketscad tail -50 /var/log/php_errors.log
```

### Map Tiles "Access Blocked" (Referer Header)

**Symptom:** Map shows gray tiles or "Access blocked" errors in the browser console.

**Cause:** OpenStreetMap tile servers require a valid Referer header. Some browsers or proxy configurations strip this header.

**Fix (preferred):** Switch to proxy tile mode:
1. Config > Map > Tile Mode > Set to `proxy`
2. This routes tile requests through your server, which adds the correct Referer header

**Fix (alternative):** Clear your browser cache (Ctrl+Shift+Delete) and reload. Version 3.44.2 adds `referrerPolicy: 'origin'` to tile requests.

### Login Issues

**Password format mismatch:**
If you upgraded from a very old version and cannot log in, your password hash format may not be recognized. Update to v3.44.2 (which supports all 6 formats) or use the password reset tool:

```
http://your-server/tickets/tools/reset-admin-password.php
```

Delete this file immediately after use.

**Session expired too quickly:**
Increase the `session_timeout` setting (default: 60 minutes). In PHP, also check `session.gc_maxlifetime` in `php.ini`.

**"Login userlist" shows all usernames:**
Set `login_userlist` to `0` in Config > General to hide the username dropdown. Showing usernames on the login page is a security risk.

### Installer Problems

**"Installer step parse error":** PHP warnings are being output before JSON. This is fixed in v3.44.2. Re-download the latest files.

**"Field doesn't have a default value":** Legacy tables have NOT NULL columns without defaults. The v3.44.2 installer detects and fixes these automatically.

**Version mismatch detection:** The installer detects when the code version does not match the database version and prompts for an upgrade.

### PHP Error Log Locations

| Environment | Log Location |
|-------------|-------------|
| XAMPP Windows | `C:\xampp\php\logs\php_error_log` |
| Linux Apache | `/var/log/php_errors.log` or `/var/log/apache2/error.log` |
| Docker | `docker exec ticketscad tail -50 /var/log/php_errors.log` |
| Custom | Check `error_log` directive in `php.ini` |

### Diagnostic Tool

TicketsCAD includes a diagnostic tool that checks PHP version, database connectivity, table count, file permissions, and configuration:

```
http://your-server/tickets/tools/diagnose.php
```

Delete or restrict access to this file on production servers after use.

---

## Appendix A: Settings Reference

The `settings` table stores system configuration as name-value pairs. Below is a reference of all settings with descriptions and defaults.

### General Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `login_banner` | `Welcome to Tickets - an Open Source Dispatch System` | Text displayed on the login page |
| `title_string` | *(empty)* | Browser title bar text |
| `host` | `www.yourdomain.com` | Server hostname |
| `timezone` | `America/New_York` | PHP timezone identifier |
| `date_format` | `n/j/y H:i` | PHP date format string |
| `military_time` | `1` | Use 24-hour time (1=yes, 0=no) |
| `locale` | `0` | Locale setting for number/date formatting |
| `session_timeout` | `60` | Session timeout in minutes |
| `login_userlist` | `0` | Show username dropdown on login (1=yes, 0=no) |
| `debug` | `0` | Enable debug mode (1=yes, 0=no) |
| `log_days` | `3` | Number of days to retain log entries |
| `internet` | `1` | System has internet access (1=yes, 0=no) |

### Default Location

| Setting | Default | Description |
|---------|---------|-------------|
| `def_city` | *(empty)* | Default city for new incidents |
| `def_st` | *(empty)* | Default state abbreviation |
| `def_area_code` | *(empty)* | Default phone area code |
| `def_lat` | `39.1` | Default map center latitude |
| `def_lng` | `-90.7` | Default map center longitude |
| `def_zoom` | `3` | Default map zoom level (1-18) |
| `def_zoom_fixed` | `0` | Lock zoom level (1=yes, 0=no) |
| `bounds` | `0.0,0.0,0.0,0.0` | Geocoding bounding box (south,west,north,east) |

### Map Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `tile_mode` | `online` | Tile server mode: `online`, `proxy`, or `offline` |
| `tile_server_url` | `https://tile.openstreetmap.org/{z}/{x}/{y}.png` | Tile server URL pattern |
| `tile_cache_days` | `60` | Days to cache tiles in proxy mode |
| `local_maps` | `0` | Enable local map tile serving |
| `default_map_layer` | `0` | Default map layer index |
| `terrain` | `1` | Show terrain layer option |
| `maptype` | `1` | Map type selector |
| `map_height` | `512` | Map height in pixels |
| `map_width` | `512` | Map width in pixels |
| `map_caption` | `Your area` | Map title caption |
| `kml_files` | `1` | Enable KML file overlay |
| `lat_lng` | `0` | Show lat/lng on map clicks |
| `map_in_portal` | `1` | Show map in the portal view |
| `map_on_rm` | `1` | Show map on responder mobile |
| `use_osmap` | `0` | Use OS Map (UK Ordnance Survey) |
| `openspace_api` | `0` | OpenSpace API key |

### Geocoding

| Setting | Default | Description |
|---------|---------|-------------|
| `reverse_geo` | `1` | Enable reverse geocoding |
| `geocoding_provider` | `0` | Geocoding provider (0=Nominatim) |
| `bing_api_key` | *(empty)* | Bing Maps API key |
| `addr_source` | `0` | Address data source |
| `allow_nogeo` | `0` | Allow incidents without geocoded address |

### API Keys

| Setting | Default | Description |
|---------|---------|-------------|
| `gmaps_api_key` | *(empty)* | Google Maps API key (legacy) |
| `openweathermaps_api` | *(empty)* | OpenWeatherMap API key |
| `aprs_fi_key` | *(empty)* | APRS.fi API key for station lookups |
| `cloudmade_api` | *(empty)* | CloudMade API key (legacy) |
| `wp_key` | *(empty)* | WhitePages API key |
| `instam_key` | *(empty)* | Instamapper API key (legacy) |

### Incident Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `_inc_num` | *(encoded)* | Incident numbering format (base64 serialized) |
| `serial_no_ap` | `1` | Append serial number to incidents |
| `abbreviate_affected` | `30` | Truncate affected field at N characters |
| `abbreviate_description` | `30` | Truncate description field at N characters |
| `closed_interval` | *(empty)* | Auto-close interval for incidents |
| `ticket_per_page` | `0` | Incidents per page (0=all) |
| `ticket_table_width` | `640` | Incident table width in pixels |
| `quick` | `0` | Enable quick-add incident mode |
| `allow_custom_tags` | `0` | Allow custom tags on incidents |
| `add_uselocation` | `0` | Use location services for new incidents |

### Dispatch Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `disp_stat` | `D/R/O/FE/FA/Clear` | Dispatch status sequence |
| `group_or_dispatch` | `0` | Group-based dispatch (1=yes, 0=no) |
| `restrict_user_add` | `0` | Restrict who can add incidents |
| `restrict_user_tickets` | `0` | Restrict incident visibility by user |
| `restrict_units` | `0` | Restrict unit visibility |
| `regions_control` | `0` | Enable region-based dispatch |
| `use_disp_autostat` | `0` | Auto-update dispatch status |
| `notify_assigns` | `1` | Notify on unit assignments |
| `notify_facilities` | `0` | Notify facilities on incidents |
| `notify_in_types` | `0` | Notify by incident type |
| `warn_proximity` | `1` | Warn on nearby incidents |
| `warn_proximity_units` | `M` | Proximity units (M=miles, K=km) |
| `guest_add_ticket` | `0` | Allow guests to create incidents |
| `oper_can_edit` | `0` | Operators can edit incidents |
| `unit_can_edit` | `0` | Units can edit incidents |

### Communications

| Setting | Default | Description |
|---------|---------|-------------|
| `email_from` | *(empty)* | Email sender address |
| `email_reply_to` | *(empty)* | Email reply-to address |
| `smtp_acct` | *(empty)* | SMTP account configuration |
| `allow_notify` | `1` | Enable email notifications |
| `use_messaging` | `0` | Enable messaging system |
| `broadcast` | `0` | Enable broadcast messages |
| `socketserver_url` | *(empty)* | WebSocket server URL |
| `socketserver_port` | *(empty)* | WebSocket server port |

### Responder / Unit Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `use_responder_mobile` | `1` | Enable responder mobile interface |
| `responder_mobile_tracking` | `2` | Mobile tracking mode |
| `responder_mobile_forcelogin` | `1` | Force login on mobile |
| `responder_list_sort` | `1,1` | Default responder list sort |
| `mob_show_cleared` | `1` | Show cleared incidents on mobile |
| `hide_booked` | `48` | Hide booked-off units after N hours |

### Facility Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `facility_auto_status` | `0` | Auto-update facility status |
| `facility_list_sort` | `1,1` | Default facility list sort |
| `facboard_hide_patient` | `0` | Hide patient info on facility board |

### Display Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `frameborder` | `1` | Show frame borders (Legacy) |
| `framesize` | `50` | Frame size percentage (Legacy) |
| `listheader_height` | `25` | List header height in pixels |
| `situ_refr` | *(empty)* | Situation board refresh interval |
| `auto_refresh` | `1/1/1` | Auto-refresh settings per panel |
| `custom_situation` | `1/1` | Custom situation board configuration |
| `alternate_sit` | `0` | Use alternate situation layout |
| `full_sit_v2` | `0` | Use full situation v2 layout |
| `call_board` | `1` | Show call board |
| `chat_time` | `4` | Chat display time in seconds |
| `ics_top` | `0` | Show ICS bar at top |

### Tracking / External Data

| Setting | Default | Description |
|---------|---------|-------------|
| `auto_poll` | `0` | Auto-poll external data sources |
| `auto_route` | `1` | Auto-route calculations |
| `tracks_length` | `12` | GPS track history length in hours |
| `gtrack_url` | *(empty)* | GPS tracking URL |
| `xastir_server` | `localhost` | Xastir APRS server hostname |
| `xastir_db` | *(empty)* | Xastir database name |
| `xastir_dbuser` | *(empty)* | Xastir database username |
| `xastir_dbpass` | *(empty)* | Xastir database password |
| `traccar_server` | `localhost` | Traccar GPS server hostname |
| `traccar_db` | *(empty)* | Traccar database name |
| `traccar_dbuser` | *(empty)* | Traccar database username |
| `traccar_dbpass` | *(empty)* | Traccar database password |
| `javaprssrvr_server` | `localhost` | JavaPRSSrvr server hostname |
| `javaprssrvr_db` | *(empty)* | JavaPRSSrvr database name |
| `javaprssrvr_dbuser` | *(empty)* | JavaPRSSrvr database username |
| `javaprssrvr_dbpass` | *(empty)* | JavaPRSSrvr database password |
| `followmee_username` | *(empty)* | FollowMee GPS tracking username |
| `followmee_key` | *(empty)* | FollowMee API key |

### Reports / Statistics

| Setting | Default | Description |
|---------|---------|-------------|
| `report_graphic` | *(empty)* | Report header graphic file |
| `report_header` | *(empty)* | Report header text |
| `report_footer` | *(empty)* | Report footer text |
| `report_contact` | *(empty)* | Report contact information |
| `pie_charts` | `300/450/300` | Pie chart dimensions |
| `inc_statistics_red_thresholds` | `5,2,4,0,0,3` | Red threshold values for statistics |
| `inc_statistics_orange_thresholds` | `3,1,2,0,0,2` | Orange threshold values for statistics |

### Portal Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `portal_contact_email` | *(empty)* | Public portal contact email |
| `portal_contact_phone` | *(empty)* | Public portal contact phone |
| `access_requests` | `0` | Enable public access requests |

### SSL / Security

| Setting | Default | Description |
|---------|---------|-------------|
| `sslcert_location` | *(empty)* | SSL certificate file path |
| `sslcert_passphrase` | *(empty)* | SSL certificate passphrase |
| `httpuser` | *(empty)* | HTTP basic auth username (tile proxy) |
| `httppwd` | *(empty)* | HTTP basic auth password (tile proxy) |

### Miscellaneous

| Setting | Default | Description |
|---------|---------|-------------|
| `_version` | `v3.44.2` | Installed version (set by installer) |
| `_sleep` | `5` | Polling interval in seconds |
| `_cloud` | `0` | Cloud mode enabled |
| `_aprs_time` | *(timestamp)* | Last APRS poll timestamp |
| `logo` | `t.png` | Logo filename |
| `link_capt` | *(empty)* | Custom link caption |
| `link_url` | *(empty)* | Custom link URL |
| `func_key1` | `http://openises.sourceforge.net/,Open ISES` | Function key 1 URL and caption |
| `func_key2` | *(empty)* | Function key 2 URL and caption |
| `func_key3` | *(empty)* | Function key 3 URL and caption |
| `use_mdb` | `1` | Enable MDB import |
| `live_mdb` | `0` | Live MDB connection |
| `use_wizard` | `0` | Enable setup wizard |
| `os_watch` | `0/0/0` | OS Watch configuration |
| `sound_wav` | `aooga.wav` | Alert sound WAV file |
| `sound_mp3` | `phonesring.mp3` | Alert sound MP3 file |
| `delta_mins` | `0` | Time delta in minutes |
| `ogts_info` | *(empty)* | OGTS configuration info |

---

## Appendix B: Environment Variables (Docker)

These environment variables configure the Docker deployment. Set them in a `.env` file alongside `docker-compose.yml` or pass them on the command line.

### Database Configuration

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `db` | Database hostname (use `db` for the bundled MariaDB container) |
| `DB_USER` | `tickets` | Database username |
| `DB_PASS` | `tickets` | Database password |
| `DB_NAME` | `tickets` | Database name |
| `DB_PREFIX` | *(empty)* | Table name prefix (for multi-tenant setups) |
| `DB_ROOT_PASS` | `ticketscad_root_2026` | MariaDB root password |

### Application Configuration

| Variable | Default | Description |
|----------|---------|-------------|
| `ADMIN_USER` | `admin` | Initial admin username (only used on first install) |
| `ADMIN_PASS` | `admin` | Initial admin password (only used on first install) |
| `ADMIN_NAME` | `Super Administrator` | Admin display name |
| `AUTO_INSTALL` | `true` | Automatically create tables on first run |
| `WEB_PORT` | `8080` | Host port mapped to the web server |

### MariaDB Container

These are standard MariaDB Docker image variables, set on the `db` service:

| Variable | Default | Description |
|----------|---------|-------------|
| `MARIADB_ROOT_PASSWORD` | Uses `DB_ROOT_PASS` | Root password for MariaDB |
| `MARIADB_DATABASE` | Uses `DB_NAME` | Database to create on first run |
| `MARIADB_USER` | Uses `DB_USER` | User to create on first run |
| `MARIADB_PASSWORD` | Uses `DB_PASS` | Password for the created user |

### Example .env File

```
# TicketsCAD Docker Configuration
ADMIN_USER=admin
ADMIN_PASS=Ch@ng3M3!2026
ADMIN_NAME=Dispatch Supervisor
DB_PASS=Secur3_DB_P@ss!
DB_ROOT_PASS=R00t_P@ss_2026!
WEB_PORT=8080
```

---

## Appendix C: Security Checklist

Use this checklist to verify your TicketsCAD deployment follows security best practices. Based on OWASP guidelines.

### Pre-Deployment

- [ ] **Update to v3.44.2** -- Contains 88 security fixes for XSS, SQL injection, hardcoded secrets, SSL verification, and file permissions
- [ ] **Set strong passwords** -- Admin password, database password, and MariaDB root password should each be unique and at least 12 characters
- [ ] **Remove or disable install.php** -- Rename to `install.php.disabled` or delete after installation
- [ ] **Remove diagnostic tools** -- Delete `tools/diagnose.php` and `tools/reset-admin-password.php` after use

### Network and Transport

- [ ] **Enable HTTPS** -- Use TLS certificates (Let's Encrypt is free) with a reverse proxy
- [ ] **Restrict network access** -- TicketsCAD should only be accessible from your organization's network or via VPN
- [ ] **Firewall rules** -- Only expose ports 80/443 (or your chosen port); block direct database access from the internet
- [ ] **SSL verification enabled** -- v3.44.2 enables `CURLOPT_SSL_VERIFYPEER` by default on all outbound HTTPS connections

### Authentication

- [ ] **Disable login_userlist** -- Set to `0` to prevent username enumeration
- [ ] **Set session timeout** -- 60 minutes or less for dispatch environments
- [ ] **Enable 2FA** (NewUI) -- For all admin and dispatcher accounts
- [ ] **Review active sessions** periodically (NewUI) -- Force-logout any suspicious sessions
- [ ] **Password strength** -- Enforce minimum password length through organizational policy

### Application Security

- [ ] **XSS protection** -- v3.44.2 escapes all user input in HTML output using `htmlspecialchars()`
- [ ] **SQL injection protection** -- v3.44.2 uses prepared statements, `intval()`, and identifier whitelisting
- [ ] **CSRF tokens** -- All POST endpoints verify CSRF tokens stored in the session
- [ ] **Security headers** -- v3.44.2 sets X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, and Referrer-Policy
- [ ] **Secure session cookies** -- HttpOnly, SameSite=Lax, and Secure (when HTTPS) flags are set automatically
- [ ] **No hardcoded secrets** -- v3.44.2 removed all hardcoded API keys and database passwords from source files

### Server Hardening

- [ ] **PHP display_errors off** -- Set `display_errors = Off` in production php.ini
- [ ] **PHP expose_php off** -- Set `expose_php = Off` to hide PHP version from headers
- [ ] **Apache ServerTokens Prod** -- Hide Apache version information
- [ ] **File permissions** -- 755 for directories, 644 for files; v3.44.2 no longer uses 0777
- [ ] **uploads directory** -- Writable by web server (775) but not executable; consider adding `.htaccess` to deny PHP execution
- [ ] **Database user privileges** -- Grant only the privileges needed (ALL on the tickets database, not global)

### Docker-Specific

- [ ] **Change default passwords** -- Never use `admin`/`admin` or `tickets`/`tickets` in production
- [ ] **Do not use `docker compose down -v`** on production -- The `-v` flag deletes all data volumes
- [ ] **Container runs as non-root** -- The Dockerfile switches to `www-data` user
- [ ] **Health checks enabled** -- The MariaDB container includes a health check that prevents the web container from starting before the database is ready

### Ongoing

- [ ] **Monitor audit logs** -- Review for unauthorized access or configuration changes
- [ ] **Keep TicketsCAD updated** -- Watch the GitHub releases page for security updates
- [ ] **Keep PHP and MariaDB updated** -- Apply OS-level security patches regularly
- [ ] **Back up regularly** -- Daily database backups, stored off-server
- [ ] **Test restores** -- Periodically verify that backups can be restored successfully
- [ ] **Review user accounts** -- Deactivate accounts for departed members promptly

---

*This guide was written for TicketsCAD v3.44.2 (Legacy) and v4.0.0-dev (NewUI). For the latest information, see the [GitHub wiki](https://github.com/openises/tickets/wiki) and [release notes](https://github.com/openises/tickets/releases).*
