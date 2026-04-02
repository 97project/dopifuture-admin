<?php
try {
    $db = new PDO('pgsql:host=dopifuture-db-production-2.cxgw0eagud4i.eu-central-1.rds.amazonaws.com;port=5432;dbname=postgres', 'doadmin', 'Q8D2mS5v!xY9aB3c#L7pP4jK1rF6tN');
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and (table_name like '%submit%' or table_name like '%submis%')");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['table_name'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
