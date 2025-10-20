<?php

namespace App\Controllers;

use App\Services\DiveSites\WeatherService;

class DiveSitesController
{
    private WeatherService $weatherService;

    public function __construct()
    {
        $this->weatherService = new WeatherService();
    }

    /**
     * List all dive sites
     */
    public function index(): void
    {
        require_once __DIR__ . '/../Views/dive_sites/index.php';
    }

    /**
     * Show individual dive site with weather
     */
    public function show(int $id): void
    {
        require_once __DIR__ . '/../Views/dive_sites/show.php';
    }

    /**
     * Create new dive site
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        require_once __DIR__ . '/../Views/dive_sites/create.php';
    }

    /**
     * Store new dive site
     */
    private function store(): void
    {
        try {
            $db = \App\Core\Database::getInstance();

            $sql = "INSERT INTO dive_sites
                    (name, location, country, latitude, longitude, max_depth, average_depth,
                     difficulty_level, description, best_season, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute([
                $_POST['name'],
                $_POST['location'] ?? null,
                $_POST['country'] ?? null,
                $_POST['latitude'] ?? null,
                $_POST['longitude'] ?? null,
                $_POST['max_depth'] ?? null,
                $_POST['average_depth'] ?? null,
                $_POST['difficulty_level'] ?? 'beginner',
                $_POST['description'] ?? null,
                $_POST['best_season'] ?? null
            ]);

            $siteId = (int)$db->getConnection()->lastInsertId();

            $_SESSION['success'] = 'Dive site added successfully';
            header('Location: /dive-sites/' . $siteId);
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to add dive site: ' . $e->getMessage();
            header('Location: /dive-sites/create');
            exit;
        }
    }

    /**
     * Get weather data via AJAX
     */
    public function weather(int $id): void
    {
        header('Content-Type: application/json');

        $type = $_GET['type'] ?? 'current';

        try {
            switch ($type) {
                case 'current':
                    $data = $this->weatherService->getCurrentWeather($id);
                    if ($data) {
                        $data['rating'] = $this->weatherService->getDiveConditionsRating($data);
                    }
                    break;

                case 'forecast':
                    $days = (int)($_GET['days'] ?? 7);
                    $data = $this->weatherService->getForecast($id, $days);
                    break;

                case 'marine':
                    $data = $this->weatherService->getMarineData($id);
                    break;

                default:
                    throw new \Exception('Invalid weather type');
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update weather for all sites (can be called by cron)
     */
    public function updateAllWeather(): void
    {
        $results = $this->weatherService->updateAllSites();

        echo json_encode([
            'success' => true,
            'results' => $results
        ]);
    }
}
