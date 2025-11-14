<?php

namespace App\Services\Payment;

use App\Core\TenantDatabase;
use App\Core\Cache;

/**
 * Multi-Currency and Tax Management Service
 *
 * Features:
 * - Real-time exchange rate updates
 * - Multi-currency pricing
 * - Automatic tax calculation (US sales tax, VAT, GST)
 * - Tax nexus management
 * - Currency conversion
 * - Localized pricing display
 */
class MultiCurrencyService
{
    private Cache $cache;
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
        $this->cache = Cache::getInstance();
    }

    /**
     * Get current exchange rate
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return (float) $cached;
        }

        // Get from database
        $rate = TenantDatabase::fetchOneTenant(
            "SELECT rate FROM exchange_rates
             WHERE from_currency = ? AND to_currency = ?
             AND updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
             ORDER BY updated_at DESC LIMIT 1",
            [$fromCurrency, $toCurrency]
        );

        if ($rate) {
            $this->cache->set($cacheKey, $rate['rate'], self::CACHE_TTL);
            return (float) $rate['rate'];
        }

        // Fetch from external API
        $rate = $this->fetchExchangeRate($fromCurrency, $toCurrency);

        if ($rate) {
            TenantDatabase::insertTenant('exchange_rates', [
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'rate' => $rate,
                'source' => 'api',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->cache->set($cacheKey, $rate, self::CACHE_TTL);
            return $rate;
        }

        return 1.0;
    }

    /**
     * Convert amount between currencies
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
        return round($amount * $rate, 2);
    }

    /**
     * Get price in customer's preferred currency
     */
    public function getLocalizedPrice(int $productId, string $currency): array
    {
        $product = TenantDatabase::fetchOneTenant(
            "SELECT price, currency FROM products WHERE id = ?",
            [$productId]
        );

        $baseCurrency = $product['currency'] ?? 'USD';
        $basePrice = $product['price'];

        if ($currency === $baseCurrency) {
            return [
                'amount' => $basePrice,
                'currency' => $currency,
                'formatted' => $this->formatCurrency($basePrice, $currency)
            ];
        }

        $convertedPrice = $this->convert($basePrice, $baseCurrency, $currency);

        return [
            'amount' => $convertedPrice,
            'currency' => $currency,
            'formatted' => $this->formatCurrency($convertedPrice, $currency),
            'original_amount' => $basePrice,
            'original_currency' => $baseCurrency
        ];
    }

    /**
     * Calculate tax for transaction
     */
    public function calculateTax(array $items, array $billingAddress, ?array $shippingAddress = null): array
    {
        $address = $shippingAddress ?? $billingAddress;
        $country = $address['country'] ?? 'US';
        $state = $address['state'] ?? '';
        $zipCode = $address['zip_code'] ?? '';

        $subtotal = array_sum(array_column($items, 'total'));
        $taxableAmount = 0;
        $taxBreakdown = [];

        // Determine tax rates based on nexus
        $taxRates = $this->getTaxRates($country, $state, $zipCode);

        foreach ($items as $item) {
            $product = TenantDatabase::fetchOneTenant(
                "SELECT tax_code, is_taxable FROM products WHERE id = ?",
                [$item['product_id']]
            );

            if ($product && $product['is_taxable']) {
                $itemTotal = $item['total'];
                $taxableAmount += $itemTotal;

                foreach ($taxRates as $rate) {
                    $taxAmount = $itemTotal * ($rate['rate'] / 100);

                    if (!isset($taxBreakdown[$rate['name']])) {
                        $taxBreakdown[$rate['name']] = [
                            'rate' => $rate['rate'],
                            'amount' => 0,
                            'type' => $rate['type']
                        ];
                    }

                    $taxBreakdown[$rate['name']]['amount'] += $taxAmount;
                }
            }
        }

        $totalTax = array_sum(array_column($taxBreakdown, 'amount'));

        return [
            'subtotal' => $subtotal,
            'taxable_amount' => $taxableAmount,
            'total_tax' => round($totalTax, 2),
            'tax_breakdown' => $taxBreakdown,
            'total' => round($subtotal + $totalTax, 2),
            'tax_jurisdiction' => [
                'country' => $country,
                'state' => $state,
                'zip_code' => $zipCode
            ]
        ];
    }

    /**
     * Get applicable tax rates
     */
    private function getTaxRates(string $country, string $state, string $zipCode): array
    {
        $rates = [];

        if ($country === 'US') {
            // Check if we have nexus in this state
            $nexus = TenantDatabase::fetchOneTenant(
                "SELECT * FROM tax_nexus WHERE country = ? AND state = ? AND is_active = 1",
                [$country, $state]
            );

            if ($nexus) {
                // Get state tax rate
                $stateTax = TenantDatabase::fetchOneTenant(
                    "SELECT * FROM tax_rates WHERE country = ? AND state = ? AND zip_code IS NULL",
                    [$country, $state]
                );

                if ($stateTax) {
                    $rates[] = [
                        'name' => "{$state} State Tax",
                        'rate' => $stateTax['rate'],
                        'type' => 'state'
                    ];
                }

                // Get local tax rate
                $localTax = TenantDatabase::fetchOneTenant(
                    "SELECT * FROM tax_rates WHERE country = ? AND state = ? AND zip_code = ?",
                    [$country, $state, substr($zipCode, 0, 5)]
                );

                if ($localTax) {
                    $rates[] = [
                        'name' => "Local Tax",
                        'rate' => $localTax['rate'],
                        'type' => 'local'
                    ];
                }
            }
        } elseif (in_array($country, ['GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'IE'])) {
            // EU VAT
            $vatRate = TenantDatabase::fetchOneTenant(
                "SELECT * FROM tax_rates WHERE country = ? AND tax_type = 'vat'",
                [$country]
            );

            if ($vatRate) {
                $rates[] = [
                    'name' => 'VAT',
                    'rate' => $vatRate['rate'],
                    'type' => 'vat'
                ];
            }
        } elseif ($country === 'CA') {
            // Canadian GST/HST/PST
            $gst = TenantDatabase::fetchOneTenant(
                "SELECT * FROM tax_rates WHERE country = ? AND state = ? AND tax_type = 'gst'",
                [$country, $state]
            );

            if ($gst) {
                $rates[] = [
                    'name' => 'GST/HST',
                    'rate' => $gst['rate'],
                    'type' => 'gst'
                ];
            }

            $pst = TenantDatabase::fetchOneTenant(
                "SELECT * FROM tax_rates WHERE country = ? AND state = ? AND tax_type = 'pst'",
                [$country, $state]
            );

            if ($pst) {
                $rates[] = [
                    'name' => 'PST',
                    'rate' => $pst['rate'],
                    'type' => 'pst'
                ];
            }
        } elseif ($country === 'AU') {
            // Australian GST
            $rates[] = [
                'name' => 'GST',
                'rate' => 10.0,
                'type' => 'gst'
            ];
        }

        return $rates;
    }

    /**
     * Add or update tax nexus
     */
    public function addTaxNexus(string $country, string $state, array $data): bool
    {
        $existing = TenantDatabase::fetchOneTenant(
            "SELECT id FROM tax_nexus WHERE country = ? AND state = ?",
            [$country, $state]
        );

        $nexusData = [
            'country' => $country,
            'state' => $state,
            'is_active' => $data['is_active'] ?? true,
            'effective_date' => $data['effective_date'] ?? date('Y-m-d'),
            'registration_number' => $data['registration_number'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            TenantDatabase::updateTenant('tax_nexus', $nexusData, 'id = ?', [$existing['id']]);
        } else {
            $nexusData['created_at'] = date('Y-m-d H:i:s');
            TenantDatabase::insertTenant('tax_nexus', $nexusData);
        }

        return true;
    }

    /**
     * Update tax rate
     */
    public function updateTaxRate(string $country, string $state, float $rate, string $taxType = 'sales', ?string $zipCode = null): bool
    {
        $existing = TenantDatabase::fetchOneTenant(
            "SELECT id FROM tax_rates WHERE country = ? AND state = ? AND " .
            ($zipCode ? "zip_code = ?" : "zip_code IS NULL"),
            $zipCode ? [$country, $state, $zipCode] : [$country, $state]
        );

        $data = [
            'country' => $country,
            'state' => $state,
            'zip_code' => $zipCode,
            'rate' => $rate,
            'tax_type' => $taxType,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            TenantDatabase::updateTenant('tax_rates', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            TenantDatabase::insertTenant('tax_rates', $data);
        }

        return true;
    }

    /**
     * Format currency for display
     */
    public function formatCurrency(float $amount, string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'INR' => '₹',
            'MXN' => 'MX$'
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        $decimals = in_array($currency, ['JPY', 'KRW']) ? 0 : 2;

        return $symbol . number_format($amount, $decimals);
    }

    /**
     * Update exchange rates from external API
     */
    public function updateExchangeRates(string $baseCurrency = 'USD'): array
    {
        $currencies = ['EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'MXN'];
        $updated = [];

        foreach ($currencies as $currency) {
            if ($currency === $baseCurrency) continue;

            $rate = $this->fetchExchangeRate($baseCurrency, $currency);

            if ($rate) {
                TenantDatabase::insertTenant('exchange_rates', [
                    'from_currency' => $baseCurrency,
                    'to_currency' => $currency,
                    'rate' => $rate,
                    'source' => 'api',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Also store inverse
                TenantDatabase::insertTenant('exchange_rates', [
                    'from_currency' => $currency,
                    'to_currency' => $baseCurrency,
                    'rate' => 1 / $rate,
                    'source' => 'api',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $updated[] = "{$baseCurrency}/{$currency}";

                // Clear cache
                $this->cache->delete("exchange_rate_{$baseCurrency}_{$currency}");
                $this->cache->delete("exchange_rate_{$currency}_{$baseCurrency}");
            }
        }

        return $updated;
    }

    /**
     * Fetch exchange rate from external API
     */
    private function fetchExchangeRate(string $from, string $to): ?float
    {
        // Using exchangerate-api.com (free tier)
        $apiUrl = "https://api.exchangerate-api.com/v4/latest/{$from}";

        try {
            $response = @file_get_contents($apiUrl);

            if ($response) {
                $data = json_decode($response, true);

                if (isset($data['rates'][$to])) {
                    return (float) $data['rates'][$to];
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail
            error_log("Exchange rate fetch failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$'],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥'],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => 'MX$']
        ];
    }

    /**
     * Detect customer's currency based on IP/location
     */
    public function detectCurrency(string $ipAddress): string
    {
        // Simple IP-based detection (in production, use a proper geolocation service)
        $countryToCurrency = [
            'US' => 'USD',
            'GB' => 'GBP',
            'DE' => 'EUR',
            'FR' => 'EUR',
            'IT' => 'EUR',
            'ES' => 'EUR',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'JP' => 'JPY',
            'CN' => 'CNY',
            'IN' => 'INR',
            'MX' => 'MXN'
        ];

        // For now, return USD as default
        return 'USD';
    }
}
