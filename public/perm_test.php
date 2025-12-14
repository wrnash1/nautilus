<?php
// Immediate debug
$log = '/var/www/html/storage/logs/perm_debug.log';
file_put_contents($log, "User: " . get_current_user() . " (" . getmyuid() . ")\n");
file_put_contents($log, ".env exists: " . (file_exists('../.env') ? 'YES' : 'NO') . "\n", FILE_APPEND);
file_put_contents($log, ".env writable: " . (is_writable('../.env') ? 'YES' : 'NO') . "\n", FILE_APPEND);
file_put_contents($log, ".installed exists: " . (file_exists('../.installed') ? 'YES' : 'NO') . "\n", FILE_APPEND);
file_put_contents($log, ".installed writable: " . (is_writable('../.installed') ? 'YES' : 'NO') . "\n", FILE_APPEND);
file_put_contents($log, "Root writable: " . (is_writable('..') ? 'YES' : 'NO') . "\n", FILE_APPEND);
echo "Debug logged";
