# 🌊 Nautilus Universal Installation Guide

Complete installation guide for any environment: Linux (all distributions), Docker, Cloud platforms, and various database/web server combinations.

---

## 📋 Table of Contents

1. [System Requirements](#system-requirements)
2. [Quick Install (Recommended)](#quick-install)
3. [Database Options](#database-options)
4. [Web Server Options](#web-server-options)
5. [Cloud Deployments](#cloud-deployments)
6. [Manual Installation](#manual-installation)

---

## 🖥️ System Requirements

### Minimum Requirements
- **CPU**: 2+ cores
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 10GB available space
- **Network**: Internet connection for installation

### Software Requirements

**PHP** (any of these versions):
- PHP 8.1+ ✅
- PHP 8.2+ ✅
- PHP 8.3+ ✅

**Required PHP Extensions:**
- pdo, pdo_mysql (or pdo_pgsql for PostgreSQL)
- mbstring, json, curl, openssl
- gd, zip, xml

**Database** (choose one):
- MySQL 8.0+ ✅
- MySQL 5.7+ ⚠️ (legacy support)
- MariaDB 10.6+ ✅
- MariaDB 10.3+ ⚠️ (legacy support)
- PostgreSQL 13+ ✅ (beta support)

**Web Server** (choose one):
- Apache 2.4+ with mod_rewrite ✅
- Nginx 1.18+ ✅
- Caddy 2.0+ ✅

**Operating System** (any of these):
- RHEL/CentOS 7, 8, 9
- Fedora 35+
- Rocky Linux 8, 9
- AlmaLinux 8, 9
- Debian 10, 11, 12
- Ubuntu 20.04, 22.04, 24.04
- Arch Linux
- openSUSE Leap 15+
- Alpine Linux 3.15+

---

## ⚡ Quick Install (Automated - Recommended)

### Universal Auto-Installer

Works on **all Linux distributions** and auto-detects your system:

```bash
cd /path/to/nautilus

# Default: MariaDB + Apache
sudo bash scripts/universal-install.sh

# With PostgreSQL
sudo bash scripts/universal-install.sh --database=postgresql

# With MySQL
sudo bash scripts/universal-install.sh --database=mysql

# View all options
sudo bash scripts/universal-install.sh --help
```

**✅ Automated Installation Includes:**
- Operating system and package manager detection
- PHP 8.1+ installation and configuration
- Database installation: MySQL, MariaDB, **or PostgreSQL** 
- Apache web server with SSL virtual hosts
- SELinux configuration (RHEL-based systems)
- Firewall rules
- File permissions

**Supported Databases (Automated):**
- ✅ **MariaDB** (default, recommended)
- ✅ **MySQL 8.0+** (fully supported)
- ✅ **PostgreSQL 13+** (fully supported)

**Supported Web Servers:**
- ✅ **Apache 2.4+** (automated install)
- 📖 **Nginx 1.18+** (manual setup guide below)
- 📖 **Caddy 2.0+** (manual setup guide below)

**Then complete installation:**
- Open browser: `https://nautilus.local/install/`
- Follow web installer to create admin account
- In database step, set `.env` file with:
  - For MySQL/MariaDB: `DB_CONNECTION=mysql`
  - For PostgreSQL: `DB_CONNECTION=pgsql`
- All 110+ database migrations install automatically

---

## 💾 Database Options

Nautilus supports multiple database systems:

### Option 1: MySQL (Most Common)

**Installation:**

```bash
# RHEL/Fedora/Rocky/Alma
sudo dnf install mysql-server
sudo systemctl enable --now mysqld

# Debian/Ubuntu
sudo apt install mysql-server
sudo systemctl enable --now mysql

# Arch
sudo pacman -S mysql
sudo systemctl enable --now mysqld
```

**Configuration:**
```bash
# Secure installation
sudo mysql_secure_installation

# Create database
mysql -u root -p
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Option 2: MariaDB (Recommended for Linux)

**Installation:**

```bash
# RHEL/Fedora/Rocky/Alma
sudo dnf install mariadb-server
sudo systemctl enable --now mariadb

# Debian/Ubuntu
sudo apt install mariadb-server
sudo systemctl enable --now mariadb

# Arch
sudo pacman -S mariadb
sudo mysql_install_db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
sudo systemctl enable --now mariadb
```

**Configuration:** (same as MySQL above)

### Option 3: PostgreSQL (Beta Support)

**Installation:**

```bash
# RHEL/Fedora/Rocky/Alma
sudo dnf install postgresql-server postgresql-contrib
sudo postgresql-setup --initdb
sudo systemctl enable --now postgresql

# Debian/Ubuntu
sudo apt install postgresql postgresql-contrib
sudo systemctl enable --now postgresql

# Arch
sudo pacman -S postgresql
sudo -u postgres initdb -D /var/lib/postgres/data
sudo systemctl enable --now postgresql
```

**Configuration:**
```bash
sudo -u postgres psql

CREATE DATABASE nautilus WITH ENCODING 'UTF8';
CREATE USER nautilus WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE nautilus TO nautilus;
\q
```

**Update .env for PostgreSQL:**
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=nautilus
DB_USERNAME=nautilus
DB_PASSWORD=secure_password
```

**Install PHP PostgreSQL extension:**
```bash
# RHEL/Fedora
sudo dnf install php-pgsql

# Debian/Ubuntu
sudo apt install php-pgsql

# Arch
sudo pacman -S php-pgsql
```

---

## 🌐 Web Server Options

### Option 1: Apache (Recommended)

**Installed by universal-install.sh automatically**

**Manual Install:**
```bash
# RHEL/Fedora/Rocky/Alma
sudo dnf install httpd mod_ssl
sudo systemctl enable --now httpd

# Debian/Ubuntu
sudo apt install apache2
sudo a2enmod rewrite ssl
sudo systemctl enable --now apache2

# Arch
sudo pacman -S apache
sudo systemctl enable --now httpd
```

**Virtual Host Configuration:**
```apache
<VirtualHost *:443>
    ServerName nautilus.yourdomain.com
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile /path/to/cert.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Option 2: Nginx

**Installation:**
```bash
# RHEL/Fedora/Rocky/Alma
sudo dnf install nginx php-fpm
sudo systemctl enable --now nginx php-fpm

# Debian/Ubuntu
sudo apt install nginx php-fpm
sudo systemctl enable --now nginx php8.1-fpm

# Arch
sudo pacman -S nginx php-fpm
sudo systemctl enable --now nginx php-fpm
```

**Configuration** (`/etc/nginx/sites-available/nautilus`):
```nginx
server {
    listen 443 ssl http2;
    server_name nautilus.yourdomain.com;
    root /var/www/html/nautilus/public;

    ssl_certificate /path/to/cert.crt;
    ssl_certificate_key /path/to/private.key;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/nautilus /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Option 3: Caddy (Easiest SSL)

**Installation:**
```bash
# Universal (official Caddy install)
sudo curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
sudo curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/cfg/setup/bash.deb.sh' | sudo bash
sudo apt install caddy
```

**Caddyfile:**
```caddyfile
nautilus.yourdomain.com {
    root * /var/www/html/nautilus/public
    encode gzip
    php_fastcgi unix//var/run/php/php8.1-fpm.sock
    file_server
}
```

**Caddy auto-manages SSL certificates via Let's Encrypt!**

---



## ☁️ Cloud Deployments

### AWS EC2

**1. Launch EC2 Instance:**
- AMI: Amazon Linux 2023, Ubuntu 22.04, or RHEL 9
- Instance Type: t3.medium or larger
- Security Group: Open ports 80, 443, 22

**2. Connect and Install:**
```bash
ssh -i your-key.pem ec2-user@your-instance-ip

# Update system
sudo yum update -y  # or: sudo apt update && sudo apt upgrade -y

# Clone and install
git clone https://github.com/your-repo/nautilus.git
cd nautilus
sudo bash scripts/universal-install.sh
```

**3. Configure Domain:**
- Point your domain A record to EC2 Elastic IP
- Update virtual host with your domain name
- Install Let's Encrypt certificate:
  ```bash
  sudo dnf install certbot python3-certbot-apache
  sudo certbot --apache -d nautilus.yourdomain.com
  ```

### Google Cloud Platform (GCE)

**1. Create VM Instance:**
```bash
gcloud compute instances create nautilus-instance \
    --image-family=ubuntu-2204-lts \
    --image-project=ubuntu-os-cloud \
    --machine-type=e2-medium \
    --zone=us-central1-a \
    --tags=http-server,https-server
```

**2. SSH and Install:**
```bash
gcloud compute ssh nautilus-instance

sudo apt update && sudo apt upgrade -y
git clone https://github.com/your-repo/nautilus.git
cd nautilus
sudo bash scripts/universal-install.sh
```

**3. Configure Firewall:**
```bash
gcloud compute firewall-rules create allow-http --allow tcp:80
gcloud compute firewall-rules create allow-https --allow tcp:443
```

### Microsoft Azure

**1. Create VM:**
```bash
az vm create \
  --resource-group nautilus-rg \
  --name nautilus-vm \
  --image UbuntuLTS \
  --size Standard_B2s \
  --admin-username azureuser \
  --generate-ssh-keys
```

**2. Open Ports:**
```bash
az vm open-port --port 80 --resource-group nautilus-rg --name nautilus-vm
az vm open-port --port 443 --resource-group nautilus-rg --name nautilus-vm
```

**3. SSH and Install:**
```bash
ssh azureuser@your-vm-ip
sudo apt update && sudo apt upgrade -y
git clone https://github.com/your-repo/nautilus.git
cd nautilus
sudo bash scripts/universal-install.sh
```

### DigitalOcean Droplet

**1. Create Droplet:**
- Choose Ubuntu 22.04, Fedora 39, or Debian 12
- Select plan (Basic $12/mo minimum recommended)
- Add SSH key
- Enable backups

**2. SSH and Install:**
```bash
ssh root@your-droplet-ip

apt update && apt upgrade -y  # or: dnf update -y
git clone https://github.com/your-repo/nautilus.git
cd nautilus
bash scripts/universal-install.sh
```

**3. Point Domain:**
- Add A record in DigitalOcean DNS or your registrar
- Install SSL: `sudo certbot --apache -d nautilus.yourdomain.com`

---

## 🔧 Manual Installation

For full control or non-standard setups:

### Step 1: Install Dependencies

**Choose your OS and follow instructions:**

<details>
<summary><b>Fedora / RHEL / Rocky / AlmaLinux</b></summary>

```bash
# Install web server
sudo dnf install httpd mod_ssl

# Install PHP and extensions
sudo dnf install php php-mysqlnd php-mbstring php-json php-curl php-gd php-zip php-xml

# Install database
sudo dnf install mariadb-server

# Enable services
sudo systemctl enable --now httpd mariadb
```
</details>

<details>
<summary><b>Debian / Ubuntu</b></summary>

```bash
# Install web server
sudo apt install apache2

# Install PHP and extensions
sudo apt install php libapache2-mod-php php-mysql php-mbstring php-json php-curl php-gd php-zip php-xml

# Install database
sudo apt install mariadb-server

# Enable modules
sudo a2enmod rewrite ssl

# Enable services
sudo systemctl enable --now apache2 mariadb
```
</details>

<details>
<summary><b>Arch Linux</b></summary>

```bash
# Install packages
sudo pacman -S apache php php-apache mariadb

# Configure PHP
sudo nano /etc/php/php.ini
# Uncomment: extension=pdo_mysql, extension=mysqli, extension=gd, extension=zip

# Enable services
sudo systemctl enable --now httpd mariadb
```
</details>

### Step 2: Deploy Application Files

```bash
# Copy files
sudo cp -r /path/to/nautilus /var/www/html/

# Set ownership
sudo chown -R apache:apache /var/www/html/nautilus  # or www-data on Debian

# Set permissions
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

### Step 3: Create Database

```bash
mysql -u root -p

CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 4: Configure Web Server

See [Web Server Options](#web-server-options) above for Apache, Nginx, or Caddy configuration.

### Step 5: Run Web Installer

Navigate to: `https://your-domain.com/install/`

---

## 🔐 Security Hardening

### Production Checklist

```bash
# 1. Enable HTTPS (Let's Encrypt)
sudo certbot --apache -d yourdomain.com

# 2. Enable SELinux (RHEL-based)
sudo setenforce 1
sudo setsebool -P httpd_can_network_connect_db on

# 3. Configure Firewall
sudo firewall-cmd --permanent --add-service=http --add-service=https
sudo firewall-cmd --reload

# 4. Secure file permissions
sudo chmod 600 /var/www/html/nautilus/.env
sudo chown apache:apache /var/www/html/nautilus/.env

# 5. Disable debug mode
# Edit .env: APP_DEBUG=false
```

---

## ✅ Post-Installation

### Verify Installation

```bash
# Check web server
sudo systemctl status httpd  # or nginx

# Check database
sudo systemctl status mariadb  # or mysql or postgresql

# Check PHP
php -v
php -m | grep -E "pdo|mysql|mbstring"

# Count database tables
mysql -u nautilus -p nautilus -e "SHOW TABLES;" | wc -l
# Should show 80+ tables
```

### Default Credentials

- **Email**: admin@nautilus.local
- **Password**: admin123
- ⚠️ **Change immediately after first login!**

---

## 🆘 Troubleshooting

See **COMPLETE_GUIDE.md** for comprehensive troubleshooting guide.

**Common Issues:**
- Can't connect to database → Check credentials in `.env`
- 500 Error → Check Apache error log: `sudo tail -f /var/log/httpd/error_log`
- Permission denied → Run: `sudo chown -R apache:apache /var/www/html/nautilus`
- SELinux blocking → Check: `sudo ausearch -m avc -ts recent | grep denied`

---

**Last Updated**: November 27, 2025  
**Version**: 2.0 Universal  
**Supported Platforms**: 15+ Linux distributions, Docker, 4+ cloud platforms
