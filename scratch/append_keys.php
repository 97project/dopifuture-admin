<?php
$admin_keys = [
    'tr' => [
        'profile' => 'Profil',
        'logout' => 'Çıkış',
        'login' => 'Giriş',
        'new_school' => 'Kayıt',
        'date' => 'Tarih'
    ],
    'en' => [
        'profile' => 'Profile',
        'logout' => 'Logout',
        'login' => 'Login',
        'new_school' => 'Register',
        'date' => 'Date'
    ],
    'mn' => [
        'profile' => 'Профайл',
        'logout' => 'Гарах',
        'login' => 'Нэвтрэх',
        'new_school' => 'Бүртгүүлэх',
        'date' => 'Огноо'
    ]
];

$portal_keys = [
    'tr' => [
        'solutions_title' => 'Çözümler',
        'contact_title' => 'İletişim'
    ],
    'en' => [
        'solutions_title' => 'Solutions',
        'contact_title' => 'Contact'
    ],
    'mn' => [
        'solutions_title' => 'Шийдлүүд',
        'contact_title' => 'Холбоо барих'
    ]
];

function appendAdmin($path, $extraData) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $extraData);
    $content = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
    file_put_contents($path, $content);
}

appendAdmin(dirname(__DIR__) . '/lang/tr/admin.php', $admin_keys['tr']);
appendAdmin(dirname(__DIR__) . '/lang/en/admin.php', $admin_keys['en']);
appendAdmin(dirname(__DIR__) . '/lang/mn/admin.php', $admin_keys['mn']);

appendAdmin(dirname(__DIR__) . '/lang/tr/portal.php', $portal_keys['tr']);
appendAdmin(dirname(__DIR__) . '/lang/en/portal.php', $portal_keys['en']);
appendAdmin(dirname(__DIR__) . '/lang/mn/portal.php', $portal_keys['mn']);

echo "Done.";
