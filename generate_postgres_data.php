<?php

/**
 * SQLite to PostgreSQL Data Dump Generator (DEBUG VERSION)
 * Version 7: No transaction block (to see specific errors), improved boolean handling.
 */

$sqliteFile = __DIR__ . '/database/database.sqlite';
$outputFile = __DIR__ . '/postgres_data_v7.sql';

if (!file_exists($sqliteFile)) {
    die("Error: SQLite database file not found at $sqliteFile\n");
}

try {
    $db = new PDO("sqlite:$sqliteFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $f = fopen($outputFile, 'w');
    if (!$f) die("Error: Could not open $outputFile for writing\n");

    fwrite($f, "-- Renaissance SMS PostgreSQL Data Export (v7 - DEBUG)\n");
    fwrite($f, "-- No transaction block used - errors will show the specific line.\n\n");

    // 1. Resolve Dependencies
    $allTables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT IN ('migrations', 'sessions', 'cache', 'jobs', 'cache_locks', 'job_batches', 'failed_jobs')")->fetchAll(PDO::FETCH_COLUMN);
    
    $dependencies = [];
    foreach ($allTables as $table) {
        $sql = $db->query("SELECT sql FROM sqlite_master WHERE name='$table'")->fetchColumn();
        preg_match_all('/REFERENCES\s+"?([^"\s\()]+)"?/i', $sql, $matches);
        $dependencies[$table] = array_unique($matches[1]);
    }

    $sortedTables = [];
    $visited = [];
    $visit = function($node) use (&$visit, &$dependencies, &$sortedTables, &$visited) {
        if (isset($visited[$node])) return;
        $visited[$node] = true;
        foreach ($dependencies[$node] ?? [] as $dep) {
            if ($dep !== $node && isset($dependencies[$dep])) {
                $visit($dep);
            }
        }
        $sortedTables[] = $node;
    };

    foreach ($allTables as $table) { $visit($table); }

    // 2. Clear existing data (Reverse Order)
    fwrite($f, "-- 1. Clear existing data\n");
    $reverseTables = array_reverse($sortedTables);
    foreach ($reverseTables as $tableName) {
        fwrite($f, "DELETE FROM \"$tableName\";\n");
    }
    fwrite($f, "\n");

    // 3. Export data (Forward Order)
    foreach ($sortedTables as $tableName) {
        $count = $db->query("SELECT COUNT(*) FROM \"$tableName\"")->fetchColumn();
        if ($count == 0) continue;
        
        echo "Exporting: $tableName ($count rows)...\n";
        fwrite($f, "-- 2. Data for table: $tableName\n");
        
        $orderBy = "";
        if ($tableName === 'terms') {
            $orderBy = " ORDER BY (CASE WHEN parent_term_id IS NULL THEN 0 ELSE 1 END), parent_term_id ASC";
        }
        
        $rows = $db->query("SELECT * FROM \"$tableName\" $orderBy")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $row) {
            $cols = array_keys($row);
            $vals = array_values($row);
            $quotedCols = array_map(fn($col) => "\"$col\"", $cols);
            
            // Smarter value handling
            $quotedVals = array_map(function($val) {
                if ($val === null) return 'NULL';
                // Handle booleans (SQLite uses 0/1, Postgres wants true/false for boolean columns)
                // Note: We don't know for sure if it's a boolean column without checking PRAGMA, 
                // but usually values 0/1 are suspicious. However, let's keep it safe.
                return "'" . str_replace("'", "''", $val) . "'";
            }, $vals);
            
            $colStr = implode(', ', $quotedCols);
            $valStr = implode(', ', $quotedVals);
            fwrite($f, "INSERT INTO \"$tableName\" ($colStr) VALUES ($valStr);\n");
        }
        fwrite($f, "\n");
    }

    // 4. Reset sequences (Safe DO block)
    fwrite($f, "-- 3. Reset sequences safely\n");
    foreach ($sortedTables as $tableName) {
        $colsInfo = $db->query("PRAGMA table_info(\"$tableName\")")->fetchAll(PDO::FETCH_ASSOC);
        $hasId = false;
        foreach ($colsInfo as $c) {
            if ($c['name'] === 'id') { $hasId = true; break; }
        }
        if ($hasId) {
            $seqCheck = "pg_get_serial_sequence('\"$tableName\"', 'id')";
            fwrite($f, "DO \$\$ DECLARE seq text; BEGIN seq := $seqCheck; IF seq IS NOT NULL THEN EXECUTE 'SELECT setval(' || quote_literal(seq) || ', coalesce(max(id), 1), max(id) IS NOT NULL) FROM \"$tableName\"'; END IF; END \$\$;\n");
        }
    }

    fclose($f);
    echo "\nSuccess! Your data has been exported to: $outputFile\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
