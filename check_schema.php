<?php
$db = new PDO("sqlite:database/database.sqlite");
echo "TABLE: grade_levels\n";
echo $db->query("SELECT sql FROM sqlite_master WHERE name='grade_levels'")->fetchColumn() . "\n";
