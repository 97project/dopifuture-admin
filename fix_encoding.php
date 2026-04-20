<?php
// Fix ALL remaining Â issues in FR file
$file = __DIR__ . '/lang/fr/portal.php';
$c = file_get_contents($file);

// The pattern is: bytes C3 82 followed by a space (20) or other byte
// This is "Â" in UTF-8 followed by space = should be "à " (C3 A0 20)
// But some cases have "Â" followed by non-space byte

// Fix: vision_quote line - "permet Â  chaque" -> "permet à chaque" and "apprendre Â  son" -> "apprendre à son"
// These are "C3 82 C2 A0" (Â + non-breaking space) that should be "C3 A0" (à)
$c = str_replace("\xC3\x82\xC2\xA0", "\xC3\xA0 ", $c);

// Also fix any remaining "Â " (C3 82 20) 
$c = str_replace("\xC3\x82\x20", "\xC3\xA0 ", $c);

// Fix "lÂ " -> "là " (88 -> contact_hero_desc)
$c = str_replace("l\xC3\x82", "l\xC3\xA0", $c);

file_put_contents($file, $c);

// Verify
$arr = @include $file;
echo "FR: " . (is_array($arr) ? "Valid, " . count($arr) . " keys" : "INVALID!") . "\n";

// Check remaining
$lines = explode("\n", file_get_contents($file));
$issues = 0;
foreach ($lines as $i => $line) {
    if (preg_match('/hero_title_(tr|en|mn|hi|ko|ja)|hero_tagline_(tr|en|mn|hi|ko|ja)/', $line)) continue;
    if (strpos($line, "\xC3\x82") !== false) {
        $issues++;
        echo "  L" . ($i+1) . ": " . substr(trim($line), 0, 70) . "\n";
    }
}
echo "Remaining: $issues\n";
