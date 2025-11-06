# Nautilus Project Structure

## Directory Organization

```
nautilus/
│
├── app/                    # Application code
│   ├── Controllers/        # Request handlers
│   ├── Services/           # Business logic
│   ├── Models/             # Data models
│   ├── Middleware/         # Request middleware
│   └── Views/              # HTML templates
│
├── public/                 # Web root
│   ├── index.php           # Application entry point
│   └── assets/             # CSS, JS, images
│
├── database/               # Database files
│   ├── migrations/         # Schema migrations (*.sql)
│   └── seeders/            # Data seeders (*.sql)
│
├── routes/                 # Route definitions
│   └── web.php
│
├── scripts/                # Utility scripts
│   ├── deploy-complete.sh  # Full deployment
│   ├── migrate.sh          # Run migrations
│   ├── backup.sh           # Database backup
│   └── fresh-install.sh    # Development reset
│
├── docs/                   # Documentation
│   ├── COURSE_ENROLLMENT_WORKFLOW.md
│   ├── COURSE_ENROLLMENT_IMPLEMENTATION.md
│   └── DEPLOY_COURSE_ENROLLMENT.md
│
├── storage/                # Application storage
│   ├── logs/               # Log files
│   ├── sessions/           # Session data
│   ├── cache/              # Cache files
│   └── uploads/            # User uploads
│
├── archive/                # Archived files (not in git)
│   └── old-fixes/          # Old development scripts
│
├── vendor/                 # Composer dependencies
├── .env                    # Environment config (not in git)
├── .gitignore             # Git ignore rules
└── README.md              # Project overview
```

## Key Files

- `.env` - Environment configuration (database, API keys)
- `composer.json` - PHP dependencies
- `routes/web.php` - Application routes
- `public/index.php` - Application bootstrap

## Installation

Fresh installation:
```bash
# Navigate to https://yourdomain.com/install
# Follow the installation wizard
```

Existing installation (updates):
```bash
sudo bash scripts/deploy-complete.sh
```

## Deployment

For production deployment:
```bash
sudo bash scripts/deploy-complete.sh
```

This syncs:
- Application code
- Database migrations
- Assets (JS/CSS)
- Configuration

## Development

Run migrations:
```bash
php scripts/migrate.sh
```

Fresh install (development):
```bash
bash scripts/fresh-install.sh
```

Backup database:
```bash
bash scripts/backup.sh
```

## Documentation

All documentation is in the `docs/` directory:
- Course enrollment workflow
- Deployment guides
- API documentation
