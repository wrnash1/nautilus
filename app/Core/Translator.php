<?php

namespace App\Core;

/**
 * Internationalization (i18n) Translator
 * Handles multi-language support for the application
 */
class Translator
{
    private static ?Translator $instance = null;
    private string $currentLocale;
    private string $fallbackLocale = 'en';
    private array $translations = [];
    private string $translationsPath;

    private function __construct()
    {
        $this->translationsPath = BASE_PATH . '/app/Languages';
        $this->currentLocale = $this->detectLocale();
        $this->loadTranslations($this->currentLocale);
    }

    public static function getInstance(): Translator
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get translation for a key
     */
    public function get(string $key, array $replacements = [], ??string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale;

        // Parse key (format: file.key or file.nested.key)
        $parts = explode('.', $key);
        $file = array_shift($parts);

        // Load translation file if not loaded
        if (!isset($this->translations[$locale][$file])) {
            $this->loadTranslationFile($locale, $file);
        }

        // Get translation value
        $translation = $this->translations[$locale][$file] ?? [];

        foreach ($parts as $part) {
            if (isset($translation[$part])) {
                $translation = $translation[$part];
            } else {
                // Try fallback locale
                if ($locale !== $this->fallbackLocale) {
                    return $this->get($key, $replacements, $this->fallbackLocale);
                }
                return $key; // Return key if translation not found
            }
        }

        // Handle replacements
        if (is_string($translation) && !empty($replacements)) {
            foreach ($replacements as $placeholder => $value) {
                $translation = str_replace(':' . $placeholder, $value, $translation);
            }
        }

        return is_string($translation) ? $translation : $key;
    }

