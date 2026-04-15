<?php
/**
 * BULK PORTAL LOCALIZATION SCRIPT
 * Replaces hardcoded English strings in all portal blade files with __() calls
 * and appends the translation keys to lang/{tr,en,mn}/portal.php
 */

$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

// ═══════════════════════════════════════════════════
// STEP 1: Define all replacements (exact string → key)
// ═══════════════════════════════════════════════════
// Format: 'exact HTML to find' => 'replacement HTML'
// We target text BETWEEN tags, not attributes

$replacements = [
    // ─── Section Titles / Headers ───
    '>License Management<'          => '>{{ __(\'portal.license_management\') }}<',
    '>Administration<'              => '>{{ __(\'admin.dashboard\') }}<',
    '>Add New License<'             => '>{{ __(\'portal.add_new_license\') }}<',
    '>Request Additional Seats<'    => '>{{ __(\'portal.request_additional_seats\') }}<',
    '>License Status<'              => '>{{ __(\'portal.license_status\') }}<',
    '>Recently Added Students<'     => '>{{ __(\'portal.recently_added_students\') }}<',
    '>My Seat Requests<'            => '>{{ __(\'portal.my_seat_requests\') }}<',
    '>Number of Additional Seats *<'=> '>{{ __(\'portal.num_additional_seats\') }} *<',
    '>Reason / Notes<'              => '>{{ __(\'portal.reason_notes\') }}<',
    '>Submit Request<'              => '>{{ __(\'portal.submit_request\') }}<',
    '>Fill in the license details below.<' => '>{{ __(\'portal.fill_license_details\') }}<',
    '>Submit a request to your administrator for additional student seats.<' => '>{{ __(\'portal.seat_request_subtitle\') }}<',

    // ─── Table Headers ───
    '>School Name<'                 => '>{{ __(\'admin.school_name\') }}<',
    '>Country/State<'               => '>{{ __(\'portal.country_state\') }}<',
    '>Total Licenses<'              => '>{{ __(\'portal.total_licenses\') }}<',
    '>Purchase Date<'               => '>{{ __(\'portal.purchase_date\') }}<',
    '>License Duration<'            => '>{{ __(\'portal.license_duration\') }}<',
    '>E-mail<'                      => '>{{ __(\'admin.email\') }}<',
    '>Actions<'                     => '>{{ __(\'admin.actions\') }}<',
    '>Date<'                        => '>{{ __(\'admin.date\') }}<',
    '>Seats Requested<'             => '>{{ __(\'portal.seats_requested\') }}<',
    '>Reason<'                      => '>{{ __(\'portal.reason\') }}<',
    '>Admin Response<'              => '>{{ __(\'portal.admin_response\') }}<',
    '>Status<'                      => '>{{ __(\'admin.status\') }}<',
    '>No<'                          => '>{{ __(\'portal.no_num\') }}<',

    // ─── Stat Card Labels ───
    '>Students<'                    => '>{{ __(\'portal.nav_students\') }}<',
    '>Teachers<'                    => '>{{ __(\'portal.nav_teachers\') }}<',
    '>Classes<'                     => '>{{ __(\'admin.classes\') }}<',
    '>Available Seats<'             => '>{{ __(\'portal.available_seats\') }}<',
    '>Seat Usage<'                  => '>{{ __(\'portal.seat_usage\') }}<',
    '>Total Seats<'                 => '>{{ __(\'portal.total_seats\') }}<',
    '>Available<'                   => '>{{ __(\'portal.available\') }}<',
    '>Start Date<'                  => '>{{ __(\'portal.start_date\') }}<',
    '>Expiry Date<'                 => '>{{ __(\'portal.expiry_date\') }}<',
    '>End Date<'                    => '>{{ __(\'portal.end_date\') }}<',

    // ─── Vega App Cards ───
    '>Simulations<'                 => '>{{ __(\'portal.simulations\') }}<',
    '>Avg Score<'                   => '>{{ __(\'portal.avg_score\') }}<',
    '>Active Students<'             => '>{{ __(\'portal.active_students\') }}<',
    '>Completed<'                   => '>{{ __(\'portal.completed\') }}<',
    '>Discussions<'                 => '>{{ __(\'portal.discussions\') }}<',
    '>Messages<'                    => '>{{ __(\'portal.messages\') }}<',
    '>Sessions<'                    => '>{{ __(\'portal.sessions\') }}<',

    // ─── Status Badges ───
    ">\n                                Active\n"  => ">\n                                {{ __('portal.active') }}\n",
    '>Active<'                      => '>{{ __(\'portal.active\') }}<',
    '>Not Started<'                 => '>{{ __(\'portal.not_started\') }}<',
    '>Pending<'                     => '>{{ __(\'portal.pending\') }}<',
    '>Approved<'                    => '>{{ __(\'portal.approved\') }}<',
    '>Rejected<'                    => '>{{ __(\'portal.rejected\') }}<',
    '>Expired<'                     => '>{{ __(\'portal.expired\') }}<',
    '>No License<'                  => '>{{ __(\'portal.no_license\') }}<',

    // ─── Buttons ───
    '>Cancel<'                      => '>{{ __(\'portal.cancel\') }}<',
    '>Save<'                        => '>{{ __(\'admin.save\') }}<',
    '>Delete<'                      => '>{{ __(\'admin.delete\') }}<',
    '>Edit<'                        => '>{{ __(\'admin.edit\') }}<',
    '>Update<'                      => '>{{ __(\'admin.save\') }}<',

    // ─── Links ───
    '>View all →<'                  => '>{{ __(\'portal.view_all\') }} →<',
    '>View Details →<'              => '>{{ __(\'portal.view_details\') }} →<',

    // ─── Empty States ───
    '>No licenses found.<'          => '>{{ __(\'portal.no_licenses_found\') }}<',
    'No active license found. Contact your administrator.' => '{{ __(\'portal.no_active_license\') }}',
    'No students yet.'              => '{{ __(\'portal.no_students_yet\') }}',
    '>Add your first student →<'    => '>{{ __(\'portal.add_first_student\') }} →<',

    // ─── Pagination ───
    '>Previous<'                    => '>{{ __(\'portal.previous\') }}<',
    '>Next<'                        => '>{{ __(\'portal.next\') }}<',

    // ─── Form Labels ───
    '>School *<'                    => '>{{ __(\'admin.school_name\') }} *<',
    '>Number of Seats *<'           => '>{{ __(\'portal.number_of_seats\') }} *<',
    '>Select a school...<'          => '>{{ __(\'portal.select_school\') }}<',

    // ─── Page Titles (section) ───
    "'title', 'License Management'" => "'title', __('portal.license_management')",
    "'title', 'Dashboard'"          => "'title', __('admin.dashboard')",
    "'page-title', 'Administration'" => "'page-title', __('admin.dashboard')",
    "'page-title', 'Dashboard'"     => "'page-title', __('admin.dashboard')",

    // ─── Dashboard-student / teacher ───
    '>My Applications<'             => '>{{ __(\'portal.my_applications\') }}<',
    '>My Classes<'                  => '>{{ __(\'portal.my_classes\') }}<',
    '>Class Name<'                  => '>{{ __(\'portal.class_name\') }}<',
    '>Total Students<'              => '>{{ __(\'portal.total_students\') }}<',
    '>Application<'                 => '>{{ __(\'portal.application\') }}<',
    '>Progress<'                    => '>{{ __(\'portal.progress\') }}<',
    '>Last Activity<'               => '>{{ __(\'portal.last_activity\') }}<',
    '>Score<'                       => '>{{ __(\'portal.score\') }}<',
    '>View<'                        => '>{{ __(\'portal.view\') }}<',

    // ─── Hierarchy page ───
    '>School Hierarchy<'            => '>{{ __(\'portal.school_hierarchy\') }}<',
    '>students<'                    => '>{{ __(\'portal.students_lc\') }}<',
    '>teachers<'                    => '>{{ __(\'portal.teachers_lc\') }}<',

    // ─── Confirm dialogs ───
    "confirm('Are you sure you want to delete this license?')" => "confirm('{{ __(\'portal.confirm_delete_license\') }}')",
    "confirm('Are you sure')"       => "confirm('{{ __(\'portal.confirm_action\') }}')",
    "confirm('Delete this"          => "confirm('{{ __(\'portal.confirm_delete\') }}",
];

