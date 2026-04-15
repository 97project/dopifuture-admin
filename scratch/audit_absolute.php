<?php
/**
 * ABSOLUTE FINAL AUDIT - catches EVERYTHING
 * Including: JS strings, alert(), innerHTML, button text, option text, 
 * td text, span text, div text, label text, h1-h6, p tags, li tags
 */
$base = dirname(__DIR__);
$viewsPath = $base . '/resources/views/portal';
$brandNames = ['DopiFuture','Mission Way','Mission WAY','Role Galaxy','WAY AI Coach','Study Space','Way Startup','Startup Lab','Vega','WebSocket','LinkedIn','Twitter','Instagram','WAY AI','Nunito','Carbon','Laravel'];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$totalIssues = 0;
$allHits = [];

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $lines = file($file->getPathname());
    $relPath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $hits = [];

    foreach ($lines as $i => $line) {
        $lineNum = $i + 1;
        $trimmed = trim($line);
        
        // Skip pure code/directive/comment/svg lines
        if (preg_match('/^\s*(@|\/\/|\*|{--|<svg|<path|<circle|<rect|\$|var |let |const |function |return |if\s*\(|else\s*{|switch|case |for\s*\(|while|echo |preg_|str_|array|foreach|match|default\s*=>)/', $trimmed)) continue;
        if (preg_match('/^\s*<style|<\/style|\.[\w-]+\s*{|}\s*$/', $trimmed)) continue;
        if (empty($trimmed) || strlen($trimmed) < 4) continue;

        // Already has __() — check if there's ALSO hardcoded text on same line
        $hasTranslation = strpos($line, '__(') !== false;
        
        // 1. Text between > and < (most common pattern)
        if (preg_match_all('/>\s*([A-Z][a-zA-Z\s\-\/\(\)]{2,}?)\s*</', $line, $m)) {
            foreach ($m[1] as $text) {
                $text = trim($text);
                if (strlen($text) < 3) continue;
                if (isBrand($text, $brandNames)) continue;
                if ($hasTranslation && preg_match('/\{\{.*__\(.*' . preg_quote(substr($text, 0, 5), '/') . '/', $line)) continue;
                $hits[] = "  L$lineNum [>text<]: \"$text\"";
            }
        }
        
        // 2. Placeholders
        if (preg_match_all('/placeholder="([^"]{4,})"/', $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') !== false) continue;
                if (!preg_match('/[a-zA-Z]{3,}/', $text)) continue;
                if (preg_match('/^[\+\d\s\-X]+$/', $text)) continue; // phone format
                if (preg_match('/^https?:/', $text)) continue; // URL
                if (preg_match('/@|\.com|\.edu|\.tr/', $text)) continue; // email
                $hits[] = "  L$lineNum [placeholder]: \"$text\"";
            }
        }
        
        // 3. Title attributes
        if (preg_match_all('/title="([^"]{3,})"/', $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') !== false) continue;
                if (!preg_match('/[A-Z][a-z]{2,}/', $text)) continue;
                if (isBrand($text, $brandNames)) continue;
                $hits[] = "  L$lineNum [title]: \"$text\"";
            }
        }
        
        // 4. Confirm/alert dialogs
        if (preg_match_all("/(?:confirm|alert)\('([^']{5,})'\)/", $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') !== false) continue;
                if (!preg_match('/[a-zA-Z]{3,}/', $text)) continue;
                $hits[] = "  L$lineNum [dialog]: \"$text\"";
            }
        }
        
        // 5. Section with hardcoded string
        if (preg_match("/@section\('(?:title|page-title)',\s*'([^']+)'\)/", $line, $m)) {
            $hits[] = "  L$lineNum [section]: \"$m[1]\"";
        }
        
        // 6. Ternary with hardcoded strings (not inside __())
        if (!$hasTranslation && preg_match_all("/\?\s*'([A-Z][a-z]+(?:\s[A-Za-z]+)*)'/", $line, $m)) {
            foreach ($m[1] as $text) {
                $hits[] = "  L$lineNum [ternary]: \"$text\"";
            }
        }
        
        // 7. Card/modal titles
        if (preg_match('/(?:dp-card-title|dp-modal-title|figma-modal-title)"?>([^<{]+)</', $line, $m)) {
            $text = trim($m[1]);
            if (strlen($text) > 2 && preg_match('/[A-Z]/', $text) && strpos($text, '{{') === false) {
                if (!isBrand($text, $brandNames)) $hits[] = "  L$lineNum [card-title]: \"$text\"";
            }
        }
        
        // 8. Modal subtitles
        if (preg_match('/dp-modal-subtitle"?>([^<{]+)</', $line, $m)) {
            $text = trim($m[1]);
            if (strlen($text) > 5 && preg_match('/[A-Z]/', $text) && strpos($text, '{{') === false) {
                $hits[] = "  L$lineNum [subtitle]: \"$text\"";
            }
        }
        
        // 9. Label text
        if (preg_match('/dp-form-label"?>([^<{]+)</', $line, $m)) {
            $text = trim($m[1]);
            if (strlen($text) > 1 && preg_match('/[A-Z]/', $text) && strpos($text, '{{') === false) {
                $hits[] = "  L$lineNum [label]: \"$text\"";
            }
        }
        
        // 10. JS innerHTML/textContent with English text
        if (preg_match_all('/(?:innerHTML|textContent)\s*=\s*[\'"]([A-Z][a-z].+?)[\'"]/', $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') !== false) continue;
                $hits[] = "  L$lineNum [js-text]: \"$text\"";
            }
        }
        
        // 11. JS alert/confirm
        if (preg_match_all("/(?:alert|confirm)\(['\"]([^'\"]{5,})['\"]\)/", $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') !== false) continue;
                if (!preg_match('/[a-zA-Z]{3,}/', $text)) continue;
                $hits[] = "  L$lineNum [js-dialog]: \"$text\"";
            }
        }
    }
    
    if (!empty($hits)) {
        echo "\n=== $relPath (" . count($hits) . ") ===\n";
        echo implode("\n", $hits) . "\n";
        $totalIssues += count($hits);
        $allHits[$relPath] = $hits;
    }
}

echo "\n══════════════════════════════════════════\n";
echo "ABSOLUTE FINAL COUNT: $totalIssues issues\n";
echo "══════════════════════════════════════════\n";

function isBrand($text, $brands) {
    foreach ($brands as $bn) {
        if (stripos($text, $bn) !== false) return true;
    }
    return false;
}
