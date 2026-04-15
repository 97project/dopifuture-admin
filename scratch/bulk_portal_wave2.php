<?php
/**
 * BULK PORTAL LOCALIZATION - WAVE 2
 * Catches remaining hardcoded strings missed by wave 1
 */

$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

$replacements = [
    // ─── Form Labels ───
    '>Class Name *<'                => '>{{ __(\'portal.class_name\') }} *<',
    '>Grade Level<'                 => '>{{ __(\'portal.grade_level\') }}<',
    '>Academic Year<'               => '>{{ __(\'portal.academic_year\') }}<',
    '>First Name *<'                => '>{{ __(\'admin.name\') }} *<',
    '>Last Name<'                   => '>{{ __(\'admin.surname\') }}<',
    '>Assign to Class<'             => '>{{ __(\'portal.assign_to_class\') }}<',
    '>CSV File *<'                  => '>{{ __(\'portal.csv_file\') }} *<',
    '>School Name *<'               => '>{{ __(\'admin.school_name\') }} *<',
    '>Seat Count *<'                => '>{{ __(\'portal.number_of_seats\') }} *<',
    '>Purchase Date *<'             => '>{{ __(\'portal.purchase_date\') }} *<',

    // ─── Select Placeholders ───
    '>Select student to add...<'    => '>{{ __(\'portal.select_student\') }}<',
    '>Select teacher to add...<'    => '>{{ __(\'portal.select_teacher\') }}<',
    '>Select School<'               => '>{{ __(\'portal.select_school\') }}<',
    '>Please select<'               => '>{{ __(\'portal.please_select\') }}<',

    // ─── Report / Stat Labels ───
    '>Total Sessions<'              => '>{{ __(\'portal.total_sessions\') }}<',
    '>Total Duration<'              => '>{{ __(\'portal.total_duration\') }}<',
    '>Total Messages<'              => '>{{ __(\'portal.total_messages\') }}<',
    '>Total Simulations<'           => '>{{ __(\'portal.total_simulations\') }}<',
    '>Total Users<'                 => '>{{ __(\'portal.total_users\') }}<',
    '>Active Apps<'                 => '>{{ __(\'portal.active_apps\') }}<',
    '>Scenarios Explored<'          => '>{{ __(\'portal.scenarios_explored\') }}<',
    '>Used Seats<'                  => '>{{ __(\'portal.used_seats\') }}<',
    '>Remaining Seats<'             => '>{{ __(\'portal.remaining_seats\') }}<',
    '>Mission Name<'                => '>{{ __(\'portal.mission_name\') }}<',
    '>Assigned Date<'               => '>{{ __(\'portal.assigned_date\') }}<',
    '>System Point<'                => '>{{ __(\'portal.system_point\') }}<',
    '>Teacher Point<'               => '>{{ __(\'portal.teacher_point\') }}<',
    '>Total Discussion Minute<'     => '>{{ __(\'portal.total_discussion_min\') }}<',
    '>Total Discussion Count<'      => '>{{ __(\'portal.total_discussion_count\') }}<',
    '>Average Galaxy Join<'         => '>{{ __(\'portal.avg_galaxy_join\') }}<',
    '>Average Duration<'            => '>{{ __(\'portal.avg_duration\') }}<',
    '>Total Role Galaxies Joined<'  => '>{{ __(\'portal.total_galaxies_joined\') }}<',
    '>Galaxy Selected<'             => '>{{ __(\'portal.galaxy_selected\') }}<',
    '>Role Played<'                 => '>{{ __(\'portal.role_played\') }}<',
    '>Discussion Minutes<'          => '>{{ __(\'portal.discussion_minutes\') }}<',
    '>Discussion Count<'            => '>{{ __(\'portal.discussion_count\') }}<',
    '>Module Distribution<'         => '>{{ __(\'portal.module_distribution\') }}<',
    '>Daily Sessions (Last 30 Days)<' => '>{{ __(\'portal.daily_sessions_30\') }}<',
    '>Global Skill Matrix<'         => '>{{ __(\'portal.global_skill_matrix\') }}<',
    '>Live Activity Feed<'          => '>{{ __(\'portal.live_activity_feed\') }}<',
    '>Sync Progress<'               => '>{{ __(\'portal.sync_progress\') }}<',
    '>Purchase History<'            => '>{{ __(\'portal.purchase_history\') }}<',
    '>Add New Purchase<'            => '>{{ __(\'portal.add_new_purchase\') }}<',
    '>Add Purchase<'                => '>{{ __(\'portal.add_purchase\') }}<',

    // ─── Modal Titles ───
    '>Reset Password<'              => '>{{ __(\'portal.reset_password\') }}<',

    // ─── Reports Page Subtitle ───
    'Application usage, license analytics and per-school distribution reports.' => '{{ __(\'portal.reports_subtitle\') }}',

    // ─── Confirm dialogs (remaining) ───
    "confirm('Are you sure you want to reset this user" => "confirm('{{ __(\'portal.confirm_reset_password\') }}",

    // ─── Page titles ───
    "'title', 'Reports'"            => "'title', __('admin.reports')",
    "'title', 'Profile'"            => "'title', __('admin.profile')",
    "'title', 'Classes'"            => "'title', __('admin.classes')",
    "'title', 'Users'"              => "'title', __('portal.nav_students')",
    "'title', 'Schools'"            => "'title', __('admin.schools')",
    "'title', 'Licenses'"           => "'title', __('portal.license_management')",

    "'page-title', 'Reports'"       => "'page-title', __('admin.reports')",
    "'page-title', 'Classes'"       => "'page-title', __('admin.classes')",
    "'page-title', 'Profile'"       => "'page-title', __('admin.profile')",
    "'page-title', 'Users'"         => "'page-title', __('portal.nav_students')",
    "'page-title', 'Schools'"       => "'page-title', __('admin.schools')",
    "'page-title', 'Licenses'"      => "'page-title', __('portal.license_management')",
];

