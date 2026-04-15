<?php
/**
 * WAVE 7 — Dashboard pages, pagination, remaining ternaries, greeting messages
 */
$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

$replacements = [
    // ─── Profile ───
    "'My Profile'"           => "__('portal.my_profile')",

    // ─── Hierarchy ───
    "'School Hierarchy'"     => "__('portal.school_hierarchy')",

    // ─── Dashboard Pagination ───
    '>Page '                 => '>{{ __(\'portal.page\') }} ',
    ' of {{ '                => ' {{ __(\'portal.of\') }} {{ ',

    // ─── Dashboard School ───
    "'My School'"            => "__('portal.my_school')",
    '>Request Additional Seats<' => '>{{ __(\'portal.request_additional_seats\') }}<',
    'License expires in'     => '{{ __(\'portal.license_expires_in\') }}',
    'days!'                  => '{{ __(\'portal.days\') }}!',
    'days.'                  => '{{ __(\'portal.days\') }}.',

    // ─── Dashboard Student ───
    'Hello, {{ $user->name }}!' => '{{ __(\'portal.hello_user\', [\'name\' => $user->name]) }}',
    '>View My Report<'       => '>{{ __(\'portal.view_my_report\') }}<',
    '>Edit Profile<'         => '>{{ __(\'portal.edit_profile\') }}<',

    // ─── Dashboard Teacher ───
    'Welcome, {{ $user->name }}' => '{{ __(\'portal.welcome_user\', [\'name\' => $user->name]) }}',
    '>My Classes<'           => '>{{ __(\'portal.my_classes\') }}<',
    '>Recent Students<'      => '>{{ __(\'portal.recently_added_students\') }}<',

    // ─── Register meta ───
    "'Register your school on DopiFuture digital education platform'" => "__('portal.register_meta')",

    // ─── Common "← Back" remaining ───
    '>← Back<'               => '>← {{ __(\'portal.back\') }}<',

    // ─── Common "Active/Inactive" ternaries not yet caught ───
    "? 'Active' : 'Inactive'" => "? __('portal.active') : __('portal.inactive')",

    // ─── Dashboard school seat request table headers ───
    '>Date<'                 => '>{{ __(\'portal.date\') }}<',
    '>Reason<'               => '>{{ __(\'portal.reason\') }}<',
    '>Admin Notes<'          => '>{{ __(\'portal.admin_notes\') }}<',
    '>Requested<'            => '>{{ __(\'portal.requested\') }}<',

    // ─── Common save/cancel/update/create ───
    '>Save Changes<'         => '>{{ __(\'admin.save\') }}<',
    '>Cancel<'               => '>{{ __(\'portal.cancel\') }}<',
    '>Close<'                => '>{{ __(\'portal.close\') }}<',
    '>Submit<'               => '>{{ __(\'portal.submit\') }}<',
    '>Submit Request<'       => '>{{ __(\'portal.submit_request\') }}<',
    '>Create<'               => '>{{ __(\'portal.create\') }}<',
    '>Update<'               => '>{{ __(\'portal.update\') }}<',
    '>Previous<'             => '>{{ __(\'portal.previous\') }}<',
    '>Next<'                 => '>{{ __(\'portal.next\') }}<',
    '>No data<'              => '>{{ __(\'portal.no_data\') }}<',
    '>No records found.<'    => '>{{ __(\'portal.no_records\') }}<',
    '>No results found.<'    => '>{{ __(\'portal.no_results\') }}<',
    '>Showing<'              => '>{{ __(\'portal.showing\') }}<',
    '>View All<'             => '>{{ __(\'portal.view_all\') }}<',
    '>Export<'               => '>{{ __(\'portal.export\') }}<',
    '>Add New<'              => '>{{ __(\'portal.add_new\') }}<',
    '>Assign<'               => '>{{ __(\'portal.assign\') }}<',
    '>Refresh<'              => '>{{ __(\'portal.refresh\') }}<',
    '>Approved<'             => '>{{ __(\'portal.approved\') }}<',
    '>Rejected<'             => '>{{ __(\'portal.rejected\') }}<',
    '>Pending<'              => '>{{ __(\'portal.pending\') }}<',

    // ─── Seat Request Modal ───
    '>Request Additional Seats<' => '>{{ __(\'portal.request_additional_seats\') }}<',
    '>Number of Additional Seats *<' => '>{{ __(\'portal.number_additional_seats\') }} *<',
    '>Reason *<'             => '>{{ __(\'portal.reason\') }} *<',

    // ─── Dashboard Add License Modal ───
    '>Add License<'          => '>{{ __(\'portal.add_license\') }}<',
];

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
echo "\n═══ Wave 7: Modified $fileCount files ═══\n";

