<?php
$chip_keys = [
    'tr' => [
        // Mission WAY chips
        'chip_mw_multiplayer'  => 'Çok Oyunculu',
        'chip_mw_role'         => 'Rol Tabanlı',
        'chip_mw_scoring'      => 'Anlık Puanlama',
        'chip_mw_assignments'  => 'Görevler',
        // Way Startup chips
        'chip_ws_ai'           => 'YZ Değerlendirme',
        'chip_ws_upload'       => 'Dosya Yükleme',
        'chip_ws_points'       => 'Puan Sistemi',
        'chip_ws_steps'        => 'Adım Adım',
        // Role Galaxy chips
        'chip_rg_ai'           => 'YZ Üretimli',
        'chip_rg_branching'    => 'Dallanan Hikayeler',
        'chip_rg_career'       => 'Kariyer Keşfi',
        // WAY AI Coach chips
        'chip_coach_chat'      => 'Anlık Sohbet',
        'chip_coach_personal'  => 'Kişiselleştirilmiş',
        'chip_coach_ws'        => 'WebSocket',
        // Study Space chips
        'chip_ss_subject'      => 'Derse Göre',
        'chip_ss_grade'        => 'Seviye Bazlı',
        'chip_ss_interactive'  => 'İnteraktif',
        'chip_ss_history'      => 'Oturum Geçmişi',
    ],
    'en' => [
        'chip_mw_multiplayer'  => 'Multiplayer',
        'chip_mw_role'         => 'Role-Based',
        'chip_mw_scoring'      => 'Real-time Scoring',
        'chip_mw_assignments'  => 'Assignments',
        'chip_ws_ai'           => 'AI Evaluation',
        'chip_ws_upload'       => 'File Upload',
        'chip_ws_points'       => 'Points System',
        'chip_ws_steps'        => 'Step-by-Step',
        'chip_rg_ai'           => 'AI-Generated',
        'chip_rg_branching'    => 'Branching Stories',
        'chip_rg_career'       => 'Career Exploration',
        'chip_coach_chat'      => 'Real-time Chat',
        'chip_coach_personal'  => 'Personalized',
        'chip_coach_ws'        => 'WebSocket',
        'chip_ss_subject'      => 'Subject-Based',
        'chip_ss_grade'        => 'Grade-Level',
        'chip_ss_interactive'  => 'Interactive',
        'chip_ss_history'      => 'Session History',
    ],
    'mn' => [
        'chip_mw_multiplayer'  => 'Олон Тоглогч',
        'chip_mw_role'         => 'Дүрд Суурилсан',
        'chip_mw_scoring'      => 'Цаг Алдалгүй Оноо',
        'chip_mw_assignments'  => 'Даалгавар',
        'chip_ws_ai'           => 'ХИ Үнэлгээ',
        'chip_ws_upload'       => 'Файл Оруулах',
        'chip_ws_points'       => 'Оноо Систем',
        'chip_ws_steps'        => 'Алхам Дараалал',
        'chip_rg_ai'           => 'ХИ Үүсгэсэн',
        'chip_rg_branching'    => 'Салаалсан Түүхүүд',
        'chip_rg_career'       => 'Карьер Судалгаа',
        'chip_coach_chat'      => 'Шуурхай Чат',
        'chip_coach_personal'  => 'Хувийн Тохиргоо',
        'chip_coach_ws'        => 'WebSocket',
        'chip_ss_subject'      => 'Хичээлд Суурилсан',
        'chip_ss_grade'        => 'Ангийн Түвшин',
        'chip_ss_interactive'  => 'Интерактив',
        'chip_ss_history'      => 'Сессийн Түүх',
    ],
];

function appendToPortal($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}

appendToPortal(dirname(__DIR__) . '/lang/tr/portal.php', $chip_keys['tr']);
appendToPortal(dirname(__DIR__) . '/lang/en/portal.php', $chip_keys['en']);
appendToPortal(dirname(__DIR__) . '/lang/mn/portal.php', $chip_keys['mn']);

echo "All chip keys appended to tr, en, mn!";
