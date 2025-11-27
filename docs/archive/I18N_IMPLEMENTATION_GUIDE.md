# Multi-Language Support (i18n) Implementation Guide

## Overview
Nautilus includes a comprehensive internationalization (i18n) system that supports 8 languages out of the box, with easy extensibility for additional languages.

## Supported Languages
- 🇺🇸 English (en) - Default
- 🇪🇸 Spanish (es)
- 🇫🇷 French (fr)
- 🇩🇪 German (de)
- 🇵🇹 Portuguese (pt)
- 🇮🇹 Italian (it)
- 🇯🇵 Japanese (ja)
- 🇨🇳 Chinese (zh)

## Core Components

### 1. Translator Class
**Location:** `app/Core/Translator.php`

The main translation engine that handles:
- Locale detection (session, user preference, browser, environment)
- Translation file loading and caching
- Fallback locale support
- Pluralization
- Date, number, and currency formatting

### 2. Translation Files
**Location:** `app/Languages/{locale}/`

Structure:
```
app/Languages/
├── en/
│   ├── messages.php      # General UI messages
│   ├── auth.php          # Authentication strings
│   ├── validation.php    # Form validation messages
│   └── common.php        # Business terminology
├── es/ (Spanish)
├── fr/ (French)
└── ... (other locales)
```

### 3. Helper Functions

#### `__($key, $replacements = [])`
Get a translation with optional placeholder replacement.

```php
// Simple translation
echo __('messages.welcome');
// Output: "Welcome to Nautilus"

// With replacements
echo __('messages.success.created', ['item' => 'Customer']);
// Output: "Customer created successfully"
```

#### `__n($key, $count, $replacements = [])`
Get a pluralized translation.

```php
// Pluralization
echo __n('messages.items', 0);  // "no items"
echo __n('messages.items', 1);  // "1 item"
echo __n('messages.items', 5);  // "5 items"
```

## Usage Examples

### In Views
```php
<h1><?= __('messages.dashboard') ?></h1>
<p><?= __('messages.welcome') ?></p>

<button><?= __('messages.save') ?></button>
<button><?= __('messages.cancel') ?></button>
```

### In Controllers
```php
use App\Core\Translator;

$translator = Translator::getInstance();
$message = $translator->get('messages.success.saved');
$_SESSION['success'] = $message;
```

### Dynamic Locale Switching
```php
// Get current locale
$currentLocale = $translator->getLocale(); // e.g., "en"

// Change locale
$translator->setLocale('es');

// Get available locales
$locales = $translator->getAvailableLocales();
// Returns: ['en' => 'English', 'es' => 'Español', ...]
```

### Nested Translation Keys
Use dot notation to access nested translations:

```php
// File: app/Languages/en/messages.php
return [
    'success' => [
        'created' => ':item created successfully',
        'updated' => ':item updated successfully'
    ]
];

// Usage:
echo __('messages.success.created', ['item' => 'Product']);
// Output: "Product created successfully"
```

### Placeholder Replacement
Placeholders use the `:key` format:

```php
echo __('messages.showing_results', [
    'start' => 1,
    'end' => 10,
    'total' => 100
]);
// Output: "Showing 1 to 10 of 100 results"
```

## Locale Detection Priority

The system detects locale in the following order:

1. **Session** - `$_SESSION['locale']`
2. **User Preference** - Database `users.locale` column
3. **Browser Language** - `HTTP_ACCEPT_LANGUAGE` header
4. **Environment** - `.env` `DEFAULT_LOCALE` setting
5. **Fallback** - Default `en`

## Date & Number Formatting

### Date Formatting
```php
$translator->formatDate('2024-12-25', 'long');
// en: "December 25, 2024"
// es: "25 de diciembre de 2024"
// fr: "25 décembre 2024"
```

Format options: `short`, `medium`, `long`, `full`

### Number Formatting
```php
$translator->formatNumber(1234.56, 2);
// en: "1,234.56"
// es: "1.234,56"
// fr: "1 234,56"
// de: "1.234,56"
```

### Currency Formatting
```php
$translator->formatCurrency(99.99, 'USD');
// en: "$99.99"
// es: "99,99 $"
// fr: "99,99 $"
```

## Language Switcher Component

### Usage in Views
```php
<!-- Include the language switcher in your layout -->
<?php require BASE_PATH . '/app/Views/components/language_switcher.php'; ?>
```

The component provides:
- Dropdown menu with all available languages
- Current language indicator
- AJAX-based language switching
- Automatic page reload after language change

### Already Integrated
The language switcher is already integrated in:
- `app/Views/layouts/app.php` (main navbar)
- User settings page

## Adding a New Language

### Step 1: Create Translation Files
Create a new directory for the locale:
```bash
mkdir app/Languages/de  # For German
```

### Step 2: Copy Translation Files
Copy and translate files from an existing locale:
```bash
cp app/Languages/en/messages.php app/Languages/de/messages.php
cp app/Languages/en/auth.php app/Languages/de/auth.php
cp app/Languages/en/validation.php app/Languages/de/validation.php
cp app/Languages/en/common.php app/Languages/de/common.php
```

