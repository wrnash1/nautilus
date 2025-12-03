# Contributing to Nautilus

Thank you for your interest in contributing to Nautilus! This document provides guidelines for contributing to the project.

## 🌊 Our Vision

Nautilus is an open-source dive shop management system that learns from every installation. By sharing anonymized insights across all dive shops using Nautilus, we create an AI-powered ecosystem that helps the entire diving community.

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- MariaDB 10.5+ or MySQL 8.0+
- Apache 2.4+ with mod_rewrite
- Composer

### Installation for Development

1. Clone the repository:
```bash
git clone https://github.com/yourusername/nautilus.git
cd nautilus
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Configure your database in `.env`

5. Run the web installer:
- Visit `http://localhost/nautilus/public/install.php`
- Follow the installation wizard

## 🤝 How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in [Issues](https://github.com/yourusername/nautilus/issues)
2. If not, create a new issue with:
   - Clear, descriptive title
   - Steps to reproduce
   - Expected vs actual behavior
   - Your environment (OS, PHP version, database version)
   - Screenshots if applicable

### Suggesting Features

1. Check [existing feature requests](https://github.com/yourusername/nautilus/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)
2. Create a new issue with the `enhancement` label
3. Describe:
   - The problem you're trying to solve
   - Your proposed solution
   - How it benefits dive shops
   - Any implementation ideas

### Code Contributions

1. **Fork the repository**

2. **Create a feature branch**
```bash
git checkout -b feature/your-feature-name
```

3. **Follow coding standards**
   - PSR-12 coding style
   - Meaningful variable and function names
   - Comments for complex logic
   - Security-first approach (no SQL injection, XSS, etc.)

4. **Write tests** (when applicable)
   - PHPUnit tests for new features
   - Test edge cases

5. **Commit your changes**
```bash
git add .
git commit -m "Add feature: brief description"
```

Follow commit message conventions:
- `Add: new feature`
- `Fix: bug description`
- `Update: improvement description`
- `Refactor: code restructuring`
- `Docs: documentation changes`

6. **Push to your fork**
```bash
git push origin feature/your-feature-name
```

7. **Create a Pull Request**
   - Clear description of changes
   - Reference any related issues
   - Include screenshots for UI changes
   - Ensure all tests pass

## 📋 Code Style Guidelines

### PHP

```php
<?php

namespace App\Controllers\Example;

use App\Core\Database;

class ExampleController
{
    /**
     * Show example page
     */
    public function index()
    {
        // Always check permissions
        if (!hasPermission('example.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        // Sanitize user input
        $search = sanitizeInput($_GET['search'] ?? '');

        // Use prepared statements
        $results = Database::fetchAll(
            "SELECT * FROM table WHERE column LIKE ?",
            ["%{$search}%"]
        );

        require __DIR__ . '/../../Views/example/index.php';
    }
}
```

### Security Checklist

- ✅ Always use prepared statements for database queries
- ✅ Sanitize user input with `sanitizeInput()`
- ✅ Validate permissions with `hasPermission()`
- ✅ Use CSRF tokens for forms
- ✅ Escape output with `htmlspecialchars()` in views
- ✅ Never store passwords in plain text
- ✅ Use HTTPS in production

### Database Migrations

- Make migrations idempotent (can run multiple times safely)
- Use MariaDB-compatible syntax
- Add proper indexes for performance
- Include rollback capability when possible

## 🔬 Testing

Run tests before submitting:

```bash
composer test
```

## 🌍 AI & Privacy

### What Gets Shared

Nautilus automatically shares **anonymized** insights to help all dive shops:

✅ **Shared (Anonymized)**:
- Product category trends
- Course enrollment patterns
- Seasonal demand patterns
- Equipment rental patterns
- Operational best practices

❌ **Never Shared**:
- Customer names, emails, phone numbers
- Actual prices or revenue
- Personal data
- Business-specific information

### Contributing to AI Models

AI models are stored and versioned in the GitHub repository under `/ai/models/`. Contributions to improve prediction accuracy are welcome!

## 📜 License

By contributing to Nautilus, you agree that your contributions will be licensed under the MIT License.

## 🎯 Priority Areas

We especially welcome contributions in:

1. **Multi-language support** - Translations for dive shops worldwide
2. **Mobile optimization** - PWA improvements
3. **Integration plugins** - Third-party service integrations
4. **AI model improvements** - Better predictions and insights
5. **Accessibility** - WCAG 2.1 AA compliance
6. **Performance** - Database optimization, caching

## 💬 Community

- **GitHub Discussions**: Ask questions, share ideas
- **GitHub Issues**: Bug reports and feature requests
- **Pull Requests**: Code contributions

## 🙏 Recognition

Contributors are recognized in:
- `CONTRIBUTORS.md` file
- GitHub contributors page
- Release notes

Thank you for helping make Nautilus better for dive shops everywhere! 🌊🤿