// Apply replacements
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$fileCount = 0;

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    $original = $content;
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $relPath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
        echo "✓ $relPath\n";
        $fileCount++;
    }
}
echo "\n═══ Wave 2: Modified $fileCount files ═══\n";

// Add translation keys
$keys = [
    'tr' => [
        'grade_level'             => 'Sınıf Seviyesi',
        'academic_year'           => 'Akademik Yıl',
        'assign_to_class'         => 'Sınıfa Ata',
        'csv_file'                => 'CSV Dosyası',
        'select_student'          => 'Öğrenci seçin...',
        'select_teacher'          => 'Öğretmen seçin...',
        'please_select'           => 'Lütfen seçin',
        'total_sessions'          => 'Toplam Oturum',
        'total_duration'          => 'Toplam Süre',
        'total_messages'          => 'Toplam Mesaj',
        'total_simulations'       => 'Toplam Simülasyon',
        'total_users'             => 'Toplam Kullanıcı',
        'active_apps'             => 'Aktif Uygulamalar',
        'scenarios_explored'      => 'Keşfedilen Senaryolar',
        'used_seats'              => 'Kullanılan Koltuk',
        'remaining_seats'         => 'Kalan Koltuk',
        'mission_name'            => 'Görev Adı',
        'assigned_date'           => 'Atanma Tarihi',
        'system_point'            => 'Sistem Puanı',
        'teacher_point'           => 'Öğretmen Puanı',
        'total_discussion_min'    => 'Toplam Tartışma Dakikası',
        'total_discussion_count'  => 'Toplam Tartışma Sayısı',
        'avg_galaxy_join'         => 'Ortalama Galaxy Katılım',
        'avg_duration'            => 'Ortalama Süre',
        'total_galaxies_joined'   => 'Toplam Katılınan Galaxy',
        'galaxy_selected'         => 'Seçilen Galaxy',
        'role_played'             => 'Oynanan Rol',
        'discussion_minutes'      => 'Tartışma Dakikası',
        'discussion_count'        => 'Tartışma Sayısı',
        'module_distribution'     => 'Modül Dağılımı',
        'daily_sessions_30'       => 'Günlük Oturumlar (Son 30 Gün)',
        'global_skill_matrix'     => 'Genel Yetenek Matrisi',
        'live_activity_feed'      => 'Canlı Aktivite Akışı',
        'sync_progress'           => 'Senkronizasyon İlerlemesi',
        'purchase_history'        => 'Satın Alma Geçmişi',
        'add_new_purchase'        => 'Yeni Satın Alma Ekle',
        'add_purchase'            => 'Satın Alma Ekle',
        'reset_password'          => 'Şifre Sıfırla',
        'reports_subtitle'        => 'Uygulama kullanımı, lisans analitiği ve okul bazlı dağılım raporları.',
        'confirm_reset_password'  => 'Bu kullanıcının şifresini sıfırlamak istediğinizden emin misiniz?',
    ],
    'en' => [
        'grade_level'             => 'Grade Level',
        'academic_year'           => 'Academic Year',
        'assign_to_class'         => 'Assign to Class',
        'csv_file'                => 'CSV File',
        'select_student'          => 'Select student to add...',
        'select_teacher'          => 'Select teacher to add...',
        'please_select'           => 'Please select',
        'total_sessions'          => 'Total Sessions',
        'total_duration'          => 'Total Duration',
        'total_messages'          => 'Total Messages',
        'total_simulations'       => 'Total Simulations',
        'total_users'             => 'Total Users',
        'active_apps'             => 'Active Apps',
        'scenarios_explored'      => 'Scenarios Explored',
        'used_seats'              => 'Used Seats',
        'remaining_seats'         => 'Remaining Seats',
        'mission_name'            => 'Mission Name',
        'assigned_date'           => 'Assigned Date',
        'system_point'            => 'System Point',
        'teacher_point'           => 'Teacher Point',
        'total_discussion_min'    => 'Total Discussion Minute',
        'total_discussion_count'  => 'Total Discussion Count',
        'avg_galaxy_join'         => 'Average Galaxy Join',
        'avg_duration'            => 'Average Duration',
        'total_galaxies_joined'   => 'Total Role Galaxies Joined',
        'galaxy_selected'         => 'Galaxy Selected',
        'role_played'             => 'Role Played',
        'discussion_minutes'      => 'Discussion Minutes',
        'discussion_count'        => 'Discussion Count',
        'module_distribution'     => 'Module Distribution',
        'daily_sessions_30'       => 'Daily Sessions (Last 30 Days)',
        'global_skill_matrix'     => 'Global Skill Matrix',
        'live_activity_feed'      => 'Live Activity Feed',
        'sync_progress'           => 'Sync Progress',
        'purchase_history'        => 'Purchase History',
        'add_new_purchase'        => 'Add New Purchase',
        'add_purchase'            => 'Add Purchase',
        'reset_password'          => 'Reset Password',
        'reports_subtitle'        => 'Application usage, license analytics and per-school distribution reports.',
        'confirm_reset_password'  => 'Are you sure you want to reset this user\'s password?',
    ],
    'mn' => [
        'grade_level'             => 'Ангийн түвшин',
        'academic_year'           => 'Хичээлийн жил',
        'assign_to_class'         => 'Анги руу хуваарилах',
        'csv_file'                => 'CSV Файл',
        'select_student'          => 'Сурагч сонгох...',
        'select_teacher'          => 'Багш сонгох...',
        'please_select'           => 'Сонгоно уу',
        'total_sessions'          => 'Нийт сессүүд',
        'total_duration'          => 'Нийт хугацаа',
        'total_messages'          => 'Нийт мессеж',
        'total_simulations'       => 'Нийт симуляци',
        'total_users'             => 'Нийт хэрэглэгч',
        'active_apps'             => 'Идэвхтэй апп',
        'scenarios_explored'      => 'Судалсан сценарууд',
        'used_seats'              => 'Ашигласан суудал',
        'remaining_seats'         => 'Үлдсэн суудал',
        'mission_name'            => 'Даалгаврын нэр',
        'assigned_date'           => 'Хуваарилсан огноо',
        'system_point'            => 'Системийн оноо',
        'teacher_point'           => 'Багшийн оноо',
        'total_discussion_min'    => 'Нийт хэлэлцүүлэг минут',
        'total_discussion_count'  => 'Нийт хэлэлцүүлгийн тоо',
        'avg_galaxy_join'         => 'Дундаж Galaxy нэгдэлт',
        'avg_duration'            => 'Дундаж хугацаа',
        'total_galaxies_joined'   => 'Нийт нэгдсэн Galaxy',
        'galaxy_selected'         => 'Сонгосон Galaxy',
        'role_played'             => 'Тоглосон дүр',
        'discussion_minutes'      => 'Хэлэлцүүлгийн минут',
        'discussion_count'        => 'Хэлэлцүүлгийн тоо',
        'module_distribution'     => 'Модулийн хуваарилалт',
        'daily_sessions_30'       => 'Өдрийн сессүүд (Сүүлийн 30 өдөр)',
        'global_skill_matrix'     => 'Ерөнхий чадварын матриц',
        'live_activity_feed'      => 'Шууд идэвхийн мэдээ',
        'sync_progress'           => 'Синхрончлолын явц',
        'purchase_history'        => 'Худалдан авалтын түүх',
        'add_new_purchase'        => 'Шинэ худалдан авалт',
        'add_purchase'            => 'Худалдан авалт нэмэх',
        'reset_password'          => 'Нууц үг шинэчлэх',
        'reports_subtitle'        => 'Програмын ашиглалт, лицензийн аналитик ба сургууль тус бүрийн тайлан.',
        'confirm_reset_password'  => 'Энэ хэрэглэгчийн нууц үгийг шинэчлэхдээ итгэлтэй байна уу?',
    ],
];

function appendToLangFile($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}

appendToLangFile($basePath . '/lang/tr/portal.php', $keys['tr']);
appendToLangFile($basePath . '/lang/en/portal.php', $keys['en']);
appendToLangFile($basePath . '/lang/mn/portal.php', $keys['mn']);

echo "✅ Wave 2 keys appended!\n";
