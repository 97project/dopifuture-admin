<?php
try {
    $db = new PDO("mysql:host=127.0.0.1;port=3306;dbname=panel26", "root", "");
    $stmt = $db->query("SHOW TABLES LIKE 'ws_%'");
    while($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . PHP_EOL;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
