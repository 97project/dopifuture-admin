<?php
$json = json_decode(file_get_contents('C:/laragon/www/panel26/referans_alman_icin_dis_projeler/apidoc.json'), true);
if (isset($json['paths'])) {
    foreach ($json['paths'] as $path => $methods) {
        if (stripos($path, 'mission') !== false || stripos($path, 'startup') !== false || stripos($path, 'report') !== false || stripos($path, 'coach') !== false) {
            echo "PATH: $path\n";
        }
    }
} else {
    echo "NO PATHS FOUND\n";
    print_r(array_keys($json));
}
