<?php

namespace App\Services\DiveSites;

use App\Core\Database;
use PDO;
use App\Core\Logger;
use App\Core\Cache;
use Exception;

/**
 * Weather Service
 * Integrates with OpenWeatherMap API for dive site weather tracking
 */
class WeatherService
{
    private PDO $db;
    private Logger $logger;
    private Cache $cache;
    private string $apiKey;
    private string $apiUrl = 'https://api.openweathermap.org/data/2.5';

    public function __construct()
    {
        $this->db = Database::getPdo();
        $this->logger = new Logger();
        $this->cache = Cache::getInstance();
        $this->apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? '';
    }

    /**
     * Get current weather for dive site
     */
    public function getCurrentWeather(int $siteId): ?array
    {
        try {
            // Get dive site
            $site = $this->getDiveSite($siteId);

            if (!$site || empty($site['latitude']) || empty($site['longitude'])) {
                return null;
            }

            // Check cache first (15 minute TTL)
            $cacheKey = "weather:current:{$siteId}";
            $cached = $this->cache->get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }

            // Fetch from API
            $url = sprintf(
                '%s/weather?lat=%s&lon=%s&appid=%s&units=metric',
                $this->apiUrl,
                $site['latitude'],
                $site['longitude'],
                $this->apiKey
            );

            $response = $this->makeApiRequest($url);

            if (!$response) {
                return null;
            }

            // Parse and format response
            $weather = $this->formatCurrentWeather($response);

            // Cache the result
            $this->cache->set($cacheKey, $weather, 900); // 15 minutes

            // Save to database
            $this->saveConditions($siteId, $weather);

            return $weather;

        } catch (Exception $e) {
            $this->logger->error('Failed to get current weather', [
                'site_id' => $siteId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get weather forecast for dive site
     */
    public function getForecast(int $siteId, int $days = 7): ?array
    {
        try {
            // Get dive site
            $site = $this->getDiveSite($siteId);

            if (!$site || empty($site['latitude']) || empty($site['longitude'])) {
                return null;
            }

            // Check cache first (1 hour TTL)
            $cacheKey = "weather:forecast:{$siteId}:{$days}";
            $cached = $this->cache->get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }

            // Fetch from API
            $url = sprintf(
                '%s/forecast/daily?lat=%s&lon=%s&cnt=%d&appid=%s&units=metric',
                $this->apiUrl,
                $site['latitude'],
                $site['longitude'],
                $days,
                $this->apiKey
            );

            $response = $this->makeApiRequest($url);

            if (!$response) {
                return null;
            }

            // Parse and format response
            $forecast = $this->formatForecast($response);

            // Cache the result
            $this->cache->set($cacheKey, $forecast, 3600); // 1 hour

            return $forecast;

        } catch (Exception $e) {
            $this->logger->error('Failed to get forecast', [
                'site_id' => $siteId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get marine/ocean data (if available)
     */
    public function getMarineData(int $siteId): ?array
    {
        try {
            $site = $this->getDiveSite($siteId);

            if (!$site || empty($site['latitude']) || empty($site['longitude'])) {
                return null;
            }

            // Check cache
            $cacheKey = "weather:marine:{$siteId}";
            $cached = $this->cache->get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }

            // Note: Marine data requires separate API or service
            // This is a placeholder for future implementation
            $marineData = [
                'wave_height' => null,
                'wave_period' => null,
                'water_temperature' => null,
                'current_speed' => null,
                'current_direction' => null,
                'visibility' => null,
                'tide' => null
            ];

            $this->cache->set($cacheKey, $marineData, 1800); // 30 minutes

            return $marineData;

        } catch (Exception $e) {
            $this->logger->error('Failed to get marine data', [
                'site_id' => $siteId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get historical conditions for dive site
     */
    public function getHistoricalConditions(int $siteId, string $startDate, string $endDate): array
    {
        $sql = "SELECT * FROM dive_site_conditions
                WHERE dive_site_id = ?
                AND recorded_at BETWEEN ? AND ?
                ORDER BY recorded_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siteId, $startDate, $endDate]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Save weather conditions to database
     */
    public function saveConditions(int $siteId, array $weather): void
    {
        try {
            $sql = "INSERT INTO dive_site_conditions
                    (dive_site_id, temperature, feels_like, weather_condition, weather_description,
                     wind_speed, wind_direction, humidity, pressure, visibility, clouds, recorded_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $siteId,
                $weather['temperature'],
                $weather['feels_like'],
                $weather['condition'],
                $weather['description'],
                $weather['wind_speed'],
                $weather['wind_direction'],
                $weather['humidity'],
                $weather['pressure'],
                $weather['visibility'],
                $weather['clouds']
            ]);

        } catch (Exception $e) {
            $this->logger->error('Failed to save weather conditions', [
                'site_id' => $siteId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update all dive site weather conditions
     */
    public function updateAllSites(): array
    {
        $results = [
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Get all active dive sites with coordinates
        $sql = "SELECT id, name FROM dive_sites
                WHERE is_active = 1
                AND latitude IS NOT NULL
                AND longitude IS NOT NULL";

        $stmt = $this->db->query($sql);
        $sites = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($sites as $site) {
            $weather = $this->getCurrentWeather($site['id']);

            if ($weather) {
                $results['updated']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to update {$site['name']}";
            }

            // Rate limiting - don't overwhelm API
            usleep(200000); // 200ms delay between requests
        }

        $this->logger->info('Weather update completed', $results);

        return $results;
    }

    /**
     * Get dive site conditions for trip planning
     */
    public function getSiteConditionsForDate(int $siteId, string $date): array
    {
        $targetDate = strtotime($date);
        $daysAway = (int)(($targetDate - time()) / 86400);

        // If within 7 days, use forecast
        if ($daysAway <= 7 && $daysAway >= 0) {
            $forecast = $this->getForecast($siteId, $daysAway + 1);
            if ($forecast && isset($forecast['days'][$daysAway])) {
                return $forecast['days'][$daysAway];
            }
        }

        // Otherwise, use historical average
        $year = date('Y', $targetDate);
        $monthDay = date('m-d', $targetDate);

        return $this->getHistoricalAverage($siteId, $monthDay);
    }

    /**
     * Get historical average for a specific date
     */
    private function getHistoricalAverage(int $siteId, string $monthDay): array
    {
        $sql = "SELECT
                    AVG(temperature) as avg_temp,
                    AVG(wind_speed) as avg_wind,
                    AVG(visibility) as avg_visibility
                FROM dive_site_conditions
                WHERE dive_site_id = ?
                AND DATE_FORMAT(recorded_at, '%m-%d') = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siteId, $monthDay]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'temperature' => $result['avg_temp'] ? round($result['avg_temp'], 1) : null,
            'wind_speed' => $result['avg_wind'] ? round($result['avg_wind'], 1) : null,
            'visibility' => $result['avg_visibility'] ? round($result['avg_visibility']) : null,
            'is_historical' => true
        ];
    }

    /**
     * Make API request
     */
    private function makeApiRequest(string $url): ?array
    {
        if (empty($this->apiKey)) {
            $this->logger->warning('OpenWeatherMap API key not configured');
            return null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->logger->error('Weather API request failed', [
                'url' => $url,
                'http_code' => $httpCode
            ]);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Format current weather response
     */
    private function formatCurrentWeather(array $data): array
    {
        return [
            'temperature' => $data['main']['temp'] ?? null,
            'feels_like' => $data['main']['feels_like'] ?? null,
            'temp_min' => $data['main']['temp_min'] ?? null,
            'temp_max' => $data['main']['temp_max'] ?? null,
            'pressure' => $data['main']['pressure'] ?? null,
            'humidity' => $data['main']['humidity'] ?? null,
            'visibility' => isset($data['visibility']) ? $data['visibility'] / 1000 : null, // Convert to km
            'wind_speed' => $data['wind']['speed'] ?? null,
            'wind_direction' => $data['wind']['deg'] ?? null,
            'clouds' => $data['clouds']['all'] ?? null,
            'condition' => $data['weather'][0]['main'] ?? null,
            'description' => $data['weather'][0]['description'] ?? null,
            'icon' => $data['weather'][0]['icon'] ?? null,
            'sunrise' => isset($data['sys']['sunrise']) ? date('H:i', $data['sys']['sunrise']) : null,
            'sunset' => isset($data['sys']['sunset']) ? date('H:i', $data['sys']['sunset']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Format forecast response
     */
    private function formatForecast(array $data): array
    {
        $forecast = [
            'location' => $data['city']['name'] ?? 'Unknown',
            'days' => []
        ];

        if (isset($data['list'])) {
            foreach ($data['list'] as $day) {
                $forecast['days'][] = [
                    'date' => date('Y-m-d', $day['dt']),
                    'temperature' => [
                        'day' => $day['temp']['day'] ?? null,
                        'min' => $day['temp']['min'] ?? null,
                        'max' => $day['temp']['max'] ?? null,
                        'night' => $day['temp']['night'] ?? null,
                        'eve' => $day['temp']['eve'] ?? null,
                        'morn' => $day['temp']['morn'] ?? null
                    ],
                    'feels_like' => [
                        'day' => $day['feels_like']['day'] ?? null,
                        'night' => $day['feels_like']['night'] ?? null
                    ],
                    'pressure' => $day['pressure'] ?? null,
                    'humidity' => $day['humidity'] ?? null,
                    'wind_speed' => $day['speed'] ?? null,
                    'wind_direction' => $day['deg'] ?? null,
                    'clouds' => $day['clouds'] ?? null,
                    'condition' => $day['weather'][0]['main'] ?? null,
                    'description' => $day['weather'][0]['description'] ?? null,
                    'icon' => $day['weather'][0]['icon'] ?? null,
                    'rain' => $day['rain'] ?? 0,
                    'pop' => isset($day['pop']) ? round($day['pop'] * 100) : null // Probability of precipitation
                ];
            }
        }

        return $forecast;
    }

    /**
     * Get dive site by ID
     */
    private function getDiveSite(int $siteId): ?array
    {
        $sql = "SELECT * FROM dive_sites WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siteId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get dive conditions rating (0-10)
     */
    public function getDiveConditionsRating(array $weather): array
    {
        $rating = 10;
        $factors = [];

        // Temperature (optimal 24-29°C)
        if (isset($weather['temperature'])) {
            if ($weather['temperature'] < 20) {
                $rating -= 2;
                $factors[] = 'Cool water temperature';
            } elseif ($weather['temperature'] > 32) {
                $rating -= 1;
                $factors[] = 'Very warm conditions';
            }
        }

        // Wind speed (optimal < 5 m/s)
        if (isset($weather['wind_speed'])) {
            if ($weather['wind_speed'] > 10) {
                $rating -= 3;
                $factors[] = 'High wind speeds';
            } elseif ($weather['wind_speed'] > 7) {
                $rating -= 2;
                $factors[] = 'Moderate winds';
            }
        }

        // Visibility
        if (isset($weather['visibility'])) {
            if ($weather['visibility'] < 5) {
                $rating -= 2;
                $factors[] = 'Reduced visibility';
            }
        }

        // Cloud cover
        if (isset($weather['clouds'])) {
            if ($weather['clouds'] > 80) {
                $rating -= 1;
                $factors[] = 'Overcast skies';
            }
        }

        // Weather condition
        if (isset($weather['condition'])) {
            if (in_array($weather['condition'], ['Thunderstorm', 'Tornado'])) {
                $rating -= 5;
                $factors[] = 'Dangerous weather conditions';
            } elseif ($weather['condition'] === 'Rain') {
                $rating -= 2;
                $factors[] = 'Rainy conditions';
            }
        }

        $rating = max(0, min(10, $rating));

        return [
            'rating' => $rating,
            'factors' => $factors,
            'recommendation' => $this->getRecommendation($rating)
        ];
    }

    /**
     * Get diving recommendation based on rating
     */
    private function getRecommendation(int $rating): string
    {
        if ($rating >= 8) {
            return 'Excellent conditions for diving';
        } elseif ($rating >= 6) {
            return 'Good conditions for diving';
        } elseif ($rating >= 4) {
            return 'Fair conditions - check with dive master';
        } else {
            return 'Poor conditions - consider rescheduling';
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'API key not configured'
            ];
        }

        try {
            // Test with a known location (London)
            $url = sprintf(
                '%s/weather?lat=51.5074&lon=-0.1278&appid=%s',
                $this->apiUrl,
                $this->apiKey
            );

            $response = $this->makeApiRequest($url);

            if ($response) {
                return [
                    'success' => true,
                    'message' => 'API connection successful',
                    'location' => $response['name'] ?? 'Unknown'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch data from API'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
