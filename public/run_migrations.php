<?php
/**
 * Migration runner - executes SQL files via MariaDB CLI
 * Shows user-friendly progress instead of technical details
 */
session_start();

// Check if this is a quick install from streamlined installer
// Fix: Use isset() for both to avoid undefined index warnings and ensure boolean result
$isQuickInstall = isset($_GET['quick_install']) && isset($_SESSION['install_data']);

// Get DB config from session or env
if ($isQuickInstall) {
    $config = $_SESSION['install_data'];
} else {
    $config = $_SESSION["db_config"] ?? [
        "host" => getenv("DB_HOST") ?: "database",
        "port" => getenv("DB_PORT") ?: "3306",
        "database" => getenv("DB_DATABASE") ?: "nautilus",
        "username" => getenv("DB_USERNAME") ?: "root",
        "password" => getenv("DB_PASSWORD") ?: "Frogman09!"
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installing Nautilus Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; overflow-x: hidden; font-family: 'Inter', sans-serif; height: 100vh; }
        .installer-container { display: flex; height: 100vh; width: 100vw; }
        .side-panel { flex: 1; position: relative; overflow: hidden; display: none; }
        .side-panel img { width: 100%; height: 100%; object-fit: cover; }
        .main-panel { flex: 0 0 100%; padding: 40px; display: flex; flex-direction: column; justify-content: center; background: white; z-index: 10; }
        
        @media (min-width: 992px) {
            .side-panel { display: block; flex: 0 0 25%; }
            .main-panel { flex: 0 0 50%; box-shadow: 0 0 50px rgba(0,0,0,0.1); }
        }

        h1 { font-family: 'Cinzel', serif; color: #1a365d; font-weight: 600; margin-bottom: 20px; }
        .overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.5)); }
        
        .progress { height: 20px; border-radius: 10px; background-color: #edf2f7; overflow: hidden; }
        .progress-bar { background: linear-gradient(90deg, #4299e1 0%, #3182ce 100%); transition: width 0.3s ease; }
        .status-text { color: #4a5568; font-weight: 500; min-height: 24px; }
        .stage-title { color: #2d3748; font-weight: 600; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="installer-container">
        <!-- Left Panel: Diver -->
        <div class="side-panel">
            <img src="/images/diver.png" alt="Diver">
            <div class="overlay"></div>
        </div>

        <!-- Center Panel: Progress -->
        <div class="main-panel">
            <div style="max-width: 500px; margin: 0 auto; width: 100%; text-align: center;">
                <h1 class="display-6">INITIALIZING</h1>
                <p class="stage-title">Setting up your dive shop database...</p>

                <div class="spinner-border text-primary mb-4" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                
                <div class="progress mb-3 shadow-sm">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%"></div>
                </div>
                
                <div id="status" class="status-text">Starting installation...</div>
            </div>
        </div>

        <!-- Right Panel: Coral -->
        <div class="side-panel">
            <img src="/images/coral.png" alt="Coral">
            <div class="overlay"></div>
        </div>
    </div>

    <script>
    let processed = 0;
    let total = 0;
    let startTime = Date.now();

    async function runMigrations() {
        try {
            const response = await fetch('run_migrations_backend.php');
            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            while (true) {
                const {done, value} = await reader.read();
                if (done) break;

                const text = decoder.decode(value);
                const lines = text.split("\n");

                for (const line of lines) {
                    if (line.startsWith("TOTAL:")) {
                        total = parseInt(line.split(":")[1]);
                    } else if (line.startsWith("PROGRESS:")) {
                        processed = parseInt(line.split(":")[1]);
                        const percent = Math.round((processed / total) * 100);
                        document.getElementById("progressBar").style.width = percent + "%";
                        
                        const elapsed = Math.round((Date.now() - startTime) / 1000);
                        document.getElementById("status").textContent = 
                            `Processing table ${processed} of ${total} (${elapsed}s)`;
                    } else if (line.startsWith("COMPLETE")) {
                        document.getElementById("status").innerHTML =
                            '<strong class="text-success">✓ Setup Complete! Launching...</strong>';
                        const isQuickInstall = <?= json_encode($isQuickInstall) ?>;
                        if (isQuickInstall) {
                            setTimeout(() => window.location = '/', 2000);
                        } else {
                            setTimeout(() => window.location = 'install.php?step=4', 2000);
                        }
                    }
                }
            }
        } catch (error) {
            document.getElementById("status").innerHTML = 
                '<span class="text-danger">Error: '+error.message+'</span>';
        }
    }

    runMigrations();
    </script>
</body>
</html>
