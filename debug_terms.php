<?php
$db = new PDO("sqlite:database/database.sqlite");
$rows = $db->query("SELECT id, name, parent_term_id FROM terms ORDER BY id LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Parent: " . ($row['parent_term_id'] ?? 'NULL') . "\n";
}
