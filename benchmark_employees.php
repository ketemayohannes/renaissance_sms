<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;

$start = microtime(true);

$employees = Employee::with('user')->paginate(15);
$stats = [
    'total' => Employee::count(),
    'active' => Employee::active()->count(),
    'teachers' => Employee::teachers()->count(),
];

$end = microtime(true);
echo "Execution Time: " . ($end - $start) . "s\n";
echo "Employee Count: " . Employee::count() . "\n";
echo "User Count: " . \App\Models\User::count() . "\n";
