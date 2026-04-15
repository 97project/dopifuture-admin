<?php
$keys = [
    'tr' => [
        'login_title' => 'Giriş',
        'login_meta' => 'DopiFuture portal girişi',
        'login_subtitle' => 'DopiFuture hesabınızla giriş yapın.',
        'email_placeholder' => 'E-posta adresiniz',
        'password_placeholder' => 'Şifreniz',
        'remember_me' => 'Beni hatırla',
        'sign_in' => 'Giriş Yap',
        'want_to_register' => 'Okulunuzu kaydetmek ister misiniz?',
        'admin_login' => 'Admin Girişi →',
        'register_subtitle' => 'Okulunuzu DopiFuture platformuna kaydedin. En kısa sürede sizinle iletişime geçeceğiz.',
        'enter_school_name' => 'Okul adını girin',
        'eg_500' => 'örn. 500',
        'additional_notes_placeholder' => 'Ek bilgi veya talepler...',
        'submit_registration' => 'Kayıt Talebini Gönder',
        'secure_connection' => 'Güvenli Bağlantı',
        'gdpr_compliant' => 'GDPR Uyumlu',
    ],
    'en' => [
        'login_title' => 'Login',
        'login_meta' => 'DopiFuture portal login',
        'login_subtitle' => 'Sign in with your DopiFuture account.',
        'email_placeholder' => 'Your email address',
        'password_placeholder' => 'Your password',
        'remember_me' => 'Remember me',
        'sign_in' => 'Sign In',
        'want_to_register' => 'Want to register your school?',
        'admin_login' => 'Admin Login →',
        'register_subtitle' => 'Register your school on the DopiFuture platform. We will contact you as soon as possible.',
        'enter_school_name' => 'Enter school name',
        'eg_500' => 'e.g. 500',
        'additional_notes_placeholder' => 'Additional information or requests...',
        'submit_registration' => 'Submit Registration Request',
        'secure_connection' => 'Secure Connection',
        'gdpr_compliant' => 'GDPR Compliant',
    ],
    'mn' => [
        'login_title' => 'Нэвтрэх',
        'login_meta' => 'DopiFuture порталд нэвтрэх',
        'login_subtitle' => 'DopiFuture бүртгэлээрээ нэвтрэнэ үү.',
        'email_placeholder' => 'Таны и-мэйл хаяг',
        'password_placeholder' => 'Таны нууц үг',
        'remember_me' => 'Намайг санаарай',
        'sign_in' => 'Нэвтрэх',
        'want_to_register' => 'Сургуулиа бүртгүүлэх үү?',
        'admin_login' => 'Админ нэвтрэх →',
        'register_subtitle' => 'Сургуулиа DopiFuture платформд бүртгэнэ үү. Бид тантай аль болох хурдан холбоо барина.',
        'enter_school_name' => 'Сургуулийн нэрийг оруулна уу',
        'eg_500' => 'жнь. 500',
        'additional_notes_placeholder' => 'Нэмэлт мэдээлэл эсвэл хүсэлтүүд...',
        'submit_registration' => 'Бүртгэлийн хүсэлт илгээх',
        'secure_connection' => 'Аюулгүй холболт',
        'gdpr_compliant' => 'GDPR нийцтэй',
    ],
];

function appendToPortal($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}

appendToPortal(dirname(__DIR__) . '/lang/tr/portal.php', $keys['tr']);
appendToPortal(dirname(__DIR__) . '/lang/en/portal.php', $keys['en']);
appendToPortal(dirname(__DIR__) . '/lang/mn/portal.php', $keys['mn']);

echo "Login/Register portal keys appended to all 3 languages!";
