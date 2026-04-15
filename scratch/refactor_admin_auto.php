<?php
$dirViews = dirname(__DIR__) . '/resources/views';

$tr_arr = [];
$en_arr = [];
$mn_arr = [];

function slugify($text) {
    // lowercase
    $text = strtolower($text);
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '_', $text);
    // trim
    $text = trim($text, '_');
    return substr($text, 0, 30);
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirViews));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $changed = false;

        $content = preg_replace_callback("/app\(\)->getLocale\(\)\s*===\s*'tr'\s*\?\s*'([^']+)'\s*:\s*'([^']+)'/", function($m) use (&$tr_arr, &$en_arr, &$mn_arr, &$changed) {
            $tr = $m[1];
            $en = $m[2];
            $key = 'auto_' . slugify($en);
            // Append random digits if collision
            while (isset($en_arr[$key]) && $en_arr[$key] !== $en) {
                $key .= rand(0, 9);
            }
            $tr_arr[$key] = $tr;
            $en_arr[$key] = $en;
            $mn_arr[$key] = $en; // fallback to en for now
            $changed = true;
            return "__('admin." . $key . "')";
        }, $content);

        $content = preg_replace_callback('/app\(\)->getLocale\(\)\s*===\s*"tr"\s*\?\s*"([^"]+)"\s*:\s*"([^"]+)"/', function($m) use (&$tr_arr, &$en_arr, &$mn_arr, &$changed) {
            $tr = $m[1];
            $en = $m[2];
            $key = 'auto_' . slugify($en);
            while (isset($en_arr[$key]) && $en_arr[$key] !== $en) {
                $key .= rand(0, 9);
            }
            $tr_arr[$key] = $tr;
            $en_arr[$key] = $en;
            $mn_arr[$key] = $en;
            $changed = true;
            return '__("admin.' . $key . '")'; 
        }, $content);

        // Also match `app()->getLocale() == 'tr'` instead of `===` just in case
        $content = preg_replace_callback("/app\(\)->getLocale\(\)\s*==\s*'tr'\s*\?\s*'([^']+)'\s*:\s*'([^']+)'/", function($m) use (&$tr_arr, &$en_arr, &$mn_arr, &$changed) {
            $tr = $m[1];
            $en = $m[2];
            $key = 'auto_' . slugify($en);
            $tr_arr[$key] = $tr;
            $en_arr[$key] = $en;
            $mn_arr[$key] = $en;
            $changed = true;
            return "__('admin." . $key . "')";
        }, $content);

        // also look for dynamic arrays indexing `['tr' => ...][$locale]` if any, but the main issue is the ternary.

        if ($changed) {
            file_put_contents($path, $content);
        }
    }
}

// Append to files
function appendArrayToFileAdmin($path, $extraData) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $extraData);
    $content = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
    file_put_contents($path, $content);
}

appendArrayToFileAdmin(dirname(__DIR__) . '/lang/tr/admin.php', $tr_arr);
appendArrayToFileAdmin(dirname(__DIR__) . '/lang/en/admin.php', $en_arr);
appendArrayToFileAdmin(dirname(__DIR__) . '/lang/mn/admin.php', $mn_arr);

echo "Auto refactor done. Found " . count($tr_arr) . " remaining items.";
