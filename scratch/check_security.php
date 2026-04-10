<?php
$json = json_decode(file_get_contents(__DIR__ . '/swagger.json'), true);

$methods = $json['paths']['/v1/startup/members'] ?? null;
if ($methods) {
    print_r($methods['get']['security'] ?? 'No security defined for GET');
    echo "\n\n";
    print_r($methods['post']['security'] ?? 'No security defined for POST');
} else {
    echo "NO PATH FOUND\n";
}
