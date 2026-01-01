<?php

/**
 * SQLite to MySQL Data Exporter
 * This script exports all data from your local SQLite database into a .sql file
 * that can be imported directly into InfinityFree's phpMyAdmin.
 */

$sqliteFile = __DIR__ . '/database/database.sqlite';
$outputFile = __DIR__ . '/mysql_export.sql';

if (!file_exists($sqliteFile)) {
    die("Error: SQLite database file not found at $sqliteFile\n");
}

try {
    $db = new PDO("sqlite:$sqliteFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlOutput = "-- Renaissance SMS MySQL Export\n";
    $sqlOutput .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    // Get all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "Exporting table: $table...\n";
        
        $sqlOutput .= "-- Data for table `$table` --\n";
        
        // Fetch data
        $rows = $db->query("SELECT * FROM \"$table\"")->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            $sqlOutput .= "-- (No data)\n\n";
            continue;
        }

        foreach ($rows as $row) {
            $columns = array_keys($row);
            $escapedColumns = array_map(fn($c) => "`$c`", $columns);
            
            $values = array_map(function($val) use ($db) {
                if ($val === null) return 'NULL';
                if (is_numeric($val) && !is_string($val)) return $val;
                // Handle booleans (SQLite uses 0/1, MySQL is fine with 0/1)
                
                // Escape string for MySQL
                $escaped = str_replace(["\\", "'"], ["\\\\", "''"], $val);
                return "'" . $escaped . "'";
            }, array_values($row));

            $sqlOutput .= "INSERT INTO `$table` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $values) . ");\n";
        }
        $sqlOutput .= "\n";
    }

    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    file_put_contents($outputFile, $sqlOutput);
    echo "\nSuccess! Your data has been exported to: $outputFile\n";
    echo "You can now import this file into phpMyAdmin on InfinityFree.\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