// ═══════════════════════════════════════════════════
// STEP 2: Apply replacements to all blade files
// ═══════════════════════════════════════════════════

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

$totalReplacements = 0;
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
        echo "✓ Modified: $relPath\n";
        $fileCount++;
    }
}

echo "\n═══ Modified $fileCount files ═══\n";

// ═══════════════════════════════════════════════════
// STEP 3: Add all translation keys
// ═══════════════════════════════════════════════════

$portalKeys = [
    'tr' => [
        'license_management'      => 'Lisans Yönetimi',
        'add_new_license'         => 'Yeni Lisans Ekle',
        'request_additional_seats'=> 'Ek Koltuk Talep Et',
        'license_status'          => 'Lisans Durumu',
        'recently_added_students' => 'Son Eklenen Öğrenciler',
        'my_seat_requests'        => 'Koltuk Taleplerim',
        'num_additional_seats'    => 'Ek Koltuk Sayısı',
        'reason_notes'            => 'Sebep / Notlar',
        'submit_request'          => 'Talep Gönder',
        'fill_license_details'    => 'Aşağıdaki lisans bilgilerini doldurun.',
        'seat_request_subtitle'   => 'Yöneticinize ek öğrenci koltuğu talebi gönderin.',
        'country_state'           => 'Ülke/Şehir',
        'total_licenses'          => 'Toplam Lisans',
        'purchase_date'           => 'Satın Alma Tarihi',
        'license_duration'        => 'Lisans Süresi',
        'seats_requested'         => 'Talep Edilen Koltuk',
        'reason'                  => 'Sebep',
        'admin_response'          => 'Yönetici Yanıtı',
        'no_num'                  => 'No',
        'available_seats'         => 'Kullanılabilir Koltuk',
        'seat_usage'              => 'Koltuk Kullanımı',
        'total_seats'             => 'Toplam Koltuk',
        'available'               => 'Kullanılabilir',
        'start_date'              => 'Başlangıç Tarihi',
        'expiry_date'             => 'Bitiş Tarihi',
        'end_date'                => 'Bitiş Tarihi',
        'simulations'             => 'Simülasyonlar',
        'avg_score'               => 'Ort. Puan',
        'active_students'         => 'Aktif Öğrenciler',
        'completed'               => 'Tamamlanan',
        'discussions'             => 'Tartışmalar',
        'messages'                => 'Mesajlar',
        'sessions'                => 'Oturumlar',
        'active'                  => 'Aktif',
        'not_started'             => 'Başlamadı',
        'pending'                 => 'Beklemede',
        'approved'                => 'Onaylandı',
        'rejected'                => 'Reddedildi',
        'expired'                 => 'Süresi Doldu',
        'no_license'              => 'Lisans Yok',
        'cancel'                  => 'İptal',
        'view_all'                => 'Tümünü Gör',
        'view_details'            => 'Detayları Gör',
        'no_licenses_found'       => 'Lisans bulunamadı.',
        'no_active_license'       => 'Aktif lisans bulunamadı. Yöneticinizle iletişime geçin.',
        'no_students_yet'         => 'Henüz öğrenci yok.',
        'add_first_student'       => 'İlk öğrencinizi ekleyin',
        'previous'                => 'Önceki',
        'next'                    => 'Sonraki',
        'number_of_seats'         => 'Koltuk Sayısı',
        'select_school'           => 'Okul seçin...',
        'my_applications'         => 'Uygulamalarım',
        'my_classes'              => 'Sınıflarım',
        'class_name'              => 'Sınıf Adı',
        'total_students'          => 'Toplam Öğrenci',
        'application'             => 'Uygulama',
        'progress'                => 'İlerleme',
        'last_activity'           => 'Son Aktivite',
        'score'                   => 'Puan',
        'view'                    => 'Görüntüle',
        'school_hierarchy'        => 'Okul Hiyerarşisi',
        'students_lc'             => 'öğrenci',
        'teachers_lc'             => 'öğretmen',
        'confirm_delete_license'  => 'Bu lisansı silmek istediğinizden emin misiniz?',
        'confirm_action'          => 'Emin misiniz?',
        'confirm_delete'          => 'Silmek istediğinizden emin misiniz?',
    ],
    'en' => [
        'license_management'      => 'License Management',
        'add_new_license'         => 'Add New License',
        'request_additional_seats'=> 'Request Additional Seats',
        'license_status'          => 'License Status',
        'recently_added_students' => 'Recently Added Students',
        'my_seat_requests'        => 'My Seat Requests',
        'num_additional_seats'    => 'Number of Additional Seats',
        'reason_notes'            => 'Reason / Notes',
        'submit_request'          => 'Submit Request',
        'fill_license_details'    => 'Fill in the license details below.',
        'seat_request_subtitle'   => 'Submit a request to your administrator for additional student seats.',
        'country_state'           => 'Country/State',
        'total_licenses'          => 'Total Licenses',
        'purchase_date'           => 'Purchase Date',
        'license_duration'        => 'License Duration',
        'seats_requested'         => 'Seats Requested',
        'reason'                  => 'Reason',
        'admin_response'          => 'Admin Response',
        'no_num'                  => 'No',
        'available_seats'         => 'Available Seats',
        'seat_usage'              => 'Seat Usage',
        'total_seats'             => 'Total Seats',
        'available'               => 'Available',
        'start_date'              => 'Start Date',
        'expiry_date'             => 'Expiry Date',
        'end_date'                => 'End Date',
        'simulations'             => 'Simulations',
        'avg_score'               => 'Avg Score',
        'active_students'         => 'Active Students',
        'completed'               => 'Completed',
        'discussions'             => 'Discussions',
        'messages'                => 'Messages',
        'sessions'                => 'Sessions',
        'active'                  => 'Active',
        'not_started'             => 'Not Started',
        'pending'                 => 'Pending',
        'approved'                => 'Approved',
        'rejected'                => 'Rejected',
        'expired'                 => 'Expired',
        'no_license'              => 'No License',
        'cancel'                  => 'Cancel',
        'view_all'                => 'View all',
        'view_details'            => 'View Details',
        'no_licenses_found'       => 'No licenses found.',
        'no_active_license'       => 'No active license found. Contact your administrator.',
        'no_students_yet'         => 'No students yet.',
        'add_first_student'       => 'Add your first student',
        'previous'                => 'Previous',
        'next'                    => 'Next',
        'number_of_seats'         => 'Number of Seats',
        'select_school'           => 'Select a school...',
        'my_applications'         => 'My Applications',
        'my_classes'              => 'My Classes',
        'class_name'              => 'Class Name',
        'total_students'          => 'Total Students',
        'application'             => 'Application',
        'progress'                => 'Progress',
        'last_activity'           => 'Last Activity',
        'score'                   => 'Score',
        'view'                    => 'View',
        'school_hierarchy'        => 'School Hierarchy',
        'students_lc'             => 'students',
        'teachers_lc'             => 'teachers',
        'confirm_delete_license'  => 'Are you sure you want to delete this license?',
        'confirm_action'          => 'Are you sure?',
        'confirm_delete'          => 'Are you sure you want to delete this?',
    ],
    'mn' => [
        'license_management'      => 'Лицензийн менежмент',
        'add_new_license'         => 'Шинэ лиценз нэмэх',
        'request_additional_seats'=> 'Нэмэлт суудал хүсэх',
        'license_status'          => 'Лицензийн төлөв',
        'recently_added_students' => 'Сүүлд нэмсэн сурагчид',
        'my_seat_requests'        => 'Миний суудлын хүсэлтүүд',
        'num_additional_seats'    => 'Нэмэлт суудлын тоо',
        'reason_notes'            => 'Шалтгаан / Тэмдэглэл',
        'submit_request'          => 'Хүсэлт илгээх',
        'fill_license_details'    => 'Лицензийн мэдээллийг бөглөнө үү.',
        'seat_request_subtitle'   => 'Нэмэлт суудлын хүсэлтийг администраторт илгээнэ.',
        'country_state'           => 'Улс/Хот',
        'total_licenses'          => 'Нийт лиценз',
        'purchase_date'           => 'Худалдан авсан огноо',
        'license_duration'        => 'Лицензийн хугацаа',
        'seats_requested'         => 'Хүсэлт гаргасан суудал',
        'reason'                  => 'Шалтгаан',
        'admin_response'          => 'Админ хариулт',
        'no_num'                  => '№',
        'available_seats'         => 'Боломжит суудал',
        'seat_usage'              => 'Суудлын ашиглалт',
        'total_seats'             => 'Нийт суудал',
        'available'               => 'Боломжтой',
        'start_date'              => 'Эхлэх огноо',
        'expiry_date'             => 'Дуусах огноо',
        'end_date'                => 'Дуусах огноо',
        'simulations'             => 'Симуляци',
        'avg_score'               => 'Дундаж оноо',
        'active_students'         => 'Идэвхтэй сурагчид',
        'completed'               => 'Дууссан',
        'discussions'             => 'Хэлэлцүүлэг',
        'messages'                => 'Мессеж',
        'sessions'                => 'Сессүүд',
        'active'                  => 'Идэвхтэй',
        'not_started'             => 'Эхлээгүй',
        'pending'                 => 'Хүлээгдэж буй',
        'approved'                => 'Зөвшөөрсөн',
        'rejected'                => 'Татгалзсан',
        'expired'                 => 'Дууссан',
        'no_license'              => 'Лиценз байхгүй',
        'cancel'                  => 'Цуцлах',
        'view_all'                => 'Бүгдийг харах',
        'view_details'            => 'Дэлгэрэнгүй',
        'no_licenses_found'       => 'Лиценз олдсонгүй.',
        'no_active_license'       => 'Идэвхтэй лиценз олдсонгүй. Администратортай холбогдоно уу.',
        'no_students_yet'         => 'Одоохондоо сурагч байхгүй.',
        'add_first_student'       => 'Анхны сурагчаа нэмнэ үү',
        'previous'                => 'Өмнөх',
        'next'                    => 'Дараах',
        'number_of_seats'         => 'Суудлын тоо',
        'select_school'           => 'Сургууль сонгох...',
        'my_applications'         => 'Миний програмууд',
        'my_classes'              => 'Миний ангиуд',
        'class_name'              => 'Ангийн нэр',
        'total_students'          => 'Нийт сурагч',
        'application'             => 'Програм',
        'progress'                => 'Явц',
        'last_activity'           => 'Сүүлийн идэвхи',
        'score'                   => 'Оноо',
        'view'                    => 'Үзэх',
        'school_hierarchy'        => 'Сургуулийн бүтэц',
        'students_lc'             => 'сурагч',
        'teachers_lc'             => 'багш нар',
        'confirm_delete_license'  => 'Энэ лицензийг устгахдаа итгэлтэй байна уу?',
        'confirm_action'          => 'Та итгэлтэй байна уу?',
        'confirm_delete'          => 'Устгахдаа итгэлтэй байна уу?',
    ],
];

function appendToLangFile($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}

appendToLangFile($basePath . '/lang/tr/portal.php', $portalKeys['tr']);
appendToLangFile($basePath . '/lang/en/portal.php', $portalKeys['en']);
appendToLangFile($basePath . '/lang/mn/portal.php', $portalKeys['mn']);

echo "\n✅ Translation keys appended to all 3 languages!\n";
echo "═══ BULK LOCALIZATION COMPLETE ═══\n";
