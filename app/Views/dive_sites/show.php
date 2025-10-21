<?php
use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM dive_sites WHERE id = ?", [$id]);
$site = $stmt->fetch();

if (!$site) {
    $_SESSION['flash_error'] = 'Dive site not found';
    redirect('/dive-sites');
    exit;
}

$pageTitle = $site['name'];
$activeMenu = 'dive-sites';

ob_start();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($site['name']) ?></h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/dive-sites" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> Site Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="200">Location:</th>
                            <td><?= htmlspecialchars($site['location'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <td><?= htmlspecialchars($site['country'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Coordinates:</th>
                            <td>
                                <?php if ($site['latitude'] && $site['longitude']): ?>
                                    <?= $site['latitude'] ?>, <?= $site['longitude'] ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Max Depth:</th>
                            <td><?= $site['max_depth'] ? $site['max_depth'] . ' meters' : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Average Depth:</th>
                            <td><?= $site['average_depth'] ? $site['average_depth'] . ' meters' : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Difficulty:</th>
                            <td>
                                <span class="badge bg-<?= $site['difficulty_level'] === 'beginner' ? 'success' : ($site['difficulty_level'] === 'intermediate' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($site['difficulty_level']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Best Season:</th>
                            <td><?= htmlspecialchars($site['best_season'] ?? '-') ?></td>
                        </tr>
                    </table>

                    <?php if ($site['description']): ?>
                        <div class="mt-3">
                            <h6>Description:</h6>
                            <p><?= nl2br(htmlspecialchars($site['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-cloud-sun"></i> Weather Conditions</h5>
                </div>
                <div class="card-body">
                    <div id="weatherData">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading weather data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load weather data
fetch('/dive-sites/<?= $site['id'] ?>/weather?type=current')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('weatherData');
        if (data.success && data.data) {
            const weather = data.data;
            container.innerHTML = `
                <div class="text-center mb-3">
                    <h2>${weather.temperature}°C</h2>
                    <p>${weather.description}</p>
                </div>
                <table class="table table-sm">
                    <tr>
                        <td>Wind:</td>
                        <td>${weather.wind_speed} m/s</td>
                    </tr>
                    <tr>
                        <td>Visibility:</td>
                        <td>${weather.visibility} m</td>
                    </tr>
                    <tr>
                        <td>Wave Height:</td>
                        <td>${weather.wave_height || 'N/A'} m</td>
                    </tr>
                </table>
                ${weather.rating ? `
                    <div class="alert alert-${weather.rating === 'excellent' ? 'success' : (weather.rating === 'good' ? 'info' : 'warning')}">
                        <strong>Dive Conditions:</strong> ${weather.rating.toUpperCase()}
                    </div>
                ` : ''}
            `;
        } else {
            container.innerHTML = '<p class="text-muted">Weather data not available</p>';
        }
    })
    .catch(error => {
        document.getElementById('weatherData').innerHTML = '<p class="text-danger">Failed to load weather data</p>';
    });
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/app.php';
?>
