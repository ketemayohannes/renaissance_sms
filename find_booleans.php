<?php
$db = new PDO("sqlite:database/database.sqlite");
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $info = $db->query("PRAGMA table_info(\"$table\")")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($info as $col) {
        if (str_contains(strtolower($col['type']), 'bool') || 
            str_starts_with($col['name'], 'is_') || 
            str_starts_with($col['name'], 'has_') || 
            str_ends_with($col['name'], '_active')) {
            echo "Table: $table, Column: {$col['name']}, Type: {$col['type']}\n";
        }
    }
}
