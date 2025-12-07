# Nautilus Podman Setup - Quick Start

## Install Podman (if not already installed)

```bash
sudo dnf install podman podman-compose -y
```

## Start Nautilus Development Environment

```bash
cd /home/wrnash1/development/nautilus
./start-dev.sh
```

## Test Installation

1. Visit: **http://localhost:8080/install.php**

2. Use these database credentials:
   - **Host:** `database` (NOT localhost!)
   - **Port:** `3306`
   - **Database:** `nautilus`
   - **Username:** `root`
   - **Password:** `Frogman09!`

3. Complete the installation wizard

4. All 107 migrations should complete (600 second timeout configured)

## Test Fresh Installation Again

```bash
./start-dev.sh reset
```

This destroys the database and gives you a clean slate to test installation from scratch.

## View What's Running

```bash
./start-dev.sh status
```

## Stop Everything

```bash
./start-dev.sh stop
```

## Access phpMyAdmin

Visit: **http://localhost:8081/**
- Username: `root`
- Password: `Frogman09!`

---

**Full documentation:** See [DOCKER_SETUP.md](DOCKER_SETUP.md)
