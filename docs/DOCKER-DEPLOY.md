# TicketsCAD Docker Deployment Guide

Deploy TicketsCAD in minutes using Docker. Works on Windows, Linux, and macOS.

---

## Quick Start (2 minutes)

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/Mac) or Docker Engine (Linux)
- Docker Compose (included with Docker Desktop)

### Deploy

```bash
# Download the compose file
curl -LO https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml

# Start TicketsCAD
docker compose up -d

# Wait ~30 seconds for initialization, then open:
# http://localhost:8080
```

**Default login:** `admin` / `admin` (change immediately after first login!)

That's it. TicketsCAD is running.

---

## Detailed Setup

### Option A: Using the Pre-Built Image (Recommended)

The official image is hosted on GitHub Container Registry.

**1. Create a project directory:**

```bash
mkdir ticketscad && cd ticketscad
```

**2. Create `docker-compose.yml`:**

```yaml
services:
  web:
    image: ghcr.io/openises/ticketscad:latest
    container_name: ticketscad
    ports:
      - "8080:80"
    environment:
      DB_HOST: db
      DB_USER: tickets
      DB_PASS: your_secure_db_password
      DB_NAME: tickets
      ADMIN_USER: admin
      ADMIN_PASS: your_secure_admin_password
      ADMIN_NAME: Your Name
      AUTO_INSTALL: "true"
    depends_on:
      db:
        condition: service_healthy
    restart: unless-stopped

  db:
    image: mariadb:10.11
    container_name: ticketscad_db
    environment:
      MARIADB_ROOT_PASSWORD: your_secure_root_password
      MARIADB_DATABASE: tickets
      MARIADB_USER: tickets
      MARIADB_PASSWORD: your_secure_db_password
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s
    restart: unless-stopped

volumes:
  db_data:
```

**3. Start the stack:**

```bash
docker compose up -d
```

**4. Open your browser:**

```
http://localhost:8080
```

### Option B: Build from Source

```bash
# Clone the repository
git clone https://github.com/openises/tickets.git
cd tickets

# Build and start
docker compose up -d --build
```

---

## Windows-Specific Instructions

### Install Docker Desktop

1. Download Docker Desktop from https://www.docker.com/products/docker-desktop/
2. Run the installer — accept the defaults
3. **Important:** When prompted, enable "Use WSL 2 based engine" (recommended)
4. Restart your computer if prompted
5. Start Docker Desktop from the Start menu
6. Wait for the Docker icon in the system tray to show "Docker Desktop is running"

### Deploy from PowerShell

```powershell
# Create project directory
mkdir C:\TicketsCAD
cd C:\TicketsCAD

# Download compose file
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml" -OutFile "docker-compose.yml"

# Set a custom admin password (optional)
$env:ADMIN_PASS = "MySecurePassword123"

# Start TicketsCAD
docker compose up -d

# Check status
docker compose ps

# View logs if needed
docker compose logs -f web
```

### Deploy from Git Bash

```bash
mkdir /c/TicketsCAD && cd /c/TicketsCAD
curl -LO https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml
ADMIN_PASS="MySecurePassword123" docker compose up -d
```

### Troubleshooting Windows

- **"Docker daemon not running"** — Start Docker Desktop from the Start menu
- **Port 8080 in use** — Change the port: `WEB_PORT=8888 docker compose up -d`
- **WSL error** — Run `wsl --install` in PowerShell as Administrator, then restart

---

## Linux-Specific Instructions

### Install Docker (Debian/Ubuntu)

```bash
# Install Docker
curl -fsSL https://get.docker.com | sudo sh

# Add your user to the docker group (log out and back in after)
sudo usermod -aG docker $USER

# Start Docker
sudo systemctl enable docker
sudo systemctl start docker

# Verify
docker --version
docker compose version
```

### Install Docker (RHEL/CentOS/Fedora)

```bash
sudo dnf install docker-ce docker-ce-cli containerd.io docker-compose-plugin
sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -aG docker $USER
```

### Deploy

