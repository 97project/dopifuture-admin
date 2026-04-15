<?php
$d = require dirname(__DIR__) . '/lang/en/portal.php';
$keys = ['why_title','why_subtitle','feat1_title','feat1_desc','feat2_title','feat2_desc','feat3_title','feat3_desc','feat4_title','feat4_desc','feat5_title','feat5_desc','feat6_title','feat6_desc','vision_quote','vision_author','cta_title','cta_desc','cta_btn','mw_desc','startup_desc','role_desc','coach_desc','study_desc','hero_title','hero_tagline','hero_btn_start','hero_btn_explore','solutions_title','contact_title'];
foreach($keys as $k) {
    echo (isset($d[$k]) ? "OK" : "MISS") . ": $k\n";
}
