<?php
$db = new PDO("sqlite:database/database.sqlite");
$sql = $db->query("SELECT sql FROM sqlite_master WHERE name='grade_components'")->fetchColumn();
file_put_contents('schema_output.txt', $sql);
echo "Schema written to schema_output.txt\n";
