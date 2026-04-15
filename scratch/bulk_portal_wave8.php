<?php
/**
 * WAVE 8 — ABSOLUTE FINAL: empty states, JS dialogs, remaining ternaries
 */
$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

$replacements = [
    // ─── Empty States ───
    '>No applications found<'           => '>{{ __(\'portal.no_applications_found\') }}<',
    '>No missions yet<'                 => '>{{ __(\'portal.no_missions_yet\') }}<',
    '>No startups yet<'                 => '>{{ __(\'portal.no_startups_yet\') }}<',
    '>No data yet<'                     => '>{{ __(\'portal.no_data_yet\') }}<',
    '>No students with MW account found<' => '>{{ __(\'portal.no_mw_students\') }}<',
    '>No students with WS account found<' => '>{{ __(\'portal.no_ws_students\') }}<',
    '>No messages yet<'                 => '>{{ __(\'portal.no_messages_yet\') }}<',
    '>No activity<'                     => '>{{ __(\'portal.no_activity\') }}<',

    // ─── Section/Card Titles ───
    '>My Apps<'                         => '>{{ __(\'portal.my_apps\') }}<',
    '>My Progress<'                     => '>{{ __(\'portal.my_progress\') }}<',
    '>My Wings<'                        => '>{{ __(\'portal.my_wings\') }}<',
    '>AI Coach Feedback<'               => '>{{ __(\'portal.ai_coach_feedback\') }}<',
    '>YOUR ANSWER<'                     => '>{{ __(\'portal.your_answer\') }}<',
    '>AI Coach Interaction Number<'     => '>{{ __(\'portal.ai_coach_interaction_num\') }}<',
    '>In Progress<'                     => '>{{ __(\'portal.in_progress\') }}<',

    // ─── Ternary: type labels ───
    "? 'Wing'"                          => "? __('portal.wing')",
    ": 'Badge'"                         => ": __('portal.badge')",
    "? 'Badge'"                         => "? __('portal.badge')",
    "? 'Scenario'"                      => "? __('portal.scenario')",
    "? 'Objective'"                     => "? __('portal.objective')",
    "? 'Asset'"                         => "? __('portal.asset')",
    "? 'Project'"                       => "? __('portal.project')",
    "? 'Session'"                       => "? __('portal.session')",
    "? 'Unknown'"                       => "? __('portal.unknown')",
    ": 'Unknown'"                       => ": __('portal.unknown')",
    "? 'No Date'"                       => "? __('portal.no_date')",

    // ─── JS Dialogs ───
    "alert('Please select at least one student.')" => "alert('{{ __(\"portal.select_at_least_one_student\") }}')",
    "confirm('Please select at least one student.')" => "confirm('{{ __(\"portal.select_at_least_one_student\") }}')",
    "textContent = 'You selected '"     => "textContent = '{{ __(\"portal.you_selected\") }} '",
    "textContent = 'Failed to load user data.'" => "textContent = '{{ __(\"portal.failed_load_user\") }}'",
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
echo "\n═══ Wave 8: Modified $fileCount files ═══\n";

$keys = [
    'tr' => [
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
    ],
    'en' => [
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
    ],
    'mn' => [
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
echo "✅ Wave 8 keys appended!\n";
