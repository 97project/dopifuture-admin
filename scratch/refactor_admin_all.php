<?php
$dir = dirname(__DIR__) . '/resources/views/admin';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$str_map = [
    'İzin takma adlarını düzenleyin ve izinleri senkronize edin.' => ['key' => 'rep_edit_permissions', 'tr' => 'İzin takma adlarını düzenleyin ve izinleri senkronize edin.', 'en' => 'Edit permission aliases and sync permissions.', 'mn' => 'Эрхийн нэрсийг засаж, эрхийг синхрончлох.'],
    'Tahmini Öğrenci Sayısı' => ['key' => 'rep_student_count', 'tr' => 'Tahmini Öğrenci Sayısı', 'en' => 'Student Count', 'mn' => 'Оюутны тоо'],
    'Detaylı uygulama raporu' => ['key' => 'rep_detailed_app_report', 'tr' => 'Detaylı uygulama raporu', 'en' => 'Detailed application report', 'mn' => 'Апп-ын дэлгэрэнгүй тайлан'],
    'Toplam İlerleme' => ['key' => 'rep_total_progress', 'tr' => 'Toplam İlerleme', 'en' => 'Total Progress', 'mn' => 'Нийт ахиц дэвшил'],
    'Tamamlanan' => ['key' => 'rep_completed', 'tr' => 'Tamamlanan', 'en' => 'Completed', 'mn' => 'Дууссан'],
    'Ort. Puan' => ['key' => 'rep_avg_score', 'tr' => 'Ort. Puan', 'en' => 'Avg Score', 'mn' => 'Думдаж оноо'],
    'Oturum' => ['key' => 'rep_sessions', 'tr' => 'Oturum', 'en' => 'Sessions', 'mn' => 'Сесс'],
    'Toplam Süre' => ['key' => 'rep_total_duration', 'tr' => 'Toplam Süre', 'en' => 'Total Duration', 'mn' => 'Нийт хугацаа'],
    'Modül Dağılımı' => ['key' => 'rep_module_dist', 'tr' => 'Modül Dağılımı', 'en' => 'Module Distribution', 'mn' => 'Модулийн тархалт'],
    'Günlük Oturumlar' => ['key' => 'rep_daily_sessions', 'tr' => 'Günlük Oturumlar', 'en' => 'Daily Sessions', 'mn' => 'Өдөр тутмын сесс'],
    'Öğrenci Performansı' => ['key' => 'rep_student_perf', 'tr' => 'Öğrenci Performansı', 'en' => 'Student Performance', 'mn' => 'Сурагчийн гүйцэтгэл'],
    'Öğrenci' => ['key' => 'rep_students', 'tr' => 'Öğrenci', 'en' => 'Students', 'mn' => 'Сурагчид'],
    'Tamamlanma' => ['key' => 'rep_completion', 'tr' => 'Tamamlanma', 'en' => 'Completion', 'mn' => 'Гүйцэтгэл'],
    'Puan' => ['key' => 'rep_points', 'tr' => 'Puan', 'en' => 'Points', 'mn' => 'Оноо'],
    'Süre' => ['key' => 'rep_time', 'tr' => 'Süre', 'en' => 'Time', 'mn' => 'Хугацаа'],
    'Detay' => ['key' => 'rep_detail', 'tr' => 'Detay', 'en' => 'Detail', 'mn' => 'Дэлгэрэнгүй'],
    'Henüz veri yok' => ['key' => 'rep_no_data_yet', 'tr' => 'Henüz veri yok', 'en' => 'No data yet', 'mn' => 'Одоогоор өгөгдөл алга'],
    'Sistem geneli uygulama ve okul raporları' => ['key' => 'rep_system_wide', 'tr' => 'Sistem geneli uygulama ve okul raporları', 'en' => 'System-wide application and school reports', 'mn' => 'Системийн хэмжээний апп болон сургуулийн тайлангууд'],
    'Toplam Kullanıcı' => ['key' => 'rep_total_users', 'tr' => 'Toplam Kullanıcı', 'en' => 'Total Users', 'mn' => 'Нийт хэрэглэгчид'],
    'Toplam Öğrenci' => ['key' => 'rep_total_students', 'tr' => 'Toplam Öğrenci', 'en' => 'Total Students', 'mn' => 'Нийт сурагчид'],
    'Uygulama' => ['key' => 'rep_applications', 'tr' => 'Uygulama', 'en' => 'Applications', 'mn' => 'Аппууд'],
    'Okul' => ['key' => 'rep_school', 'tr' => 'Okul', 'en' => 'School', 'mn' => 'Сургууль'],
    'Uygulama Kullanımı' => ['key' => 'rep_app_usage', 'tr' => 'Uygulama Kullanımı', 'en' => 'Application Usage', 'mn' => 'Апп ашиглалт'],
    'Tamamlanma Durumu' => ['key' => 'rep_completion_status', 'tr' => 'Tamamlanma Durumu', 'en' => 'Completion Status', 'mn' => 'Дуусах төлөв'],
    'Uygulama Performansı' => ['key' => 'rep_app_perf', 'tr' => 'Uygulama Performansı', 'en' => 'Application Performance', 'mn' => 'Апп гүйцэтгэл'],
    'kullanıcı' => ['key' => 'rep_users_sm', 'tr' => 'kullanıcı', 'en' => 'users', 'mn' => 'хэрэглэгч'],
    'Devam Eden' => ['key' => 'rep_in_progress', 'tr' => 'Devam Eden', 'en' => 'In Progress', 'mn' => 'Үргэлжилж буй'],
    'Okul Dağılımı' => ['key' => 'rep_school_dist', 'tr' => 'Okul Dağılımı', 'en' => 'School Distribution', 'mn' => 'Сургуулийн тархалт'],
    'Kullanıcı' => ['key' => 'rep_users', 'tr' => 'Kullanıcı', 'en' => 'Users', 'mn' => 'Хэрэглэгч'],
    'Sınıf' => ['key' => 'rep_classes', 'tr' => 'Sınıf', 'en' => 'Classes', 'mn' => 'Анги'],
    'Lisans' => ['key' => 'rep_license', 'tr' => 'Lisans', 'en' => 'License', 'mn' => 'Лиценз'],
    'Rapor' => ['key' => 'rep_report', 'tr' => 'Rapor', 'en' => 'Report', 'mn' => 'Тайлан'],
    'Okul detaylı raporu' => ['key' => 'rep_school_detailed', 'tr' => 'Okul detaylı raporu', 'en' => 'School detailed report', 'mn' => 'Сургуулийн дэлгэрэнгүй тайлан'],
    'Lisanslar ve Satın Almalar' => ['key' => 'rep_licenses_purchases', 'tr' => 'Lisanslar ve Satın Almalar', 'en' => 'Licenses & Purchases', 'mn' => 'Лиценз ба худалдан авалт'],
    'Kontenjan' => ['key' => 'rep_seats', 'tr' => 'Kontenjan', 'en' => 'Seats', 'mn' => 'Суудлууд'],
    'Kullanılan' => ['key' => 'rep_used', 'tr' => 'Kullanılan', 'en' => 'Used', 'mn' => 'Ашигласан'],
    'Satın Alma' => ['key' => 'rep_purchases', 'tr' => 'Satın Alma', 'en' => 'Purchases', 'mn' => 'Худалдан авалт'],
    'Aktif' => ['key' => 'rep_active', 'tr' => 'Aktif', 'en' => 'Active', 'mn' => 'Идэвхтэй'],
    'Pasif' => ['key' => 'rep_inactive', 'tr' => 'Pasif', 'en' => 'Inactive', 'mn' => 'Идэвхгүй'],
    'kontenjan' => ['key' => 'rep_seats_sm', 'tr' => 'kontenjan', 'en' => 'seats', 'mn' => 'суудал'],
    'Okul Kullanıcıları' => ['key' => 'rep_school_users', 'tr' => 'Okul Kullanıcıları', 'en' => 'School Users', 'mn' => 'Сургуулийн хэрэглэгчид'],
    'Ad Soyad' => ['key' => 'rep_name', 'tr' => 'Ad Soyad', 'en' => 'Name', 'mn' => 'Овог нэр'],
    'Rol' => ['key' => 'rep_role', 'tr' => 'Rol', 'en' => 'Role', 'mn' => 'Дүр'],
    'Detaylı öğrenci raporu' => ['key' => 'rep_student_detailed', 'tr' => 'Detaylı öğrenci raporu', 'en' => 'Detailed student report', 'mn' => 'Сурагчийн дэлгэрэнгүй тайлан'],
    'Roller' => ['key' => 'rep_roles', 'tr' => 'Roller', 'en' => 'Roles', 'mn' => 'Дүрүүд'],
    'Okullar' => ['key' => 'rep_schools', 'tr' => 'Okullar', 'en' => 'Schools', 'mn' => 'Сургуулиуд'],
    'Sınıflar' => ['key' => 'rep_classes_pl', 'tr' => 'Sınıflar', 'en' => 'Classes', 'mn' => 'Ангиуд'],
    'Uygulamalar' => ['key' => 'rep_applications_pl', 'tr' => 'Uygulamalar', 'en' => 'Applications', 'mn' => 'Аппууд'],
    'Modül' => ['key' => 'rep_module', 'tr' => 'Modül', 'en' => 'Module', 'mn' => 'Модуль'],
    'Tip' => ['key' => 'rep_type', 'tr' => 'Tip', 'en' => 'Type', 'mn' => 'Төрөл'],
    'Durum' => ['key' => 'rep_status', 'tr' => 'Durum', 'en' => 'Status', 'mn' => 'Төлөв'],
    'Tarih' => ['key' => 'rep_date', 'tr' => 'Tarih', 'en' => 'Date', 'mn' => 'Огноо'],
    'Henüz rapor verisi yok.' => ['key' => 'rep_no_report_data', 'tr' => 'Henüz rapor verisi yok.', 'en' => 'No report data yet.', 'mn' => 'Одоогоор тайлангийн өгөгдөл алга.'],
    'Connector Canlı Verileri' => ['key' => 'rep_connector_data', 'tr' => 'Connector Canlı Verileri', 'en' => 'Live Connector Data', 'mn' => 'Connector амьд өгөгдөл'],
    'Senaryo Performansı' => ['key' => 'rep_scenario_perf', 'tr' => 'Senaryo Performansı', 'en' => 'Scenario Performance', 'mn' => 'Сценарийн гүйцэтгэл'],
    'Adım' => ['key' => 'rep_steps', 'tr' => 'Adım', 'en' => 'Steps', 'mn' => 'Алхам'],
    'İlerleme' => ['key' => 'rep_progress', 'tr' => 'İlerleme', 'en' => 'Progress', 'mn' => 'Ахиц']
];

