<?php
/**
 * WAVE 4 — FINAL: Every last hardcoded string
 */
$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

$replacements = [
    // ─── Common ───
    '>Total<'                       => '>{{ __(\'portal.total\') }}<',
    '>Synced<'                      => '>{{ __(\'portal.synced\') }}<',
    '>Failed<'                      => '>{{ __(\'portal.failed\') }}<',
    '>Select<'                      => '>{{ __(\'portal.select\') }}<',
    '>Inactive<'                    => '>{{ __(\'portal.inactive\') }}<',
    '>Delete This Class<'           => '>{{ __(\'portal.delete_this_class\') }}<',
    '>Delete This User<'            => '>{{ __(\'portal.delete_this_user\') }}<',
    '>Delete User<'                 => '>{{ __(\'portal.delete_user\') }}<',
    '>Select (optional)<'           => '>{{ __(\'portal.select_optional\') }}<',
    '>Recent<'                      => '>{{ __(\'portal.recent\') }}<',
    '>Recent Students<'             => '>{{ __(\'portal.recently_added_students\') }}<',
    '>Details<'                     => '>{{ __(\'portal.view_details\') }}<',
    '>Detail<'                      => '>{{ __(\'portal.detail\') }}<',
    '>All<'                         => '>{{ __(\'portal.all\') }}<',
    '>Done<'                        => '>{{ __(\'portal.done\') }}<',
    '>Student<'                     => '>{{ __(\'portal.student\') }}<',

    // ─── License ───
    '>Utilization Rate<'            => '>{{ __(\'portal.utilization_rate\') }}<',
    '>Amount<'                      => '>{{ __(\'portal.amount\') }}<',
    '>Notes<'                       => '>{{ __(\'portal.notes\') }}<',
    '>Number<'                      => '>{{ __(\'portal.number\') }}<',
    '>Total Licence<'               => '>{{ __(\'portal.total_licenses\') }}<',
    '>Used Licence<'                => '>{{ __(\'portal.used_seats\') }}<',
    '>Licence Duration<'            => '>{{ __(\'portal.license_duration\') }}<',

    // ─── Reports Common ───
    '>Deadline<'                    => '>{{ __(\'portal.deadline\') }}<',
    '>Action<'                      => '>{{ __(\'portal.action\') }}<',
    '>Step<'                        => '>{{ __(\'portal.step\') }}<',
    '>Total Progress<'              => '>{{ __(\'portal.total_progress\') }}<',
    '>Total Discussion<'            => '>{{ __(\'portal.total_discussion\') }}<',
    '>Avg Discussion Time<'         => '>{{ __(\'portal.avg_discussion_time\') }}<',
    '>Empathy Score<'               => '>{{ __(\'portal.empathy_score\') }}<',
    '>Interaction Count<'           => '>{{ __(\'portal.interaction_count\') }}<',
    '>Galaxy Join<'                 => '>{{ __(\'portal.galaxy_join\') }}<',
    '>Roles Completed<'             => '>{{ __(\'portal.roles_completed\') }}<',
    '>Last Interaction<'            => '>{{ __(\'portal.last_interaction\') }}<',
    '>Completion<'                  => '>{{ __(\'portal.completion\') }}<',
    '>Email<'                       => '>{{ __(\'admin.email\') }}<',
    '>Modules<'                     => '>{{ __(\'portal.modules\') }}<',
    '>Startup Name<'                => '>{{ __(\'portal.startup_name\') }}<',
    '>Startup Type<'                => '>{{ __(\'portal.startup_type\') }}<',
    '>Mission<'                     => '>{{ __(\'portal.mission\') }}<',
    '>Business<'                    => '>{{ __(\'portal.business\') }}<',
    '>Title<'                       => '>{{ __(\'portal.title\') }}<',
    '>Category<'                    => '>{{ __(\'portal.category\') }}<',
    '>Difficulty<'                  => '>{{ __(\'portal.difficulty\') }}<',
    '>Key<'                         => '>{{ __(\'portal.key\') }}<',
    '>Description<'                 => '>{{ __(\'portal.description\') }}<',

    // ─── Reports Index ───
    '>Platform At A Glance<'        => '>{{ __(\'portal.platform_at_glance\') }}<',
    '>Total Plays<'                 => '>{{ __(\'portal.total_plays\') }}<',
    '>Win Rate<'                    => '>{{ __(\'portal.win_rate\') }}<',
    '>Max Score<'                   => '>{{ __(\'portal.max_score\') }}<',
    '>Platforms Overview<'          => '>{{ __(\'portal.platforms_overview\') }}<',
    '>Score Distribution<'          => '>{{ __(\'portal.score_distribution\') }}<',
    '>Most Popular Modules<'        => '>{{ __(\'portal.most_popular_modules\') }}<',
    '>Completion Status<'           => '>{{ __(\'portal.completion_status\') }}<',
    '>Daily Volatility<'            => '>{{ __(\'portal.daily_volatility\') }}<',
    '>Insufficient Data<'           => '>{{ __(\'portal.insufficient_data\') }}<',
    'Advanced cross-platform intelligence and gamified reporting' => '{{ __(\'portal.reports_index_subtitle\') }}',

    // ─── Mission Detail ───
    '>Result<'                      => '>{{ __(\'portal.result\') }}<',
    '>Overall Score<'               => '>{{ __(\'portal.overall_score\') }}<',
    '>Group Flow<'                  => '>{{ __(\'portal.group_flow\') }}<',
    '>Health<'                      => '>{{ __(\'portal.health\') }}<',
    '>Resource<'                    => '>{{ __(\'portal.resource\') }}<',
    '>Ethics<'                      => '>{{ __(\'portal.ethics\') }}<',
    '>Adaptation<'                  => '>{{ __(\'portal.adaptation\') }}<',

    // ─── Role Galaxy Detail ───
    '>Not yet explored<'            => '>{{ __(\'portal.not_yet_explored\') }}<',

    // ─── Session Detail ───
    '>Scenario<'                    => '>{{ __(\'portal.scenario\') }}<',
    '>Sub Topic<'                   => '>{{ __(\'portal.sub_topic\') }}<',
    '>Theme<'                       => '>{{ __(\'portal.theme\') }}<',
    '>Language<'                    => '>{{ __(\'portal.language\') }}<',
    '>Thread<'                      => '>{{ __(\'portal.thread\') }}<',
    '>Started<'                     => '>{{ __(\'portal.started\') }}<',
    '>Token Usage<'                 => '>{{ __(\'portal.token_usage\') }}<',
    '>Estimated Total<'             => '>{{ __(\'portal.estimated_total\') }}<',
    '>Real-Time WebSocket Chat<'    => '>{{ __(\'portal.realtime_ws_chat\') }}<',

    // ─── Startup Detail ───
    '>Project Detail<'              => '>{{ __(\'portal.project_detail\') }}<',
    '>Team Summary<'                => '>{{ __(\'portal.team_summary\') }}<',
    '>Member<'                      => '>{{ __(\'portal.member\') }}<',
    '>Responsible<'                 => '>{{ __(\'portal.responsible\') }}<',
    '>Total Product Score<'         => '>{{ __(\'portal.total_product_score\') }}<',
    '>Submitted Files<'             => '>{{ __(\'portal.submitted_files\') }}<',
    '>Submitted Links<'             => '>{{ __(\'portal.submitted_links\') }}<',

    // ─── Student Report ───
    '>Module Progress<'             => '>{{ __(\'portal.module_progress\') }}<',
    '>Session History<'             => '>{{ __(\'portal.session_history\') }}<',
    '>Available Wing Badges<'       => '>{{ __(\'portal.available_wing_badges\') }}<',
    'All collectible badges from the platform' => '{{ __(\'portal.all_collectible_badges\') }}',
    'Achievement points across all apps' => '{{ __(\'portal.achievement_points_all_apps\') }}',
    '>Score Trend (Recent Simulations)<' => '>{{ __(\'portal.score_trend_simulations\') }}<',

    // ─── Way AI Coach Detail ───
    '>Total Interaction<'           => '>{{ __(\'portal.total_interaction\') }}<',
    '>Lecturer Sessions<'           => '>{{ __(\'portal.lecturer_sessions\') }}<',
    '>Chatbot Sessions<'            => '>{{ __(\'portal.chatbot_sessions\') }}<',

    // ─── Reports Main ───
    '>Reports<'                     => '>{{ __(\'admin.reports\') }}<',
    '>Capacity<'                    => '>{{ __(\'portal.capacity\') }}<',
    '>Rate<'                        => '>{{ __(\'portal.rate\') }}<',

    // ─── Schools ───
    '>Edit School<'                 => '>{{ __(\'portal.edit_school\') }}<',
    '>School Detail<'               => '>{{ __(\'portal.school_detail\') }}<',

    // ─── User Show ───
    '>First Name<'                  => '>{{ __(\'admin.name\') }}<',
    '>Registration Date<'           => '>{{ __(\'portal.registration_date\') }}<',
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
echo "\n═══ Wave 4 FINAL: Modified $fileCount files ═══\n";

// Translation keys
$keys = [
    'tr' => [
        'total'=>'Toplam','synced'=>'Senkronize','failed'=>'Başarısız','select'=>'Seç','inactive'=>'Pasif',
        'delete_this_class'=>'Bu Sınıfı Sil','delete_this_user'=>'Bu Kullanıcıyı Sil','delete_user'=>'Kullanıcı Sil',
        'select_optional'=>'Seç (isteğe bağlı)','recent'=>'Son','detail'=>'Detay','all'=>'Tümü','done'=>'Tamamlandı',
        'student'=>'Öğrenci','utilization_rate'=>'Kullanım Oranı','amount'=>'Miktar','notes'=>'Notlar','number'=>'Numara',
        'deadline'=>'Son Tarih','action'=>'İşlem','step'=>'Adım','total_progress'=>'Toplam İlerleme',
        'total_discussion'=>'Toplam Tartışma','avg_discussion_time'=>'Ort. Tartışma Süresi',
        'empathy_score'=>'Empati Puanı','interaction_count'=>'Etkileşim Sayısı',
        'galaxy_join'=>'Galaxy Katılım','roles_completed'=>'Tamamlanan Roller',
        'last_interaction'=>'Son Etkileşim','completion'=>'Tamamlanma','modules'=>'Modüller',
        'startup_name'=>'Startup Adı','startup_type'=>'Startup Türü','mission'=>'Görev',
        'business'=>'İş','title'=>'Başlık','category'=>'Kategori','difficulty'=>'Zorluk',
        'key'=>'Anahtar','description'=>'Açıklama',
        'platform_at_glance'=>'Platform Genel Bakış','total_plays'=>'Toplam Oynama',
        'win_rate'=>'Kazanma Oranı','max_score'=>'Maks. Puan','platforms_overview'=>'Platform Özeti',
        'score_distribution'=>'Puan Dağılımı','most_popular_modules'=>'En Popüler Modüller',
        'completion_status'=>'Tamamlanma Durumu','daily_volatility'=>'Günlük Değişkenlik',
        'insufficient_data'=>'Yetersiz Veri',
        'reports_index_subtitle'=>'Gelişmiş çapraz platform zekası ve oyunlaştırılmış raporlama.',
        'result'=>'Sonuç','overall_score'=>'Genel Puan','group_flow'=>'Grup Akışı',
        'health'=>'Sağlık','resource'=>'Kaynak','ethics'=>'Etik','adaptation'=>'Uyum',
        'not_yet_explored'=>'Henüz keşfedilmedi',
        'scenario'=>'Senaryo','sub_topic'=>'Alt Konu','theme'=>'Tema','language'=>'Dil',
        'thread'=>'İleti Dizisi','started'=>'Başladı','token_usage'=>'Token Kullanımı',
        'estimated_total'=>'Tahmini Toplam','realtime_ws_chat'=>'Gerçek Zamanlı WebSocket Sohbet',
        'project_detail'=>'Proje Detayı','team_summary'=>'Takım Özeti','member'=>'Üye',
        'responsible'=>'Sorumlu','total_product_score'=>'Toplam Ürün Puanı',
        'submitted_files'=>'Gönderilen Dosyalar','submitted_links'=>'Gönderilen Bağlantılar',
        'module_progress'=>'Modül İlerlemesi','session_history'=>'Oturum Geçmişi',
        'available_wing_badges'=>'Mevcut Kanat Rozetleri',
        'all_collectible_badges'=>'Platformdaki tüm koleksiyon rozetleri',
        'achievement_points_all_apps'=>'Tüm uygulamalardaki başarı puanları',
        'score_trend_simulations'=>'Puan Trendi (Son Simülasyonlar)',
        'total_interaction'=>'Toplam Etkileşim','lecturer_sessions'=>'Ders Oturumları',
        'chatbot_sessions'=>'Sohbet Botu Oturumları',
        'capacity'=>'Kapasite','rate'=>'Oran',
        'edit_school'=>'Okul Düzenle','school_detail'=>'Okul Detayı','registration_date'=>'Kayıt Tarihi',
    ],
    'en' => [
        'total'=>'Total','synced'=>'Synced','failed'=>'Failed','select'=>'Select','inactive'=>'Inactive',
        'delete_this_class'=>'Delete This Class','delete_this_user'=>'Delete This User','delete_user'=>'Delete User',
        'select_optional'=>'Select (optional)','recent'=>'Recent','detail'=>'Detail','all'=>'All','done'=>'Done',
        'student'=>'Student','utilization_rate'=>'Utilization Rate','amount'=>'Amount','notes'=>'Notes','number'=>'Number',
        'deadline'=>'Deadline','action'=>'Action','step'=>'Step','total_progress'=>'Total Progress',
        'total_discussion'=>'Total Discussion','avg_discussion_time'=>'Avg Discussion Time',
        'empathy_score'=>'Empathy Score','interaction_count'=>'Interaction Count',
        'galaxy_join'=>'Galaxy Join','roles_completed'=>'Roles Completed',
        'last_interaction'=>'Last Interaction','completion'=>'Completion','modules'=>'Modules',
        'startup_name'=>'Startup Name','startup_type'=>'Startup Type','mission'=>'Mission',
        'business'=>'Business','title'=>'Title','category'=>'Category','difficulty'=>'Difficulty',
        'key'=>'Key','description'=>'Description',
        'platform_at_glance'=>'Platform At A Glance','total_plays'=>'Total Plays',
        'win_rate'=>'Win Rate','max_score'=>'Max Score','platforms_overview'=>'Platforms Overview',
        'score_distribution'=>'Score Distribution','most_popular_modules'=>'Most Popular Modules',
        'completion_status'=>'Completion Status','daily_volatility'=>'Daily Volatility',
        'insufficient_data'=>'Insufficient Data',
        'reports_index_subtitle'=>'Advanced cross-platform intelligence and gamified reporting.',
        'result'=>'Result','overall_score'=>'Overall Score','group_flow'=>'Group Flow',
        'health'=>'Health','resource'=>'Resource','ethics'=>'Ethics','adaptation'=>'Adaptation',
        'not_yet_explored'=>'Not yet explored',
        'scenario'=>'Scenario','sub_topic'=>'Sub Topic','theme'=>'Theme','language'=>'Language',
        'thread'=>'Thread','started'=>'Started','token_usage'=>'Token Usage',
        'estimated_total'=>'Estimated Total','realtime_ws_chat'=>'Real-Time WebSocket Chat',
        'project_detail'=>'Project Detail','team_summary'=>'Team Summary','member'=>'Member',
        'responsible'=>'Responsible','total_product_score'=>'Total Product Score',
        'submitted_files'=>'Submitted Files','submitted_links'=>'Submitted Links',
        'module_progress'=>'Module Progress','session_history'=>'Session History',
        'available_wing_badges'=>'Available Wing Badges',
        'all_collectible_badges'=>'All collectible badges from the platform',
        'achievement_points_all_apps'=>'Achievement points across all apps',
        'score_trend_simulations'=>'Score Trend (Recent Simulations)',
        'total_interaction'=>'Total Interaction','lecturer_sessions'=>'Lecturer Sessions',
        'chatbot_sessions'=>'Chatbot Sessions',
        'capacity'=>'Capacity','rate'=>'Rate',
        'edit_school'=>'Edit School','school_detail'=>'School Detail','registration_date'=>'Registration Date',
    ],
    'mn' => [
        'total'=>'Нийт','synced'=>'Синхронлогдсон','failed'=>'Амжилтгүй','select'=>'Сонгох','inactive'=>'Идэвхгүй',
        'delete_this_class'=>'Энэ ангийг устгах','delete_this_user'=>'Энэ хэрэглэгчийг устгах','delete_user'=>'Хэрэглэгч устгах',
        'select_optional'=>'Сонгох (заавал биш)','recent'=>'Сүүлийн','detail'=>'Дэлгэрэнгүй','all'=>'Бүгд','done'=>'Дууссан',
        'student'=>'Сурагч','utilization_rate'=>'Ашиглалтын хувь','amount'=>'Дүн','notes'=>'Тэмдэглэл','number'=>'Дугаар',
        'deadline'=>'Хугацаа','action'=>'Үйлдэл','step'=>'Алхам','total_progress'=>'Нийт явц',
        'total_discussion'=>'Нийт хэлэлцүүлэг','avg_discussion_time'=>'Дундаж хэлэлцүүлэг хугацаа',
        'empathy_score'=>'Эмпатийн оноо','interaction_count'=>'Харилцан үйлдлийн тоо',
        'galaxy_join'=>'Galaxy нэгдэлт','roles_completed'=>'Гүйцэтгэсэн дүрүүд',
        'last_interaction'=>'Сүүлийн харилцан үйлдэл','completion'=>'Гүйцэтгэл','modules'=>'Модулиуд',
        'startup_name'=>'Startup нэр','startup_type'=>'Startup төрөл','mission'=>'Даалгавар',
        'business'=>'Бизнес','title'=>'Гарчиг','category'=>'Ангилал','difficulty'=>'Хүндрэл',
        'key'=>'Түлхүүр','description'=>'Тайлбар',
        'platform_at_glance'=>'Платформын ерөнхий байдал','total_plays'=>'Нийт тоглолт',
        'win_rate'=>'Ялалтын хувь','max_score'=>'Хамгийн өндөр оноо','platforms_overview'=>'Платформын тойм',
        'score_distribution'=>'Оноо хуваарилалт','most_popular_modules'=>'Хамгийн алдартай модуль',
        'completion_status'=>'Гүйцэтгэлийн төлөв','daily_volatility'=>'Өдрийн хэлбэлзэл',
        'insufficient_data'=>'Хангалтгүй мэдээлэл',
        'reports_index_subtitle'=>'Дэвшилтэт платформ хоорондын тагнуул ба тоглоом суурилсан тайлан.',
        'result'=>'Үр дүн','overall_score'=>'Ерөнхий оноо','group_flow'=>'Бүлгийн урсгал',
        'health'=>'Эрүүл мэнд','resource'=>'Нөөц','ethics'=>'Ёс зүй','adaptation'=>'Дасан зохицол',
        'not_yet_explored'=>'Одоогоор судлаагүй',
        'scenario'=>'Сценари','sub_topic'=>'Дэд сэдэв','theme'=>'Сэдэв','language'=>'Хэл',
        'thread'=>'Мессежийн урсгал','started'=>'Эхэлсэн','token_usage'=>'Токен ашиглалт',
        'estimated_total'=>'Тооцоолсон нийт','realtime_ws_chat'=>'Шуурхай WebSocket чат',
        'project_detail'=>'Төслийн дэлгэрэнгүй','team_summary'=>'Багийн тойм','member'=>'Гишүүн',
        'responsible'=>'Хариуцагч','total_product_score'=>'Нийт бүтээгдэхүүний оноо',
        'submitted_files'=>'Илгээсэн файлууд','submitted_links'=>'Илгээсэн холбоосууд',
        'module_progress'=>'Модулийн явц','session_history'=>'Сессийн түүх',
        'available_wing_badges'=>'Авах боломжтой далавчийн тэмдэг',
        'all_collectible_badges'=>'Платформоос цуглуулж болох бүх тэмдэгүүд',
        'achievement_points_all_apps'=>'Бүх аппын амжилтын оноо',
        'score_trend_simulations'=>'Оноо чиг хандлага (Сүүлийн симуляци)',
        'total_interaction'=>'Нийт харилцан үйлдэл','lecturer_sessions'=>'Лекцийн сессүүд',
        'chatbot_sessions'=>'Чатбот сессүүд',
        'capacity'=>'Хүчин чадал','rate'=>'Хувь',
        'edit_school'=>'Сургууль засах','school_detail'=>'Сургуулийн дэлгэрэнгүй','registration_date'=>'Бүртгэлийн огноо',
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

echo "✅ Wave 4 FINAL keys appended!\n";
