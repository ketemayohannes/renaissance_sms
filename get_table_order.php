<?php
$db = new PDO("sqlite:database/database.sqlite");
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations' AND name != 'sessions' AND name != 'cache' AND name != 'jobs'")->fetchAll(PDO::FETCH_COLUMN);

$dependencies = [];
foreach ($tables as $table) {
    preg_match_all('/REFERENCES\s+"?([^"\s\()]+)"?/i', $db->query("SELECT sql FROM sqlite_master WHERE name='$table'")->fetchColumn(), $matches);
    $dependencies[$table] = array_unique($matches[1]);
}

// Simple topological sort
$sorted = [];
$visited = [];

function t_sort($node, &$dependencies, &$sorted, &$visited) {
    if (isset($visited[$node])) return;
    $visited[$node] = true;
    foreach ($dependencies[$node] ?? [] as $dep) {
        if ($dep !== $node && isset($dependencies[$dep])) { // Avoid self-ref and missing tables
            t_sort($dep, $dependencies, $sorted, $visited);
        }
    }
    $sorted[] = $node;
}

foreach ($tables as $table) {
    t_sort($table, $dependencies, $sorted, $visited);
}

echo "ORDERED_TABLES:\n" . implode("\n", $sorted) . "\n";
