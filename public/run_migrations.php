<?php
/**
 * Migration runner - executes SQL files via MariaDB CLI
 * Shows user-friendly progress instead of technical details
 */
session_start();

// Get DB config from session or env
$config = $_SESSION["db_config"] ?? [
    "host" => getenv("DB_HOST") ?: "database",
    "port" => getenv("DB_PORT") ?: "3306", 
    "database" => getenv("DB_DATABASE") ?: "nautilus",
    "username" => getenv("DB_USERNAME") ?: "root",
    "password" => getenv("DB_PASSWORD") ?: "Frogman09!"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installing Nautilus Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .install-card { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .progress { height: 30px; }
        .spinner-border { width: 3rem; height: 3rem; }
        #status { font-size: 18px; color: #667eea; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-card text-center">
            <h2 class="mb-4">🌊 Installing Nautilus Database</h2>
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="progress mb-3">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%">0%</div>
            </div>
            <div id="status">Initializing...</div>
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
                        document.getElementById("progressBar").textContent = percent + "%";
                        
                        const elapsed = Math.round((Date.now() - startTime) / 1000);
                        document.getElementById("status").textContent = 
                            `Processing migration ${processed} of ${total} (${elapsed}s elapsed)`;
                    } else if (line.startsWith("COMPLETE")) {
                        document.getElementById("status").innerHTML = 
                            '<strong class="text-success">✓ Database installed successfully!</strong><br>Redirecting...';
                        setTimeout(() => window.location = 'install.php?step=4', 2000);
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
