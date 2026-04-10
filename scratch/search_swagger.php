<?php
$json = json_decode(file_get_contents(__DIR__ . '/swagger.json'), true);
if (isset($json['paths'])) {
    foreach ($json['paths'] as $path => $methods) {
        if (strpos($path, 'startup') !== false) {
            echo "Path: $path\n";
            foreach ($methods as $method => $details) {
                echo "  Method: $method\n";
            }
        }
    }
} else {
    echo "NO PATHS FOUND\n";
}
