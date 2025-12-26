<?php
$file = 'reset_debug.txt';
if (file_exists($file)) {
    echo "<h1>Log Content</h1><pre>";
    echo htmlspecialchars(file_get_contents($file));
    echo "</pre>";
} else {
    echo "<h1>Log File Not Found</h1>";
    echo "Path: " . realpath(__DIR__) . '/' . $file;
}

echo "<h2>Auth Log</h2><pre>";
$authLog = __DIR__ . '/../storage/logs/debug_auth.log';
if (file_exists($authLog)) {
    echo htmlspecialchars(file_get_contents($authLog));
} else {
    echo "Auth log not found at $authLog";
}
echo "</pre>";
