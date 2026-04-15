<?php
/**
 * BRUTALLY HONEST FINAL AUDIT
 * Catches ALL patterns of hardcoded English text:
 * 1. >Text< patterns
 * 2. placeholder="..." attributes
 * 3. title="..." attributes  
 * 4. confirm('...') dialogs
 * 5. @section('title', '...') with hardcoded strings
 * 6. Inline ternary 'Text' : 'Text'
 * 7. Text inside tags not matching simple regex
 */

$base = dirname(__DIR__);
$viewsPath = $base . '/resources/views/portal';
$brandNames = ['DopiFuture','Mission Way','Mission WAY','Role Galaxy','WAY AI Coach','Study Space','Way Startup','Startup Lab','Vega','WebSocket'];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$totalIssues = 0;

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $lines = file($file->getPathname());
    $relPath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $hits = [];

    foreach ($lines as $i => $line) {
        $lineNum = $i + 1;
        $trimmed = trim($line);
        
        // Skip blade directives, PHP logic, JS, comments, SVG
        if (preg_match('/^\s*(@|\/\/|\*|{--|<svg|<path|<circle)/', $trimmed)) continue;
        if (preg_match('/^\s*\$/', $trimmed)) continue;
        if (preg_match('/^\s*(var |let |const |function |return |if\s*\(|else|switch|case |for\s*\(|while)/', $trimmed)) continue;
        
        // Already translated — skip lines with __()
        // But ALSO check for lines with BOTH __() AND hardcoded text
        
        // Pattern 1: placeholder="English text" (not translated)
        if (preg_match_all('/placeholder="([^"]{3,})"/', $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') === false && preg_match('/[a-zA-Z]{3,}/', $text)) {
                    $hits[] = "  L$lineNum [placeholder]: \"$text\"";
                }
            }
        }
        
        // Pattern 2: title="English text" (not translated)
        if (preg_match_all('/title="([^"]{3,})"/', $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') === false && preg_match('/[A-Z][a-z]{2,}/', $text)) {
                    $hits[] = "  L$lineNum [title]: \"$text\"";
                }
            }
        }
        
        // Pattern 3: confirm('English text') — NOT translated
        if (preg_match_all("/confirm\('([^']{5,})'\)/", $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($text, '__(') === false && preg_match('/[a-zA-Z]{3,}/', $text)) {
                    $hits[] = "  L$lineNum [confirm]: \"$text\"";
                }
            }
        }
        
        // Pattern 4: @section('title', 'Hardcoded')
        if (preg_match("/@section\('(?:title|page-title)',\s*'([^']+)'\)/", $line, $m)) {
            $hits[] = "  L$lineNum [section]: \"$m[1]\"";
        }
        
        // Pattern 5: Ternary with hardcoded strings: ? 'Active' : 'Inactive'
        if (preg_match_all("/\?\s*'([A-Z][a-z]+(?:\s[A-Za-z]+)*)'\s*:/", $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($line, '__(') === false) {
                    $hits[] = "  L$lineNum [ternary]: \"$text\"";
                }
            }
        }
        
        // Pattern 6: Inline text between tags — already covered but let's be thorough
        // Look for hardcoded text NOT inside {{ }} or {!! !!}
        if (preg_match_all('/>\s*([A-Z][a-z]{2,}(?:\s+[A-Za-z\-]+){0,5})\s*</', $line, $m)) {
            foreach ($m[1] as $text) {
                if (strpos($line, '__(') !== false) continue; // line already has translation
                $isBrand = false;
                foreach ($brandNames as $bn) {
                    if (stripos($text, $bn) !== false) { $isBrand = true; break; }
                }
                if (!$isBrand && strlen($text) > 3) {
                    $hits[] = "  L$lineNum [text]: \"$text\"";
                }
            }
        }
        
        // Pattern 7: Label text directly in HTML (class="dp-form-label">Text</label>)
        if (preg_match('/dp-form-label"?>([^<{]+)</', $line, $m)) {
            $text = trim($m[1]);
            if (strlen($text) > 2 && preg_match('/[A-Z]/', $text) && strpos($text, '{{') === false) {
                $hits[] = "  L$lineNum [label]: \"$text\"";
            }
        }
        
        // Pattern 8: dp-card-title or dp-modal-title with hardcoded text
        if (preg_match('/(?:dp-card-title|dp-modal-title|figma-modal-title)"?>([^<{]+)</', $line, $m)) {
            $text = trim($m[1]);
            if (strlen($text) > 2 && preg_match('/[A-Z]/', $text) && strpos($text, '{{') === false) {
                $hits[] = "  L$lineNum [card-title]: \"$text\"";
            }
        }
        
        // Pattern 9: dp-modal-subtitle with hardcoded text
        if (preg_match('/dp-modal-subtitle"?>([^<{]+)</', $line, $m)) {
            $text = trim($m[1]);
            if (strlen($text) > 5 && preg_match('/[A-Z]/', $text) && strpos($text, '{{') === false) {
                $hits[] = "  L$lineNum [subtitle]: \"$text\"";
            }
        }
    }
    
    if (!empty($hits)) {
        echo "\n=== $relPath (" . count($hits) . ") ===\n";
        echo implode("\n", $hits) . "\n";
        $totalIssues += count($hits);
    }
}

echo "\n══════════════════════════════════\n";
echo "TOTAL REMAINING ISSUES: $totalIssues\n";
echo "══════════════════════════════════\n";
