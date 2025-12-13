<?php

use App\Models\Term;
use App\Models\AssessmentTemplate;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q2 = Term::where('name', 'LIKE', '%Quarter 2%')->first();
echo "Quarter 2 ID: " . ($q2 ? $q2->id : 'Not Found') . "\n";

if ($q2) {
    echo "\nTemplates for Q2:\n";
    $templates = AssessmentTemplate::where('term_id', $q2->id)->whereHas('assessmentType', function($q) {
        $q->where('code', '!=', 'TERM_TOTAL');
    })->get();
    
    $total = 0;
    foreach($templates as $t) {
        echo "- {$t->name}: {$t->weight}%\n";
        $total += $t->weight;
    }
    echo "Total Q2: {$total}%\n";
    
    echo "\nGlobal Templates (NULL term_id):\n";
    $globals = AssessmentTemplate::whereNull('term_id')->whereHas('assessmentType', function($q) {
        $q->where('code', '!=', 'TERM_TOTAL');
    })->get();
    
    $globalTotal = 0;
    foreach($globals as $t) {
        echo "- {$t->name}: {$t->weight}%\n";
        $globalTotal += $t->weight;
    }
    echo "Total Global: {$globalTotal}%\n";
    
    echo "\nStart Debugging Validation Logic:\n";
    echo "Combined (Q2 + Global): " . ($total + $globalTotal) . "%\n";
}
