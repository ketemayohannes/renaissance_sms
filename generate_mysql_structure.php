<?php

/**
 * SQLite to MySQL Schema Generator
 * This script reads your SQLite file and attempts to generate MySQL-compatible 
 * CREATE TABLE statements.
 */

$sqliteFile = __DIR__ . '/database/database.sqlite';
$outputFile = __DIR__ . '/mysql_structure.sql';

if (!file_exists($sqliteFile)) {
    die("Error: SQLite database file not found at $sqliteFile\n");
}

try {
    $db = new PDO("sqlite:$sqliteFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlOutput = "-- Renaissance SMS MySQL Structure Export\n";
    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    // Get all tables
    $tables = $db->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tables as $tableInfo) {
        $tableName = $tableInfo['name'];
        $sqliteSql = $tableInfo['sql'];

        echo "Generating structure for: $tableName...\n";

        // basic translation of SQLite SQL to MySQL
        $mysqlSql = $sqliteSql;
        
        // Replace double quotes with backticks
        $mysqlSql = str_replace('"', '`', $mysqlSql);
        
        // Extract table name correctly before messing with the string
        if (preg_match('/CREATE TABLE `([^`]+)`/i', $mysqlSql, $matches)) {
            $tableName = $matches[1];
            $mysqlSql = "DROP TABLE IF EXISTS `$tableName`;\n" . $mysqlSql;
        }
        
        // Handle SQLite types to MySQL types
        $replacements = [
            'PRIMARY KEY AUTOINCREMENT' => 'PRIMARY KEY AUTO_INCREMENT',
            'primary key autoincrement' => 'primary key auto_increment',
            'AUTOINCREMENT' => 'AUTO_INCREMENT',
            'autoincrement' => 'auto_increment',
            'INTEGER PRIMARY KEY' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'integer primary key' => 'int auto_increment primary key',
            'BLOB' => 'LONGBLOB',
            'DATETIME' => 'DATETIME',
            'BOOLEAN' => 'TINYINT(1)',
            'TEXT' => 'LONGTEXT',
            'numeric' => 'DECIMAL(19,4)',
            'NUMERIC' => 'DECIMAL(19,4)',
            'integer' => 'INT',
            'INTEGER' => 'INT',
        ];
        
        foreach ($replacements as $sqliteType => $mysqlType) {
            $mysqlSql = str_replace($sqliteType, $mysqlType, $mysqlSql);
        }

        // Remove CHECK constraints specifically (causes errors in some MariaDB/MySQL versions)
        // This handles nested parentheses like check (column in ('A', 'B')) 
        // by matching 'check' followed by balanced parentheses
        $mysqlSql = preg_replace('/check\s*(\((?:[^()]++|(?1))*\))/i', '', $mysqlSql);
        
        // Clean up extra spaces around commas and double spaces (preserving newlines)
        $mysqlSql = preg_replace('/ +/', ' ', $mysqlSql);
        $mysqlSql = str_replace(' ,', ',', $mysqlSql);
        $mysqlSql = trim($mysqlSql);

        // Fix double AUTO_INCREMENT if both replacements hit the same line
        $mysqlSql = str_replace('AUTO_INCREMENT PRIMARY KEY AUTO_INCREMENT', 'PRIMARY KEY AUTO_INCREMENT', $mysqlSql);
        $mysqlSql = str_replace('auto_increment primary key auto_increment', 'primary key auto_increment', $mysqlSql);
        
        // Ensure VARCHAR has a length (MySQL requires it)
        $mysqlSql = preg_replace('/varchar\b(?!\s*\()/i', 'VARCHAR(255)', $mysqlSql);
        
        $sqlOutput .= $mysqlSql . " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
    }

    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    file_put_contents($outputFile, $sqlOutput);
    echo "\nSuccess! Your structure has been exported to: $outputFile\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
