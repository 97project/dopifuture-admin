<?php
/**
 * WAVE 3 — Remaining UI labels in forms, tables, modals, detail pages
 */
$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

$replacements = [
    // ─── School Form Labels ───
    '>Country<'                     => '>{{ __(\'portal.country\') }}<',
    '>State<'                       => '>{{ __(\'portal.state\') }}<',
    '>City<'                        => '>{{ __(\'portal.city\') }}<',
    '>Phone<'                       => '>{{ __(\'portal.phone\') }}<',
    '>Address<'                     => '>{{ __(\'portal.address\') }}<',
    '>Website<'                     => '>{{ __(\'portal.website\') }}<',
    '>Search<'                      => '>{{ __(\'portal.search\') }}<',

    // ─── School Index Table ───
    '>Users<'                       => '>{{ __(\'portal.total_users\') }}<',
    '>Licenses<'                    => '>{{ __(\'portal.license_management\') }}<',

    // ─── School Show ───
    '>General Information<'         => '>{{ __(\'portal.general_info\') }}<',
    '>Class<'                       => '>{{ __(\'portal.class\') }}<',
    '>Grade<'                       => '>{{ __(\'portal.grade\') }}<',
    '>Year<'                        => '>{{ __(\'portal.year\') }}<',
    '>Name<'                        => '>{{ __(\'admin.name\') }}<',
    '>Role<'                        => '>{{ __(\'admin.role\') }}<',
    '>School<'                      => '>{{ __(\'admin.school_name\') }}<',
    '>Seats<'                       => '>{{ __(\'portal.total_seats\') }}<',
    '>Used<'                        => '>{{ __(\'portal.used_seats\') }}<',
    '>Remaining<'                   => '>{{ __(\'portal.remaining_seats\') }}<',
    '>Expiry<'                      => '>{{ __(\'portal.expiry_date\') }}<',

    // ─── User Show ───
    '>User Information<'            => '>{{ __(\'portal.user_info\') }}<',
    '>Schools<'                     => '>{{ __(\'admin.schools\') }}<',
    '>Applications<'                => '>{{ __(\'portal.applications\') }}<',

    // ─── User Form ───
    '>Password<'                    => '>{{ __(\'admin.password\') }}<',
    '>Status *<'                    => '>{{ __(\'admin.status\') }} *<',

    // ─── User Index Modal ───
    '>This action cannot be undone. The user will be permanently removed.<' => '>{{ __(\'portal.confirm_delete_user_msg\') }}<',

    // ─── Report Detail Labels ───
    '>Module<'                      => '>{{ __(\'portal.module\') }}<',
    '>Type<'                        => '>{{ __(\'portal.type\') }}<',
    '>Session<'                     => '>{{ __(\'portal.session\') }}<',
    '>Lecturer<'                    => '>{{ __(\'portal.lecturer\') }}<',
    '>Chatbot<'                     => '>{{ __(\'portal.chatbot\') }}<',
    '>Attempts<'                    => '>{{ __(\'portal.attempts\') }}<',
    '>Duration<'                    => '>{{ __(\'portal.duration\') }}<',

    // ─── Contact Page ───
    '>Contact Us<'                  => '>{{ __(\'portal.contact_us\') }}<',
    '>Send Message<'                => '>{{ __(\'portal.send_message\') }}<',
    '>Your Name<'                   => '>{{ __(\'portal.your_name\') }}<',
    '>Your Email<'                  => '>{{ __(\'portal.your_email\') }}<',
    '>Subject<'                     => '>{{ __(\'portal.subject\') }}<',
    '>Message<'                     => '>{{ __(\'portal.message\') }}<',

    // ─── Solutions Page ───
    '>Our Solutions<'               => '>{{ __(\'portal.our_solutions\') }}<',

    // ─── Home features section ───
    '>Why DopiFuture?<'             => '>{{ __(\'portal.why_dopifuture\') }}<',
];

// Apply
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
echo "\n═══ Wave 3: Modified $fileCount files ═══\n";

// Add keys
$keys = [
    'tr' => [
        'country' => 'Ülke', 'state' => 'Eyalet/İl', 'city' => 'Şehir',
        'phone' => 'Telefon', 'address' => 'Adres', 'website' => 'Web Sitesi',
        'search' => 'Ara', 'general_info' => 'Genel Bilgiler',
        'class' => 'Sınıf', 'grade' => 'Seviye', 'year' => 'Yıl',
        'user_info' => 'Kullanıcı Bilgileri', 'applications' => 'Uygulamalar',
        'confirm_delete_user_msg' => 'Bu işlem geri alınamaz. Kullanıcı kalıcı olarak silinecektir.',
        'module' => 'Modül', 'type' => 'Tür', 'session' => 'Oturum',
        'lecturer' => 'Ders Anlatıcı', 'chatbot' => 'Sohbet Botu',
        'attempts' => 'Deneme', 'duration' => 'Süre',
        'contact_us' => 'Bize Ulaşın', 'send_message' => 'Mesaj Gönder',
        'your_name' => 'Adınız', 'your_email' => 'E-postanız',
        'subject' => 'Konu', 'message' => 'Mesaj',
        'our_solutions' => 'Çözümlerimiz', 'why_dopifuture' => 'Neden DopiFuture?',
    ],
    'en' => [
        'country' => 'Country', 'state' => 'State', 'city' => 'City',
        'phone' => 'Phone', 'address' => 'Address', 'website' => 'Website',
        'search' => 'Search', 'general_info' => 'General Information',
        'class' => 'Class', 'grade' => 'Grade', 'year' => 'Year',
        'user_info' => 'User Information', 'applications' => 'Applications',
        'confirm_delete_user_msg' => 'This action cannot be undone. The user will be permanently removed.',
        'module' => 'Module', 'type' => 'Type', 'session' => 'Session',
        'lecturer' => 'Lecturer', 'chatbot' => 'Chatbot',
        'attempts' => 'Attempts', 'duration' => 'Duration',
        'contact_us' => 'Contact Us', 'send_message' => 'Send Message',
        'your_name' => 'Your Name', 'your_email' => 'Your Email',
        'subject' => 'Subject', 'message' => 'Message',
        'our_solutions' => 'Our Solutions', 'why_dopifuture' => 'Why DopiFuture?',
    ],
    'mn' => [
        'country' => 'Улс', 'state' => 'Муж/Аймаг', 'city' => 'Хот',
        'phone' => 'Утас', 'address' => 'Хаяг', 'website' => 'Вэб сайт',
        'search' => 'Хайх', 'general_info' => 'Ерөнхий мэдээлэл',
        'class' => 'Анги', 'grade' => 'Түвшин', 'year' => 'Жил',
        'user_info' => 'Хэрэглэгчийн мэдээлэл', 'applications' => 'Програмууд',
        'confirm_delete_user_msg' => 'Энэ үйлдлийг буцааж болохгүй. Хэрэглэгч бүрмөсөн устгагдана.',
        'module' => 'Модуль', 'type' => 'Төрөл', 'session' => 'Сесс',
        'lecturer' => 'Лектор', 'chatbot' => 'Чатбот',
        'attempts' => 'Оролдлого', 'duration' => 'Хугацаа',
        'contact_us' => 'Бидэнтэй холбогдох', 'send_message' => 'Мессеж илгээх',
        'your_name' => 'Таны нэр', 'your_email' => 'Таны и-мэйл',
        'subject' => 'Сэдэв', 'message' => 'Мессеж',
        'our_solutions' => 'Манай шийдлүүд', 'why_dopifuture' => 'Яагаад DopiFuture?',
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

echo "✅ Wave 3 keys appended!\n";
