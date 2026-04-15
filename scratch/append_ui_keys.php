<?php
$extra_ui_keys = [
    'tr' => [
        'rep_homepage' => 'Ana Sayfa',
        'current_password' => 'Mevcut Şifre',
        'new_password' => 'Yeni Şifre',
        'confirm_password' => 'Şifreyi Onayla',
        'info' => 'Hesap Bilgileri',
        'save' => 'Kaydet',
        'language' => 'Dil'
    ],
    'en' => [
        'rep_homepage' => 'Home',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'info' => 'Account Info',
        'save' => 'Save',
        'language' => 'Language'
    ],
    'mn' => [
        'rep_homepage' => 'Нүүр хуудас',
        'current_password' => 'Одоогийн нууц үг',
        'new_password' => 'Шинэ нууц үг',
        'confirm_password' => 'Нууц үг баталгаажуулах',
        'info' => 'Бүртгэлийн мэдээлэл',
        'save' => 'Хадгалах',
        'language' => 'Хэл'
    ]
];

function appendArrayToFileAdmin($path, $extraData) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $extraData);
    $content = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
    file_put_contents($path, $content);
}

appendArrayToFileAdmin(dirname(__DIR__) . '/lang/tr/admin.php', $extra_ui_keys['tr']);
appendArrayToFileAdmin(dirname(__DIR__) . '/lang/en/admin.php', $extra_ui_keys['en']);
appendArrayToFileAdmin(dirname(__DIR__) . '/lang/mn/admin.php', $extra_ui_keys['mn']);

echo "Dynamic UI missing keys appended safely!";
