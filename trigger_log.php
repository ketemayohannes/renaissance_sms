<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Helpers\NumberFormatter;

echo "Triggering NumberFormatter for 86.5...\n";
$val = 86.5;
$formatted = NumberFormatter::format($val);
echo "Result: $formatted\n";

echo "Check laravel log for trace.\n";
