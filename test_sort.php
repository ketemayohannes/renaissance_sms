<?php
$db = new PDO("sqlite:database/database.sqlite");
$query = "SELECT id, name, parent_term_id FROM terms ORDER BY (CASE WHEN parent_term_id IS NULL THEN 0 ELSE 1 END), parent_term_id ASC";
$rows = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Parent: " . ($row['parent_term_id'] ?? 'NULL') . "\n";
}
