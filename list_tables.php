<?php
$db = new PDO("sqlite:database/database.sqlite");
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
sort($tables);
echo implode("\n", $tables) . "\n";
