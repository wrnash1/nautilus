<?php
echo "Before autoload\n";
require_once __DIR__ . '/vendor/autoload.php';
echo "Before Database\n";
require_once __DIR__ . '/app/Core/Database.php';
echo "Before Customer\n";
require_once __DIR__ . '/app/Models/Customer.php';
echo "Before Service\n";
require_once __DIR__ . '/app/Services/Import/CustomerImportService.php';
echo "Done\n";
