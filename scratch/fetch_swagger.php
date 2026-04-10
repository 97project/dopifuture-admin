<?php
$json = file_get_contents('https://mission-way-backend-test.dopingtech.net/docs-json');
if ($json) {
    file_put_contents(__DIR__ . '/swagger.json', $json);
    echo "Saved\n";
} else {
    echo "Failed to fetch\n";
}