    /**
     * Check if translation exists
     */
    public function has(string $key, ??string $locale = null): bool
    {
        $locale = $locale ?? $this->currentLocale;

        $parts = explode('.', $key);
        $file = array_shift($parts);

        if (!isset($this->translations[$locale][$file])) {
            $this->loadTranslationFile($locale, $file);
        }

        $translation = $this->translations[$locale][$file] ?? [];

        foreach ($parts as $part) {
            if (isset($translation[$part])) {
                $translation = $translation[$part];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Set current locale
     */
    public function setLocale(string $locale): void
    {
        if ($this->isValidLocale($locale)) {
            $this->currentLocale = $locale;
            $_SESSION['locale'] = $locale;
            $this->loadTranslations($locale);
        }
    }

    /**
     * Get available locales
     */
    public function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'pt' => 'Português',
            'it' => 'Italiano',
            'ja' => '日本語',
            'zh' => '中文'
        ];
    }

    /**
     * Detect locale from various sources
     */
    private function detectLocale(): string
    {
        // 1. Check session
        if (isset($_SESSION['locale']) && $this->isValidLocale($_SESSION['locale'])) {
            return $_SESSION['locale'];
        }

        // 2. Check user preference (if logged in)
        if (isset($_SESSION['user_id'])) {
            $locale = $this->getUserLocale($_SESSION['user_id']);
            if ($locale && $this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 3. Check browser language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLocale = $this->parseBrowserLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if ($this->isValidLocale($browserLocale)) {
                return $browserLocale;
            }
        }

        // 4. Check environment variable
        if (!empty($_ENV['DEFAULT_LOCALE']) && $this->isValidLocale($_ENV['DEFAULT_LOCALE'])) {
            return $_ENV['DEFAULT_LOCALE'];
        }

        // 5. Fallback
        return $this->fallbackLocale;
    }

    /**
     * Parse browser Accept-Language header
     */
    private function parseBrowserLanguage(string $acceptLanguage): string
    {
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            $parts = explode(';', $language);
            $locale = trim($parts[0]);

            // Extract language code (e.g., 'en' from 'en-US')
            if (strpos($locale, '-') !== false) {
                $locale = substr($locale, 0, strpos($locale, '-'));
            }

            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        return $this->fallbackLocale;
    }

    /**
     * Get user's preferred locale from database
     */
    private function getUserLocale(int $userId): ?string
    {
        try {
            $db = Database::getInstance();
            $sql = "SELECT locale FROM users WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result['locale'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if locale is valid
     */
    private function isValidLocale(string $locale): bool
    {
        return array_key_exists($locale, $this->getAvailableLocales());
    }

    /**
     * Load all translations for a locale
     */
    private function loadTranslations(string $locale): void
    {
        $localePath = $this->translationsPath . '/' . $locale;

        if (!is_dir($localePath)) {
            return;
        }

        $files = glob($localePath . '/*.php');

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            $this->loadTranslationFile($locale, $filename);
        }
    }

    /**
     * Load single translation file
     */
    private function loadTranslationFile(string $locale, string $file): void
    {
        $filepath = $this->translationsPath . '/' . $locale . '/' . $file . '.php';

        if (file_exists($filepath)) {
            $this->translations[$locale][$file] = require $filepath;
        }
    }

    /**
     * Get pluralized translation
     */
    public function choice(string $key, int $count, array $replacements = []): string
    {
        $translation = $this->get($key);

        if (strpos($translation, '|') !== false) {
            $parts = explode('|', $translation);

            // Simple plural rules (can be expanded)
            if ($count === 0 && isset($parts[0])) {
                $translation = $parts[0];
            } elseif ($count === 1 && isset($parts[1])) {
                $translation = $parts[1];
            } else {
                $translation = $parts[2] ?? $parts[1] ?? $parts[0];
            }
        }

        $replacements['count'] = $count;

        foreach ($replacements as $placeholder => $value) {
            $translation = str_replace(':' . $placeholder, $value, $translation);
        }

        return $translation;
    }

    /**
     * Format date according to locale
     */
    public function formatDate(string $date, string $format = 'medium'): string
    {
        $timestamp = is_numeric($date) ? $date : strtotime($date);

        $formats = [
            'en' => [
                'short' => 'm/d/Y',
                'medium' => 'M j, Y',
                'long' => 'F j, Y',
                'full' => 'l, F j, Y'
            ],
            'es' => [
                'short' => 'd/m/Y',
                'medium' => 'j M Y',
                'long' => 'j \d\e F \d\e Y',
                'full' => 'l, j \d\e F \d\e Y'
            ],
            'fr' => [
                'short' => 'd/m/Y',
                'medium' => 'j M Y',
                'long' => 'j F Y',
                'full' => 'l j F Y'
            ]
        ];

        $localeFormats = $formats[$this->currentLocale] ?? $formats['en'];
        $dateFormat = $localeFormats[$format] ?? $localeFormats['medium'];

        return date($dateFormat, $timestamp);
    }

    /**
     * Format number according to locale
     */
    public function formatNumber(float $number, int $decimals = 0): string
    {
        $formats = [
            'en' => ['decimal' => '.', 'thousands' => ','],
            'es' => ['decimal' => ',', 'thousands' => '.'],
            'fr' => ['decimal' => ',', 'thousands' => ' '],
            'de' => ['decimal' => ',', 'thousands' => '.']
        ];

        $format = $formats[$this->currentLocale] ?? $formats['en'];

        return number_format($number, $decimals, $format['decimal'], $format['thousands']);
    }

    /**
     * Format currency according to locale
     */
    public function formatCurrency(float $amount, ??string $currency = null): string
    {
        $currency = $currency ?? $_ENV['CURRENCY'] ?? 'USD';

        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥'
        ];

        $symbol = $symbols[$currency] ?? $currency;
        $formatted = $this->formatNumber($amount, 2);

        // Currency position varies by locale
        $positions = [
            'en' => 'before',
            'es' => 'after',
            'fr' => 'after',
            'de' => 'after'
        ];

        $position = $positions[$this->currentLocale] ?? 'before';

        if ($position === 'before') {
            return $symbol . $formatted;
        } else {
            return $formatted . ' ' . $symbol;
        }
    }
}

/**
 * Helper function for translations
 */
function __($key, $replacements = []) {
    return \App\Core\Translator::getInstance()->get($key, $replacements);
}

/**
 * Helper function for pluralized translations
 */
function __n($key, $count, $replacements = []) {
    return \App\Core\Translator::getInstance()->choice($key, $count, $replacements);
}
