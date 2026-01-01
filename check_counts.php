<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = ['employees', 'users', 'sessions', 'activity_log', 'student_term_records', 'student_marks'];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "$table: " . DB::table($table)->count() . "\n";
    } else {
        echo "$table: [Not Found]\n";
    }
}
