# SonarQube Setup Guide for TicketsCAD

## Overview

SonarQube is a continuous code quality and security scanner. Once set up, it will:
- Scan PHP code for SQL injection, XSS, CSRF, and 200+ other vulnerabilities
- Detect code smells, duplications, and complexity issues
- Track quality over time with dashboards
- Integrate with GitHub for PR analysis
- Run automatically on every commit via GitHub Actions

## Architecture

```
Proxmox Server
├── LXC Container or VM: SonarQube
│   ├── SonarQube Server (port 9000)
│   ├── PostgreSQL Database
│   └── SonarScanner CLI
└── Network: accessible from your workstation + GitHub Actions
```

---

## Step 1: Create a VM or LXC Container on Proxmox

### Option A: LXC Container (lighter, recommended)

In the Proxmox web UI:
1. Click **Create CT** (top right)
2. Settings:
   - **Template:** Ubuntu 22.04 or Debian 12
   - **Hostname:** `sonarqube`
   - **Disk:** 20 GB minimum (40 GB recommended for scan history)
   - **CPU:** 2 cores minimum
   - **Memory:** 4 GB minimum (6 GB recommended)
   - **Network:** DHCP or static IP on your LAN
3. Start the container

### Option B: VM (if you prefer full isolation)

1. Click **Create VM**
2. Use Ubuntu 22.04 Server ISO
3. Same resource recommendations as above
4. Install the OS normally

---

## Step 2: Install Docker in the Container/VM

SSH into your new container:

```bash
ssh root@<container-ip>
```

Install Docker:

```bash
apt update && apt upgrade -y
apt install -y docker.io docker-compose-plugin
systemctl enable docker
systemctl start docker
```

Verify:

```bash
docker --version
docker compose version
```

---

## Step 3: Deploy SonarQube with Docker Compose

Create the project directory:

```bash
mkdir -p /opt/sonarqube && cd /opt/sonarqube
```

Create `docker-compose.yml`:

```bash
cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  sonarqube:
    image: sonarqube:lts-community
    container_name: sonarqube
    depends_on:
      - db
    ports:
      - "9000:9000"
    environment:
      SONAR_JDBC_URL: jdbc:postgresql://db:5432/sonarqube
      SONAR_JDBC_USERNAME: sonar
      SONAR_JDBC_PASSWORD: sonar_password_change_me
    volumes:
      - sonarqube_data:/opt/sonarqube/data
      - sonarqube_extensions:/opt/sonarqube/extensions
      - sonarqube_logs:/opt/sonarqube/logs
    ulimits:
      nofile:
        soft: 65536
        hard: 65536
    restart: unless-stopped

  db:
    image: postgres:15
    container_name: sonarqube_db
    environment:
      POSTGRES_USER: sonar
      POSTGRES_PASSWORD: sonar_password_change_me
      POSTGRES_DB: sonarqube
    volumes:
      - postgresql_data:/var/lib/postgresql/data
    restart: unless-stopped

volumes:
  sonarqube_data:
  sonarqube_extensions:
  sonarqube_logs:
  postgresql_data:
EOF
```

**Important:** Change `sonar_password_change_me` to a strong password in both places.

Set required kernel parameter (SonarQube needs this):

```bash
echo "vm.max_map_count=524288" >> /etc/sysctl.conf
sysctl -w vm.max_map_count=524288
```

Start SonarQube:

```bash
cd /opt/sonarqube
docker compose up -d
```

Wait 2-3 minutes for it to start, then verify:

```bash
docker compose logs sonarqube | tail -5
```

You should see "SonarQube is operational" in the logs.

---

## Step 4: Initial Configuration

1. Open `http://<container-ip>:9000` in your browser
2. Login with default credentials: `admin` / `admin`
3. **Change the admin password immediately** when prompted

### Install PHP Plugin

1. Go to **Administration > Marketplace**
2. Search for "PHP"
3. Install the **SonarPHP** plugin (should be pre-installed in LTS)
4. Restart SonarQube if prompted

### Create a Project

1. Go to **Projects > Create Project**
2. Choose **Manually**
3. Settings:
   - **Project key:** `ticketscad-legacy`
   - **Display name:** `TicketsCAD Legacy (v3.44)`
4. Click **Set Up**

Optionally, create a second project for the NewUI (development-only, not part of the released product):
   - **Project key:** `ticketscad-newui`
   - **Display name:** `TicketsCAD NewUI (v4.0-dev)` -- *This is a development-only project. NewUI is not yet released.*

### Generate an Authentication Token

1. Go to **My Account > Security > Tokens**
2. Generate a new token:
   - **Name:** `github-scanner`
   - **Type:** Project Analysis Token
   - **Expires:** 1 year
3. **Copy the token** — you'll need it for the scanner and GitHub Actions

---

## Step 5: Install SonarScanner on Your Workstation

### Windows (Git Bash)

Download and extract:

```bash
cd /c/tools
curl -L -o sonar-scanner.zip https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-5.0.1.3006-windows.zip
unzip sonar-scanner.zip
```

Add to your PATH (add to `~/.bashrc`):

```bash
export PATH="$PATH:/c/tools/sonar-scanner-5.0.1.3006-windows/bin"
```

Verify:

```bash
sonar-scanner --version
```

---

## Step 6: Configure the Projects

### Legacy TicketsCAD

