<?php
$tmpFile = __DIR__ . '/../test_log_browser.txt';
$storageFile = __DIR__ . '/../storage/logs/test_log_browser.txt';

echo "Testing write permissions...<br>";

// Test /tmp
if (file_put_contents($tmpFile, "Test from browser\n")) {
    echo "Successfully wrote to $tmpFile<br>";
} else {
    echo "Failed to write to $tmpFile<br>";
    echo "Last error: " . print_r(error_get_last(), true) . "<br>";
}

// Test storage/logs
if (file_put_contents($storageFile, "Test from browser\n")) {
    echo "Successfully wrote to $storageFile<br>";
} else {
    echo "Failed to write to $storageFile<br>";
    echo "Last error: " . print_r(error_get_last(), true) . "<br>";
}

echo "Done.";
