# Nautilus Setup & Installation

## Quick Start

```bash
./start-dev.sh up
```

Visit: **http://localhost:8080/**

Fill in:
- Company Name
- Email
- Password

Click "Install Now" → Wait 30 seconds → Done!

---

## Commands

```bash
./start-dev.sh up              # Start
./start-dev.sh down            # Stop
./start-dev.sh restart         # Restart + fix permissions
./start-dev.sh reset           # Fresh install (deletes all data)
./start-dev.sh fix-permissions # Fix git/storage permissions
./start-dev.sh logs            # View logs
./start-dev.sh shell           # Container shell
./start-dev.sh db              # Database access
```

---

## Access

- **Application:** http://localhost:8080/
- **phpMyAdmin:** http://localhost:8081/
- **Login:** http://localhost:8080/store/login

**Database:**
- Host: database (or localhost:3307 from host)
- User: nautilus
- Pass: nautilus123
- Database: nautilus

---

## Troubleshooting

### Git Permission Errors
```bash
./start-dev.sh fix-permissions
```

### Redirect Loop
```bash
rm .env .installed
./start-dev.sh restart
```

### Storage Not Writable
Runs automatically on startup. If needed:
```bash
./start-dev.sh fix-permissions
```

---

## What the Installer Does

### Auto-Detects:
- Docker environment
- Database credentials
- System requirements

### Auto-Creates:
- `.env` file
- 464 database tables
- Admin account
- Your company settings

### Only Asks For:
- Company name (used on receipts, emails, reports)
- Email
- Password

---

## Production Deployment

1. Copy `.env.production.example` to `.env`
2. Generate APP_KEY: `php -r "echo bin2hex(random_bytes(32));"`
3. Update database credentials in `.env`
4. Run `./start-dev.sh up`
5. Visit installer
6. Done!

---

## Multi-Tenant Support

For multiple dive shops, see [CREDENTIALS_MANAGEMENT.md](CREDENTIALS_MANAGEMENT.md).

Each tenant gets:
- Unique company name
- Separate data
- Optional dedicated database
- Own branding

---

## Need Help?

Run: `./start-dev.sh help`