$keys = [
    'tr' => [
        'my_profile'=>'Profilim','school_hierarchy'=>'Okul Hiyerarşisi',
        'hierarchy_subtitle'=>'Okul → Sınıf → Öğrenci ağaç görünümü',
        'no_schools_found'=>'İlişkili okul bulunamadı.','more'=>'daha fazla',
        'joined'=>'Katıldı','page'=>'Sayfa','of'=>'/',
        'my_school'=>'Okulumuz','request_additional_seats'=>'Ek Koltuk Talep Et',
        'license_expires_in'=>'Lisans sona eriyor:','days'=>'gün',
        'hello_user'=>'Merhaba, :name!','view_my_report'=>'Raporumu Gör','edit_profile'=>'Profili Düzenle',
        'welcome_user'=>'Hoş geldin, :name','my_classes'=>'Sınıflarım',
        'register_meta'=>'Okulunuzu DopiFuture dijital eğitim platformuna kaydolun',
        'date'=>'Tarih','reason'=>'Sebep','admin_notes'=>'Admin Notları','requested'=>'Talep Edilen',
        'cancel'=>'İptal','close'=>'Kapat','submit'=>'Gönder','submit_request'=>'Talebi Gönder',
        'create'=>'Oluştur','update'=>'Güncelle','previous'=>'Önceki','next'=>'Sonraki',
        'no_data'=>'Veri yok','no_records'=>'Kayıt bulunamadı.','no_results'=>'Sonuç bulunamadı.',
        'showing'=>'Gösteriliyor','view_all'=>'Tümünü Gör','export'=>'Dışa Aktar',
        'add_new'=>'Yeni Ekle','assign'=>'Ata','refresh'=>'Yenile',
        'approved'=>'Onaylandı','rejected'=>'Reddedildi','pending'=>'Beklemede',
        'number_additional_seats'=>'Ek Koltuk Sayısı','add_license'=>'Lisans Ekle',
        'total_licenses'=>'Toplam Lisans','license_duration'=>'Lisans Süresi',
        'not_started'=>'Başlanmadı','completed'=>'Tamamlandı',
        'total_seats'=>'Toplam Koltuk','expiry_date'=>'Son Kullanma Tarihi',
    ],
    'en' => [
        'my_profile'=>'My Profile','school_hierarchy'=>'School Hierarchy',
        'hierarchy_subtitle'=>'School → Class → Student tree view',
        'no_schools_found'=>'No associated schools found.','more'=>'more',
        'joined'=>'Joined','page'=>'Page','of'=>'of',
        'my_school'=>'My School','request_additional_seats'=>'Request Additional Seats',
        'license_expires_in'=>'License expires in','days'=>'days',
        'hello_user'=>'Hello, :name!','view_my_report'=>'View My Report','edit_profile'=>'Edit Profile',
        'welcome_user'=>'Welcome, :name','my_classes'=>'My Classes',
        'register_meta'=>'Register your school on DopiFuture digital education platform',
        'date'=>'Date','reason'=>'Reason','admin_notes'=>'Admin Notes','requested'=>'Requested',
        'cancel'=>'Cancel','close'=>'Close','submit'=>'Submit','submit_request'=>'Submit Request',
        'create'=>'Create','update'=>'Update','previous'=>'Previous','next'=>'Next',
        'no_data'=>'No data','no_records'=>'No records found.','no_results'=>'No results found.',
        'showing'=>'Showing','view_all'=>'View All','export'=>'Export',
        'add_new'=>'Add New','assign'=>'Assign','refresh'=>'Refresh',
        'approved'=>'Approved','rejected'=>'Rejected','pending'=>'Pending',
        'number_additional_seats'=>'Number of Additional Seats','add_license'=>'Add License',
        'total_licenses'=>'Total Licenses','license_duration'=>'License Duration',
        'not_started'=>'Not Started','completed'=>'Completed',
        'total_seats'=>'Total Seats','expiry_date'=>'Expiry Date',
    ],
    'mn' => [
        'my_profile'=>'Миний профайл','school_hierarchy'=>'Сургуулийн бүтэц',
        'hierarchy_subtitle'=>'Сургууль → Анги → Сурагч модны харагдац',
        'no_schools_found'=>'Холбогдсон сургууль олдсонгүй.','more'=>'нэмэлт',
        'joined'=>'Нэгдсэн','page'=>'Хуудас','of'=>'/',
        'my_school'=>'Манай сургууль','request_additional_seats'=>'Нэмэлт суудал хүсэх',
        'license_expires_in'=>'Лицензийн хугацаа дуусахад','days'=>'өдөр',
        'hello_user'=>'Сайн уу, :name!','view_my_report'=>'Тайлан харах','edit_profile'=>'Профайл засах',
        'welcome_user'=>'Тавтай морил, :name','my_classes'=>'Миний ангиуд',
        'register_meta'=>'DopiFuture дижитал боловсролын платформд сургуулиа бүртгүүлнэ үү',
        'date'=>'Огноо','reason'=>'Шалтгаан','admin_notes'=>'Админ тэмдэглэл','requested'=>'Хүсэлт',
        'cancel'=>'Цуцлах','close'=>'Хаах','submit'=>'Илгээх','submit_request'=>'Хүсэлт илгээх',
        'create'=>'Үүсгэх','update'=>'Шинэчлэх','previous'=>'Өмнөх','next'=>'Дараах',
        'no_data'=>'Мэдээлэл байхгүй','no_records'=>'Бичлэг олдсонгүй.','no_results'=>'Үр дүн олдсонгүй.',
        'showing'=>'Харагдаж буй','view_all'=>'Бүгдийг харах','export'=>'Экспорт',
        'add_new'=>'Шинэ нэмэх','assign'=>'Хуваарилах','refresh'=>'Шинэчлэх',
        'approved'=>'Зөвшөөрсөн','rejected'=>'Татгалзсан','pending'=>'Хүлээгдэж буй',
        'number_additional_seats'=>'Нэмэлт суудлын тоо','add_license'=>'Лиценз нэмэх',
        'total_licenses'=>'Нийт лиценз','license_duration'=>'Лицензийн хугацаа',
        'not_started'=>'Эхлээгүй','completed'=>'Дууссан',
        'total_seats'=>'Нийт суудал','expiry_date'=>'Дуусах огноо',
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
echo "✅ Wave 7 keys appended!\n";
