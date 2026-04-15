<?php
$hero_keys = [
    'tr' => [
        'hero_title'     => '<span class="gradient-text">DopiFuture</span> ile<br>Eğitimi Dönüştürün',
        'hero_tagline'   => 'Beş güçlü uygulama ile öğrencilerinizin potansiyelini keşfedin. Oyunlaştırılmış öğrenme, YZ koçluk ve gerçek zamanlı analitik.',
        'hero_btn_start' => 'Hemen Başlayın',
        'hero_btn_explore'=> 'Çözümleri Keşfet',
    ],
    'en' => [
        'hero_title'     => 'Transform Education<br>with <span class="gradient-text">DopiFuture</span>',
        'hero_tagline'   => 'Unlock your students\' potential with five powerful apps. Gamified learning, AI coaching, and real-time analytics.',
        'hero_btn_start' => 'Get Started',
        'hero_btn_explore'=> 'Explore Solutions',
    ],
    'mn' => [
        'hero_title'     => '<span class="gradient-text">DopiFuture</span>-ээр<br>Боловсролыг Өөрчилье',
        'hero_tagline'   => 'Таван хүчирхэг апп-аар сурагчдынхаа чадавхийг нээ. Тоглоом суурилсан сурах, ХИ коуч болон цаг алдалгүй аналитик.',
        'hero_btn_start' => 'Эхлэх',
        'hero_btn_explore'=> 'Шийдлийг Судлах',
    ],
];

function appendToPortal($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}

appendToPortal(dirname(__DIR__) . '/lang/tr/portal.php', $hero_keys['tr']);
appendToPortal(dirname(__DIR__) . '/lang/en/portal.php', $hero_keys['en']);
appendToPortal(dirname(__DIR__) . '/lang/mn/portal.php', $hero_keys['mn']);

echo "Hero keys added to tr, en, mn!";