```bash
mkdir ~/ticketscad && cd ~/ticketscad
curl -LO https://raw.githubusercontent.com/openises/tickets/main/docker-compose.yml

# Set secure passwords
export DB_PASS="$(openssl rand -base64 16)"
export ADMIN_PASS="$(openssl rand -base64 12)"
echo "DB Password: $DB_PASS"
echo "Admin Password: $ADMIN_PASS"

docker compose up -d
```

### Production Deployment (Linux)

For production servers, add HTTPS with a reverse proxy:

```bash
# Install nginx
sudo apt install nginx certbot python3-certbot-nginx

# Create nginx config
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

# Enable and get SSL certificate
sudo ln -sf /etc/nginx/sites-available/ticketscad /etc/nginx/sites-enabled/
sudo certbot --nginx -d your-domain.com
sudo systemctl restart nginx
```

---

## Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `db` | Database hostname |
| `DB_USER` | `tickets` | Database username |
| `DB_PASS` | `tickets` | Database password |
| `DB_NAME` | `tickets` | Database name |
| `DB_PREFIX` | _(empty)_ | Table name prefix |
| `ADMIN_USER` | `admin` | Initial admin username |
| `ADMIN_PASS` | `admin` | Initial admin password |
| `ADMIN_NAME` | `Super Administrator` | Admin display name |
| `AUTO_INSTALL` | `true` | Auto-create tables on first run |
| `WEB_PORT` | `8080` | Host port for web interface |
| `DB_ROOT_PASS` | `ticketscad_root_2026` | MariaDB root password |

### Custom Port

```bash
WEB_PORT=9090 docker compose up -d
# Access at http://localhost:9090
```

### Persistent Data

All important data is stored in Docker volumes that survive container restarts and upgrades:

| Volume | Contents | Purpose |
|--------|----------|---------|
| `db_data` | MariaDB database files | All incidents, users, settings, history |
| `uploads` | File attachments | Photos, documents uploaded by users |
| `tile_cache` | Map tile cache | Cached OSM tiles for faster map loading |
| `config` | MySQL config file | Database connection settings |

**Your data is safe.** Restarting or upgrading containers does not lose data. Only `docker compose down -v` (with the `-v` flag) removes volumes — never run this on production.

### Backup

```bash
# Backup database
docker exec ticketscad_db mariadb-dump -u tickets -pYOUR_DB_PASS tickets > backup_$(date +%Y%m%d).sql

# Restore
docker exec -i ticketscad_db mariadb -u tickets -pYOUR_DB_PASS tickets < backup_20260401.sql
```

### Upgrade

```bash
cd ~/ticketscad

# Pull latest image
docker compose pull

# Restart with new image (data is preserved)
docker compose up -d

# The installer will auto-detect version mismatch and prompt for upgrade
```

---

## Verify Installation

After starting, verify everything works:

```bash
# Check containers are running
docker compose ps

# Check web server responds
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/

# Check database connection
docker exec ticketscad_db mariadb -u tickets -ptickets -e "SHOW TABLES FROM tickets;" 2>/dev/null | head -5

# View web server logs
docker compose logs web | tail -20
```

---

## Troubleshooting

### Container won't start
```bash
docker compose logs web    # Check web server logs
docker compose logs db     # Check database logs
```

### Database connection errors
```bash
# Verify database is healthy
docker compose ps
# Should show "healthy" for db container

# Test database connectivity
docker exec ticketscad_db mariadb -u tickets -ptickets -e "SELECT 1;"
```

### Reset everything
```bash
# WARNING: This deletes all data!
docker compose down -v
docker compose up -d
```

### White screen / PHP errors
```bash
# Check PHP error log
docker exec ticketscad tail -50 /var/log/php_errors.log
```

---

## System Requirements

| Resource | Minimum | Recommended |
|----------|---------|-------------|
| CPU | 1 core | 2 cores |
| RAM | 512 MB | 1 GB |
| Disk | 500 MB | 2 GB |
| Docker | 20.10+ | Latest |
| OS | Any Docker-supported OS | Debian 12+, Ubuntu 22.04+, Windows 10+ |
