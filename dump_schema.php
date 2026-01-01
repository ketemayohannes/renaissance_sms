<?php
$db = new PDO("sqlite:database/database.sqlite");
$tables = $db->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tables as $table) {
    echo "--- TABLE: {$table['name']} ---\n";
    echo $table['sql'] . "\n\n";
}
