<?php
$base = dirname(__DIR__);
$keys = [
    'tr' => [
        'contact_hero_title'=>'Bize <span>Ulaşın</span>',
        'contact_hero_subtitle'=>'Sorularınız veya işbirliği talepleriniz için bizimle iletişime geçin.',
        'contact_faq_title'=>'Sıkça Sorulan Sorular',
        'contact_faq_subtitle'=>'DopiFuture hakkında merak edilen sorular ve yanıtları.',
        'no_applications_found'=>'Uygulama bulunamadı','no_missions_yet'=>'Henüz görev yok',
        'no_startups_yet'=>'Henüz startup yok','no_data_yet'=>'Henüz veri yok',
        'no_mw_students'=>'MW hesabı olan öğrenci bulunamadı',
        'no_ws_students'=>'WS hesabı olan öğrenci bulunamadı',
        'no_messages_yet'=>'Henüz mesaj yok','no_activity'=>'Aktivite yok',
        'my_apps'=>'Uygulamalarım','my_progress'=>'İlerlemem','my_wings'=>'Kanatlarım',
        'ai_coach_feedback'=>'AI Coach Geri Bildirimi','your_answer'=>'CEVABINIZ',
        'ai_coach_interaction_num'=>'AI Coach Etkileşim Numarası',
        'wing'=>'Kanat','badge'=>'Rozet','objective'=>'Hedef','asset'=>'Varlık',
        'project'=>'Proje','unknown'=>'Bilinmiyor','no_date'=>'Tarih yok',
        'select_at_least_one_student'=>'Lütfen en az bir öğrenci seçin.',
        'you_selected'=>'Seçtiğiniz','failed_load_user'=>'Kullanıcı verileri yüklenemedi.',
        'session'=>'Oturum',
    ],
    'en' => [
        'contact_hero_title'=>'Get in <span>Touch</span>',
        'contact_hero_subtitle'=>'Reach out to us for questions, collaboration requests, or to schedule a demo.',
        'contact_faq_title'=>'Frequently Asked Questions',
        'contact_faq_subtitle'=>'Common questions about DopiFuture and their answers.',
        'no_applications_found'=>'No applications found','no_missions_yet'=>'No missions yet',
        'no_startups_yet'=>'No startups yet','no_data_yet'=>'No data yet',
        'no_mw_students'=>'No students with MW account found',
        'no_ws_students'=>'No students with WS account found',
        'no_messages_yet'=>'No messages yet','no_activity'=>'No activity',
        'my_apps'=>'My Apps','my_progress'=>'My Progress','my_wings'=>'My Wings',
        'ai_coach_feedback'=>'AI Coach Feedback','your_answer'=>'YOUR ANSWER',
        'ai_coach_interaction_num'=>'AI Coach Interaction Number',
        'wing'=>'Wing','badge'=>'Badge','objective'=>'Objective','asset'=>'Asset',
        'project'=>'Project','unknown'=>'Unknown','no_date'=>'No Date',
        'select_at_least_one_student'=>'Please select at least one student.',
        'you_selected'=>'You selected','failed_load_user'=>'Failed to load user data.',
        'session'=>'Session',
    ],
    'mn' => [
        'contact_hero_title'=>'Бидэнтэй <span>холбогдох</span>',
        'contact_hero_subtitle'=>'Асуулт, хамтын ажиллагааны хүсэлт, эсвэл демо товлохын тулд бидэнтэй холбогдоно уу.',
        'contact_faq_title'=>'Түгээмэл асуултууд',
        'contact_faq_subtitle'=>'DopiFuture-ийн тухай түгээмэл асуулт, хариултууд.',
        'no_applications_found'=>'Програм олдсонгүй','no_missions_yet'=>'Одоогоор даалгавар байхгүй',
        'no_startups_yet'=>'Одоогоор startup байхгүй','no_data_yet'=>'Одоогоор мэдээлэл байхгүй',
        'no_mw_students'=>'MW бүртгэлтэй сурагч олдсонгүй',
        'no_ws_students'=>'WS бүртгэлтэй сурагч олдсонгүй',
        'no_messages_yet'=>'Одоогоор мессеж байхгүй','no_activity'=>'Идэвхи байхгүй',
        'my_apps'=>'Миний програмууд','my_progress'=>'Миний явц','my_wings'=>'Миний далавч',
        'ai_coach_feedback'=>'AI Coach санал хүсэлт','your_answer'=>'ТАНЫ ХАРИУЛТ',
        'ai_coach_interaction_num'=>'AI Coach харилцан үйлдлийн дугаар',
        'wing'=>'Далавч','badge'=>'Тэмдэг','objective'=>'Зорилго','asset'=>'Хөрөнгө',
        'project'=>'Төсөл','unknown'=>'Тодорхойгүй','no_date'=>'Огноо байхгүй',
        'select_at_least_one_student'=>'Дор хаяж нэг сурагч сонгоно уу.',
        'you_selected'=>'Таны сонголт','failed_load_user'=>'Хэрэглэгчийн мэдээлэл ачаалж чадсангүй.',
        'session'=>'Сесс',
    ],
];
function appendToLangFile($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}
appendToLangFile($base . '/lang/tr/portal.php', $keys['tr']);
appendToLangFile($base . '/lang/en/portal.php', $keys['en']);
appendToLangFile($base . '/lang/mn/portal.php', $keys['mn']);
echo "Done\n";
