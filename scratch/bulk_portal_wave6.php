<?php
/**
 * WAVE 6 — Placeholders, titles, ternaries, card-titles, subtitles
 */
$basePath = dirname(__DIR__);
$viewsPath = $basePath . '/resources/views/portal';

$replacements = [
    // ─── Placeholders ───
    'placeholder="Search class..."'       => 'placeholder="{{ __(\'portal.search_class\') }}"',
    'placeholder="Search school..."'      => 'placeholder="{{ __(\'portal.search_school_ph\') }}"',
    'placeholder="Search"'                => 'placeholder="{{ __(\'portal.search\') }}"',
    'placeholder="Enter school name"'     => 'placeholder="{{ __(\'portal.enter_school_name\') }}"',
    'placeholder="https://"'              => 'placeholder="https://"', // keep as-is, it's URL
    'placeholder="Explain why you need additional seats..."' => 'placeholder="{{ __(\'portal.explain_seats\') }}"',
    'placeholder="Enter first name"'      => 'placeholder="{{ __(\'portal.enter_first_name\') }}"',
    'placeholder="Enter last name"'       => 'placeholder="{{ __(\'portal.enter_last_name\') }}"',
    'placeholder="name@example.com"'      => 'placeholder="{{ __(\'portal.email_placeholder\') }}"',
    'placeholder="Minimum 6 characters"'  => 'placeholder="{{ __(\'portal.min_6_chars\') }}"',
    'placeholder="admin@school.edu.tr"'   => 'placeholder="{{ __(\'portal.email_placeholder\') }}"',
    'placeholder="Please select"'         => 'placeholder="{{ __(\'portal.please_select\') }}"',

    // ─── Title attributes ───
    'title="Detail"'                      => 'title="{{ __(\'portal.detail\') }}"',
    'title="Edit"'                        => 'title="{{ __(\'admin.edit\') }}"',
    'title="Delete"'                      => 'title="{{ __(\'admin.delete\') }}"',
    'title="Remove"'                      => 'title="{{ __(\'portal.remove\') }}"',
    'title="Report"'                      => 'title="{{ __(\'portal.report\') }}"',
    'title="View Details"'                => 'title="{{ __(\'portal.view_details\') }}"',
    'title="View Report"'                 => 'title="{{ __(\'portal.view_report\') }}"',
    'title="Student Report"'              => 'title="{{ __(\'portal.student_report\') }}"',
    'title="Download"'                    => 'title="{{ __(\'portal.download\') }}"',
    'title="Overdue"'                     => 'title="{{ __(\'portal.overdue\') }}"',
    'title="Alert"'                       => 'title="{{ __(\'portal.alert\') }}"',
    'title="Study Space Detail"'          => 'title="{{ __(\'portal.study_space_detail\') }}"',
    'title="WAY AI Coach Detail"'         => 'title="{{ __(\'portal.way_ai_coach_detail\') }}"',
    'title="Role Galaxy Detail"'          => 'title="{{ __(\'portal.role_galaxy_detail\') }}"',
    'title="Details"'                     => 'title="{{ __(\'portal.view_details\') }}"',

    // ─── Ternary patterns ───
    "? 'Edit Class'"                      => "? __('portal.edit_class')",
    ": 'Create Class'"                    => ": __('portal.create_class')",
    "? 'Edit License'"                    => "? __('portal.edit_license')",
    ": 'New License'"                     => ": __('portal.new_license')",
    "? 'Edit User'"                       => "? __('portal.edit_user')",
    ": 'Add New User'"                    => ": __('portal.add_new_user')",
    "? 'Edit School'"                     => "? __('portal.edit_school')",
    ": 'New School'"                      => ": __('portal.new_school')",
    "? 'Active' :"                        => "? __('portal.active') :",
    ": 'Inactive'"                        => ": __('portal.inactive')",
    ": 'Not Started'"                     => ": __('portal.not_started')",
    "? 'Completed'"                       => "? __('portal.completed')",
    ": 'In Progress'"                     => ": __('portal.in_progress')",
    "? 'Premium'"                         => "? __('portal.premium')",
    ": 'Free'"                            => ": __('portal.free')",
    "? 'Add New Teacher'"                 => "? __('portal.add_new_teacher')",
    ": 'Add New Student'"                 => ": __('portal.add_new_student')",
    "? 'Student Name'"                    => "? __('portal.student_name')",
    ": 'Teacher Name'"                    => ": __('portal.teacher_name')",
    "? 'Fill to change'"                  => "? __('portal.fill_to_change')",

    // ─── Confirm dialogs ───
    "confirm('Are you sure you want to delete this class? This action cannot be undone.')" => "confirm('{{ __(\"portal.confirm_delete_class\") }}')",
    "confirm('Are you sure you want to delete class {{ \$cls->name }}? This cannot be undone.')" => "confirm('{{ __(\"portal.confirm_delete_class\") }}')",
    "confirm('Are you sure you want to delete this user? This action cannot be undone.')" => "confirm('{{ __(\"portal.confirm_delete_user\") }}')",

    // ─── Card titles ───
    '>👥 Student Performance<'            => '>👥 {{ __(\'portal.student_performance\') }}<',
    '>👥 Class Students<'                 => '>👥 {{ __(\'portal.class_students\') }}<',
    '>🦋 Way Wings (Badges)<'             => '>🦋 {{ __(\'portal.way_wings_badges\') }}<',
    '>📚 Lesson Catalog<'                 => '>📚 {{ __(\'portal.lesson_catalog\') }}<',
    '>🎮 Scenario Catalog<'               => '>🎮 {{ __(\'portal.scenario_catalog\') }}<',
    '>🎯 Simulation Objectives<'          => '>🎯 {{ __(\'portal.simulation_objectives\') }}<',
    '>🖼️ Media Assets<'                  => '>🖼️ {{ __(\'portal.media_assets\') }}<',
    '>📊 SimulationWing Statistics<'       => '>📊 {{ __(\'portal.simulation_wing_stats\') }}<',
    '>🎭 Version Roles<'                  => '>🎭 {{ __(\'portal.version_roles\') }}<',
    '>🌐 Supported Languages<'            => '>🌐 {{ __(\'portal.supported_languages\') }}<',

    // ─── Subtitle ───
    ">Are you sure you want to reset this user's password?<" => ">{{ __('portal.confirm_reset_password_msg') }}<",

    // ─── Labels ───
    '>E-mail *<'                          => '>{{ __(\'admin.email\') }} *<',
    '>Amount (₺)<'                        => '>{{ __(\'portal.amount\') }} (₺)<',
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
echo "\n═══ Wave 6: Modified $fileCount files ═══\n";

// Keys
$keys = [
    'tr' => [
        'search_class'=>'Sınıf ara...','search_school_ph'=>'Okul ara...','enter_school_name'=>'Okul adını girin',
        'explain_seats'=>'Ek koltuk neden gerektiğini açıklayın...','enter_first_name'=>'Adı girin',
        'enter_last_name'=>'Soyadı girin','email_placeholder'=>'ad@ornek.com','min_6_chars'=>'En az 6 karakter',
        'remove'=>'Kaldır','report'=>'Rapor','view_report'=>'Rapor Görüntüle',
        'student_report'=>'Öğrenci Raporu','download'=>'İndir','overdue'=>'Gecikmiş','alert'=>'Uyarı',
        'study_space_detail'=>'Study Space Detayı','way_ai_coach_detail'=>'WAY AI Coach Detayı',
        'role_galaxy_detail'=>'Role Galaxy Detayı',
        'edit_class'=>'Sınıf Düzenle','create_class'=>'Sınıf Oluştur',
        'edit_license'=>'Lisans Düzenle','new_license'=>'Yeni Lisans',
        'edit_user'=>'Kullanıcı Düzenle','add_new_user'=>'Yeni Kullanıcı Ekle',
        'new_school'=>'Yeni Okul',
        'in_progress'=>'Devam Ediyor','premium'=>'Premium','free'=>'Ücretsiz',
        'add_new_teacher'=>'Yeni Öğretmen Ekle','add_new_student'=>'Yeni Öğrenci Ekle',
        'student_name'=>'Öğrenci Adı','teacher_name'=>'Öğretmen Adı',
        'fill_to_change'=>'Değiştirmek için doldurun',
        'confirm_delete_class'=>'Bu sınıfı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
        'confirm_delete_user'=>'Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
        'student_performance'=>'Öğrenci Performansı','class_students'=>'Sınıf Öğrencileri',
        'way_wings_badges'=>'Way Wings (Rozetler)','lesson_catalog'=>'Ders Kataloğu',
        'scenario_catalog'=>'Senaryo Kataloğu','simulation_objectives'=>'Simülasyon Hedefleri',
        'media_assets'=>'Medya Dosyaları','simulation_wing_stats'=>'SimülasyonWing İstatistikleri',
        'version_roles'=>'Sürüm Rolleri','supported_languages'=>'Desteklenen Diller',
        'confirm_reset_password_msg'=>'Bu kullanıcının şifresini sıfırlamak istediğinizden emin misiniz?',
    ],
    'en' => [
        'search_class'=>'Search class...','search_school_ph'=>'Search school...','enter_school_name'=>'Enter school name',
        'explain_seats'=>'Explain why you need additional seats...','enter_first_name'=>'Enter first name',
        'enter_last_name'=>'Enter last name','email_placeholder'=>'name@example.com','min_6_chars'=>'Minimum 6 characters',
        'remove'=>'Remove','report'=>'Report','view_report'=>'View Report',
        'student_report'=>'Student Report','download'=>'Download','overdue'=>'Overdue','alert'=>'Alert',
        'study_space_detail'=>'Study Space Detail','way_ai_coach_detail'=>'WAY AI Coach Detail',
        'role_galaxy_detail'=>'Role Galaxy Detail',
        'edit_class'=>'Edit Class','create_class'=>'Create Class',
        'edit_license'=>'Edit License','new_license'=>'New License',
        'edit_user'=>'Edit User','add_new_user'=>'Add New User',
        'new_school'=>'New School',
        'in_progress'=>'In Progress','premium'=>'Premium','free'=>'Free',
        'add_new_teacher'=>'Add New Teacher','add_new_student'=>'Add New Student',
        'student_name'=>'Student Name','teacher_name'=>'Teacher Name',
        'fill_to_change'=>'Fill to change',
        'confirm_delete_class'=>'Are you sure you want to delete this class? This action cannot be undone.',
        'confirm_delete_user'=>'Are you sure you want to delete this user? This action cannot be undone.',
        'student_performance'=>'Student Performance','class_students'=>'Class Students',
        'way_wings_badges'=>'Way Wings (Badges)','lesson_catalog'=>'Lesson Catalog',
        'scenario_catalog'=>'Scenario Catalog','simulation_objectives'=>'Simulation Objectives',
        'media_assets'=>'Media Assets','simulation_wing_stats'=>'SimulationWing Statistics',
        'version_roles'=>'Version Roles','supported_languages'=>'Supported Languages',
        'confirm_reset_password_msg'=>'Are you sure you want to reset this user\'s password?',
    ],
    'mn' => [
        'search_class'=>'Анги хайх...','search_school_ph'=>'Сургууль хайх...','enter_school_name'=>'Сургуулийн нэр оруулах',
        'explain_seats'=>'Нэмэлт суудал хэрэгтэй шалтгааныг тайлбарлана уу...','enter_first_name'=>'Нэр оруулах',
        'enter_last_name'=>'Овог оруулах','email_placeholder'=>'нэр@жишээ.com','min_6_chars'=>'Хамгийн багадаа 6 тэмдэгт',
        'remove'=>'Хасах','report'=>'Тайлан','view_report'=>'Тайлан харах',
        'student_report'=>'Сурагчийн тайлан','download'=>'Татах','overdue'=>'Хугацаа хэтэрсэн','alert'=>'Анхааруулга',
        'study_space_detail'=>'Study Space дэлгэрэнгүй','way_ai_coach_detail'=>'WAY AI Coach дэлгэрэнгүй',
        'role_galaxy_detail'=>'Role Galaxy дэлгэрэнгүй',
        'edit_class'=>'Анги засах','create_class'=>'Анги үүсгэх',
        'edit_license'=>'Лиценз засах','new_license'=>'Шинэ лиценз',
        'edit_user'=>'Хэрэглэгч засах','add_new_user'=>'Шинэ хэрэглэгч нэмэх',
        'new_school'=>'Шинэ сургууль',
        'in_progress'=>'Явагдаж буй','premium'=>'Премиум','free'=>'Үнэгүй',
        'add_new_teacher'=>'Шинэ багш нэмэх','add_new_student'=>'Шинэ сурагч нэмэх',
        'student_name'=>'Сурагчийн нэр','teacher_name'=>'Багшийн нэр',
        'fill_to_change'=>'Өөрчлөхийн тулд бөглөнө үү',
        'confirm_delete_class'=>'Энэ ангийг устгахдаа итгэлтэй байна уу? Энэ үйлдлийг буцааж болохгүй.',
        'confirm_delete_user'=>'Энэ хэрэглэгчийг устгахдаа итгэлтэй байна уу? Энэ үйлдлийг буцааж болохгүй.',
        'student_performance'=>'Сурагчийн гүйцэтгэл','class_students'=>'Ангийн сурагчид',
        'way_wings_badges'=>'Way Wings (Тэмдэг)','lesson_catalog'=>'Хичээлийн каталог',
        'scenario_catalog'=>'Сценарийн каталог','simulation_objectives'=>'Симуляцын зорилго',
        'media_assets'=>'Медиа файлууд','simulation_wing_stats'=>'SimulationWing Статистик',
        'version_roles'=>'Хувилбарын дүрүүд','supported_languages'=>'Дэмжигдсэн хэлүүд',
        'confirm_reset_password_msg'=>'Энэ хэрэглэгчийн нууц үгийг шинэчлэхдээ итгэлтэй байна уу?',
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
echo "✅ Wave 6 keys appended!\n";
