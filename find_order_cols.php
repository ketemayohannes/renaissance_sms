<?php
$db = new PDO("sqlite:database/database.sqlite");
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    if (str_starts_with($table, 'sqlite_')) continue;
    $info = $db->query("PRAGMA table_info(\"$table\")")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($info as $col) {
        if ($col['name'] === 'order' || $col['name'] === 'sort_order') {
            echo "Table: $table, Column: {$col['name']}\n";
        }
    }
}