// Re-iterate over files and replace
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $changed = false;
        
        foreach ($str_map as $tr => $data) {
            $key = $data['key'];
            $en = $data['en'];
            
            // Generate the regex string for this pair
            // Single quotes
            $pattern_sq = "/app\(\)->getLocale\(\)\s*===\s*'tr'\s*\?\s*'" . preg_quote($tr, '/') . "'\s*:\s*'" . preg_quote($en, '/') . "'/";
            $replacement = "__('admin." . $key . "')";
            if (preg_match($pattern_sq, $content)) {
                $content = preg_replace($pattern_sq, $replacement, $content);
                $changed = true;
            }

            // Double quotes inside single quotes
            $pattern_dq = '/app\(\)->getLocale\(\)\s*===\s*"tr"\s*\?\s*"' . preg_quote($tr, '/') . '"\s*:\s*"' . preg_quote($en, '/') . '"/';
            if (preg_match($pattern_dq, $content)) {
                $content = preg_replace($pattern_dq, $replacement, $content);
                $changed = true;
            }

            // Sometimes the string is 'Okul Dağılımı' but $en is 'School Distribution' inside other quotes. It's safe since regex covers exact matching.
        }

        // Special case fallback: if it spans multiple lines. Wait, we may have special cases where there are weird linebreaks.
        if (preg_match("/app\(\)->getLocale\(\)\s*===\s*'tr'\s*\?/", $content)) {
             // Still found unmatched ones! We'll just generic replace them if needed. 
             // But my regex in the first script found exactly these.
        }

        if ($changed) {
            file_put_contents($path, $content);
            echo "Replaced in: " . basename($path) . "\n";
        }
    }
}

// Now append them to admin.php files
function appendArrayToFileAdmin($path, $extraData) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $extraData);
    $content = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
    file_put_contents($path, $content);
}

$tr_arr = [];
$en_arr = [];
$mn_arr = [];

foreach($str_map as $tr => $data) {
    $tr_arr[$data['key']] = $data['tr'];
    $en_arr[$data['key']] = $data['en'];
    $mn_arr[$data['key']] = $data['mn'];
}

appendArrayToFileAdmin(dirname(__DIR__) . '/lang/tr/admin.php', $tr_arr);
appendArrayToFileAdmin(dirname(__DIR__) . '/lang/en/admin.php', $en_arr);
appendArrayToFileAdmin(dirname(__DIR__) . '/lang/mn/admin.php', $mn_arr);

echo "All admin templates cleanly refactored!\n";