Create `C:\Users\ejosterberg\Documents\GITprojects\TicketsCADFixes\tickets\sonar-project.properties`:

```properties
sonar.projectKey=ticketscad-legacy
sonar.projectName=TicketsCAD Legacy (v3.44)
sonar.projectVersion=3.44.1

sonar.sources=.
sonar.language=php
sonar.sourceEncoding=UTF-8

# Exclusions — skip vendor libs, test tools, and non-PHP
sonar.exclusions=**/vendor/**,**/node_modules/**,**/tests/**,**/tools/**,**/*.js,**/*.css,**/*.html,**/*.sql

# SonarQube server
sonar.host.url=http://<container-ip>:9000

# Authentication (use environment variable in practice)
# sonar.token=YOUR_TOKEN_HERE
```

### NewUI (Development Only)

> **Note:** The NewUI project is for development scanning only. NewUI (v4.0) is not yet released and is not included in the v3.44 distribution.

Create `C:\Users\ejosterberg\Documents\GITprojects\TicketsCADFixes\newui-dev\newui\sonar-project.properties`:

```properties
sonar.projectKey=ticketscad-newui
sonar.projectName=TicketsCAD NewUI (v4.0)
sonar.projectVersion=4.0.0-dev

sonar.sources=.
sonar.language=php
sonar.sourceEncoding=UTF-8

sonar.exclusions=**/vendor/**,**/node_modules/**,**/tests/**,**/tools/**,**/assets/vendor/**,**/assets/js/qrcode.min.js

sonar.host.url=http://<container-ip>:9000
```

---

## Step 7: Run Your First Scan

From Git Bash on your workstation:

```bash
cd /c/Users/ejosterberg/Documents/GITprojects/TicketsCADFixes/tickets

sonar-scanner \
  -Dsonar.host.url=http://<container-ip>:9000 \
  -Dsonar.token=YOUR_TOKEN_HERE
```

This will take 2-5 minutes for the legacy codebase. When done, open the SonarQube web UI to see results.

Optionally, repeat for NewUI (development only -- not part of the released product):

```bash
cd /c/Users/ejosterberg/Documents/GITprojects/TicketsCADFixes/newui-dev/newui

sonar-scanner \
  -Dsonar.host.url=http://<container-ip>:9000 \
  -Dsonar.token=YOUR_TOKEN_HERE
```

---

## Step 8: GitHub Actions Integration (Optional)

To scan automatically on every push, add this workflow to the tickets repo.

Create `.github/workflows/sonarqube.yml`:

```yaml
name: SonarQube Scan

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  sonarqube:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - uses: sonarsource/sonarqube-scan-action@master
        env:
          SONAR_TOKEN: ${{ secrets.SONAR_TOKEN }}
          SONAR_HOST_URL: ${{ secrets.SONAR_HOST_URL }}
```

Add these GitHub secrets (Settings > Secrets > Actions):
- `SONAR_TOKEN` — The token from Step 4
- `SONAR_HOST_URL` — `http://<container-ip>:9000`

**Note:** For this to work, your Proxmox server needs to be reachable from GitHub Actions runners (public IP or VPN tunnel). If your server is behind a firewall, run scans manually from your workstation instead.

---

## Step 9: Quality Gate Configuration

Set up a quality gate that blocks PRs with security issues:

1. Go to **Quality Gates** in SonarQube
2. Create a new gate: `TicketsCAD Security Gate`
3. Add conditions:
   - **New Vulnerabilities** is greater than 0 → Fail
   - **New Security Hotspots Reviewed** is less than 100% → Fail
   - **New Bugs** is greater than 0 → Warn
4. Set as default gate for both projects

---

## What SonarQube Will Find

SonarQube's PHP analyzer checks for:

**Security (Vulnerabilities):**
- SQL Injection (all patterns)
- XSS (reflected, stored, DOM-based)
- Path Traversal
- Command Injection
- LDAP Injection
- Insecure Cryptography
- Hardcoded Credentials
- Session Fixation
- Open Redirects

**Security Hotspots (need review):**
- Use of cookies
- Use of regular expressions (ReDoS)
- CORS configuration
- File uploads
- Dynamic code execution (eval, include with variables)

**Reliability (Bugs):**
- Null pointer dereferences
- Dead code
- Logic errors
- Resource leaks

**Maintainability (Code Smells):**
- Duplicated code
- Excessive complexity
- Long methods
- Unused variables/parameters

---

## Maintenance

**Backups:** The PostgreSQL data is in a Docker volume. Back it up with:

```bash
docker exec sonarqube_db pg_dump -U sonar sonarqube > /opt/sonarqube/backup-$(date +%Y%m%d).sql
```

**Updates:** To update SonarQube:

```bash
cd /opt/sonarqube
docker compose pull
docker compose up -d
```

**Disk space:** Old scan data is kept for trend analysis. If disk fills up, go to Administration > System > Housekeeping to configure retention.

---

## Quick Reference

| Item | Value |
|------|-------|
| SonarQube URL | `http://<container-ip>:9000` |
| Default login | `admin` / `admin` (change immediately) |
| Legacy project key | `ticketscad-legacy` |
| NewUI project key | `ticketscad-newui` (development only) |
| Scanner command | `sonar-scanner -Dsonar.token=TOKEN` |
| Container resources | 2 CPU, 4-6 GB RAM, 20-40 GB disk |
| Kernel param | `vm.max_map_count=524288` |