### Step 3: Translate Content
Edit each file and translate the strings:
```php
// app/Languages/de/messages.php
return [
    'welcome' => 'Willkommen bei Nautilus',
    'dashboard' => 'Instrumententafel',
    'save' => 'Speichern',
    'cancel' => 'Abbrechen',
    // ... etc
];
```

### Step 4: Register Locale
Add the new locale to `Translator.php`:
```php
public function getAvailableLocales(): array
{
    return [
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',  // Add this
        // ... other locales
    ];
}
```

## Database Schema

### Users Table
```sql
ALTER TABLE users
ADD COLUMN locale VARCHAR(5) DEFAULT 'en' AFTER email,
ADD INDEX idx_locale (locale);
```

Migration file: `database/migrations/022_add_locale_to_users.sql`

## API Endpoints

### Change Locale
**POST** `/settings/change-locale`

Request body:
```json
{
    "locale": "es"
}
```

Response:
```json
{
    "success": true,
    "locale": "es",
    "message": "Language changed successfully"
}
```

## Best Practices

### 1. Always Use Translation Keys
❌ **Don't:**
```php
echo "Welcome to Nautilus";
```

✅ **Do:**
```php
echo __('messages.welcome');
```

### 2. Use Descriptive Keys
❌ **Don't:**
```php
'msg1' => 'Welcome'
'msg2' => 'Goodbye'
```

✅ **Do:**
```php
'welcome' => 'Welcome'
'goodbye' => 'Goodbye'
```

### 3. Group Related Translations
```php
'customer' => [
    'create' => 'Create Customer',
    'update' => 'Update Customer',
    'delete' => 'Delete Customer',
    'list' => 'Customer List'
]
```

### 4. Use Placeholders for Dynamic Content
```php
'items_selected' => ':count items selected'
'user_greeting' => 'Hello, :name!'
```

### 5. Provide Context in Comments
```php
// Translation files
return [
    // Navigation menu items
    'dashboard' => 'Dashboard',
    'reports' => 'Reports',

    // Button labels
    'save' => 'Save',
    'cancel' => 'Cancel',
];
```

## Pluralization Rules

Define plural forms using pipe `|` separator:

```php
'items' => 'no items|one item|:count items'
```

Usage:
```php
echo __n('messages.items', 0);  // "no items"
echo __n('messages.items', 1);  // "one item"
echo __n('messages.items', 5);  // "5 items"
```

## Testing Translations

### Manual Testing
1. Change language via the switcher in the navbar
2. Navigate through different pages
3. Verify all text is translated correctly
4. Check form validation messages
5. Test error messages and success notifications

### Unit Testing
```php
use App\Core\Translator;

public function testTranslation()
{
    $translator = Translator::getInstance();
    $translator->setLocale('es');

    $result = $translator->get('messages.welcome');
    $this->assertEquals('Bienvenido a Nautilus', $result);
}
```

## Translation Coverage

### Fully Translated
- ✅ General UI messages (`messages.php`)
- ✅ Authentication (`auth.php`)
- ✅ Validation messages (`validation.php`)
- ✅ Business terminology (`common.php`)

### Locales with Complete Translations
- ✅ English (en) - 100%
- ✅ Spanish (es) - Core translations
- ✅ French (fr) - Core translations
- ⚠️ German (de) - Placeholder only
- ⚠️ Portuguese (pt) - Placeholder only
- ⚠️ Italian (it) - Placeholder only
- ⚠️ Japanese (ja) - Placeholder only
- ⚠️ Chinese (zh) - Placeholder only

## Performance Considerations

1. **Translation Caching**
   - Translations are cached in memory after first load
   - No database queries for translations

2. **Lazy Loading**
   - Translation files are only loaded when needed
   - Only the current locale files are loaded

3. **Session Storage**
   - Current locale stored in session
   - Reduces lookup overhead on each request

## Troubleshooting

### Translation Not Found
If a translation key is not found, the system will:
1. Try the fallback locale (English)
2. Return the key itself if still not found

### Missing Locale Files
If a locale directory doesn't exist:
- System falls back to default locale
- No errors thrown

### Cache Issues
If translations don't update after changes:
1. Clear PHP opcache if enabled
2. Restart PHP-FPM if using it
3. Clear session data

## Future Enhancements

Potential additions:
- [ ] Right-to-left (RTL) language support (Arabic, Hebrew)
- [ ] Translation management UI for non-technical users
- [ ] Export/import translations to/from CSV/Excel
- [ ] Translation progress tracking
- [ ] Automatic translation via API (Google Translate, DeepL)
- [ ] Language-specific content (CMS)

## Resources

### Translation Services
- [DeepL](https://www.deepl.com/) - High-quality AI translation
- [Google Translate](https://translate.google.com/)
- [Professional Translation Services](https://www.gengo.com/)

### Language Codes
- ISO 639-1 two-letter codes used throughout
- Full list: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes

---

**Version:** 1.0
**Last Updated:** December 2024
**Contact:** For translation contributions, contact the development team.
