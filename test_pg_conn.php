<?php
$host = 'doping-tech-prod-side-app-db.cc1aorlmeafi.eu-central-1.rds.amazonaws.com';
$port = 5432;
$user = 'mustafa_karali';
$pass = 'fS@8Rc84$nHU';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname=postgres";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "CONNECTION OK!\n\n";

    // List all schemas
    $stmt = $pdo->query("SELECT schema_name FROM information_schema.schemata ORDER BY schema_name");
    echo "Available schemas:\n";
    foreach ($stmt as $row) {
        echo "  - {$row['schema_name']}\n";
    }

    echo "\n";

    // Check way_backend schema tables
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'way_backend' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in 'way_backend' schema (" . count($tables) . "):\n";
    foreach ($tables as $t) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM way_backend.\"{$t}\"")->fetchColumn();
        echo "  - {$t} ({$cnt} rows)\n";
    }

} catch (PDOException $e) {
    echo "CONNECTION FAILED: " . $e->getMessage() . "\n";
}
