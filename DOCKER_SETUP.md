# Nautilus Docker/Podman Development Environment

Complete containerized development environment for Nautilus Dive Shop Management System.

## What's Included

- **PHP 8.2** with Apache web server
- **MariaDB 11.6** database
- **phpMyAdmin** for database management
- All PHP extensions required by Nautilus
- Optimized PHP settings for migration execution (600s timeout)
- Auto-configured Apache with mod_rewrite

## Quick Start

### 1. Install Podman or Docker

**Fedora/RHEL:**
```bash
sudo dnf install podman podman-compose
```

**Ubuntu/Debian:**
```bash
sudo apt install podman podman-compose
```

**Or use Docker:**
```bash
sudo dnf install docker docker-compose
sudo systemctl start docker
sudo systemctl enable docker
```

### 2. Start Development Environment

```bash
cd /home/wrnash1/development/nautilus
./start-dev.sh
```

That's it! The script will:
- Build the containers
- Start Apache, MariaDB, and phpMyAdmin
- Show you the access URLs

### 3. Access Points

- **Nautilus Installer:** http://localhost:8080/install.php
- **Nautilus Application:** http://localhost:8080/
- **phpMyAdmin:** http://localhost:8081/
  - Username: `root`
  - Password: `Frogman09!`

## Installation Testing

### Fresh Installation Test

```bash
# Start with clean slate
./start-dev.sh reset

# Visit installer
# http://localhost:8080/install.php

# Use these database credentials:
Host:     database
Port:     3306
Database: nautilus
Username: root
Password: Frogman09!
```

### Test Migrations

The container is configured with:
- `max_execution_time = 600` (10 minutes)
- `memory_limit = 512M`
- MariaDB timeouts set to 600 seconds

This should allow all 107 migrations to complete.

## Common Commands

```bash
# Start containers
./start-dev.sh start

# Stop containers
./start-dev.sh stop

# Restart containers
./start-dev.sh restart

# Fresh install (destroys database!)
./start-dev.sh reset

# View logs
./start-dev.sh logs

# Open shell in web container
./start-dev.sh shell

# Connect to MariaDB
./start-dev.sh db

# Check container status
./start-dev.sh status

# Rebuild containers
./start-dev.sh build
```

## File Structure

```
nautilus/
├── Dockerfile                      # PHP + Apache container definition
├── docker-compose.yml              # Multi-container orchestration
├── start-dev.sh                    # Quick start script
├── .env.docker.example             # Environment variables template
├── docker/
│   └── mariadb-config.cnf         # MariaDB optimization settings
├── app/                            # Your application code
├── public/                         # Web root
│   └── install.php                # Installer
└── database/
    └── migrations/                # 107 SQL migration files
```

## How It Works

### Containers

1. **nautilus-web**
   - PHP 8.2 + Apache
   - Port 8080 → 80
   - Mounts your code directory
   - Auto-restarts on changes

2. **nautilus-db**
   - MariaDB 11.6
   - Port 3306 → 3306
   - Persistent volume for database data
   - Health checks enabled

3. **nautilus-phpmyadmin**
   - Web-based database manager
   - Port 8081 → 80
   - Pre-configured to connect to nautilus-db

### Volumes

- `nautilus-mariadb-data` - Persists database between restarts
- `nautilus-composer-cache` - Speeds up composer installs
- Code directory mounted at `/var/www/html`

### Network

All containers on `nautilus-network` bridge network, allowing them to communicate using container names (e.g., `database` hostname).

## Development Workflow

### 1. Normal Development

```bash
# Start containers
./start-dev.sh start

# Edit code in /home/wrnash1/development/nautilus/
# Changes appear immediately (code is mounted)

# View logs if needed
./start-dev.sh logs

# Stop when done
./start-dev.sh stop
```

### 2. Testing Fresh Installation

```bash
# Destroy database and start fresh
./start-dev.sh reset

# Visit installer
firefox http://localhost:8080/install.php

# Complete installation
# Database credentials: database/nautilus/root/Frogman09!

# Test the installed application
firefox http://localhost:8080/

# If you find bugs, fix code and reset again
./start-dev.sh reset
```

### 3. Debugging Migrations

```bash
# Start containers
./start-dev.sh start

# Connect to database
./start-dev.sh db

# Check migration status
SELECT * FROM migrations ORDER BY id;

# Count tables
SHOW TABLES;

# Exit database
exit

# View web server logs
./start-dev.sh logs web

# View database logs
./start-dev.sh logs database
```

### 4. Testing Different Database Versions

Edit `docker-compose.yml`:

```yaml
database:
  image: mariadb:11.6    # Change to mariadb:10.11 or mysql:8.0
```

Then rebuild:
```bash
./start-dev.sh build
./start-dev.sh reset
```

## Advantages Over Host Installation

✅ **Isolation** - Doesn't touch your system Apache/MariaDB
✅ **Reproducible** - Same environment every time
✅ **Fast Reset** - `./start-dev.sh reset` for fresh install
✅ **No Sudo** - Runs as your user
✅ **Version Testing** - Easy to test PHP 8.0, 8.1, 8.2, MariaDB 10.x, 11.x
✅ **Safe** - Can't break production
✅ **Clean** - Destroy everything with `podman-compose down -v`

## Troubleshooting

### Port Already in Use

If port 8080 or 3306 is already in use, edit `docker-compose.yml`:

```yaml
web:
  ports:
    - "8090:80"  # Changed from 8080

database:
  ports:
    - "3307:3306"  # Changed from 3306
```

### Migrations Timeout

Check PHP settings in container:
```bash
./start-dev.sh shell
php -i | grep max_execution_time
```

Should show 600 seconds.

### Database Connection Failed

Make sure you use hostname `database` not `localhost` in the installer!

The containers communicate via Docker network using container names.

### Permission Denied

```bash
# Fix file permissions
sudo chown -R $USER:$USER /home/wrnash1/development/nautilus
chmod +x start-dev.sh
```

## Data Persistence

### Keep Database Between Restarts

By default, database data persists. Use `./start-dev.sh stop` and `./start-dev.sh start` to keep data.

### Destroy Database

```bash
# Remove volumes (database data)
./start-dev.sh reset

# Or manually:
podman-compose down -v
```

## Production Deployment

These containers are for **DEVELOPMENT ONLY**. For production:

1. Use your existing production setup
2. OR create separate production Dockerfile with:
   - `APP_DEBUG=false`
   - `APP_ENV=production`
   - Proper SSL certificates
   - Security hardening

## Next Steps

1. **Test the installer:** `./start-dev.sh reset` → Visit installer
2. **Fix migration timeout issue:** All 107 migrations should complete now
3. **Test repeatedly:** Quick fresh installs for testing
4. **Deploy to customers:** Once installer works in containers

---

**Need Help?**

Run: `./start-dev.sh help`
