<?php
$base = dirname(__DIR__);
$keys = [
    'tr' => [
        'back' => 'Geri',
        'add' => 'Ekle',
        'no_students_in_class' => 'Bu sınıfta henüz öğrenci yok.',
        'no_teachers_in_class' => 'Bu sınıfa henüz öğretmen atanmadı.',
        'confirm_remove_student' => 'Bu öğrenciyi sınıftan çıkarmak istediğinizden emin misiniz?',
        'confirm_remove_teacher' => 'Bu öğretmeni sınıftan çıkarmak istediğinizden emin misiniz?',
    ],
    'en' => [
        'back' => 'Back',
        'add' => 'Add',
        'no_students_in_class' => 'No students in this class yet.',
        'no_teachers_in_class' => 'No teachers assigned to this class yet.',
        'confirm_remove_student' => 'Remove this student from class?',
        'confirm_remove_teacher' => 'Remove this teacher from class?',
    ],
    'mn' => [
        'back' => 'Буцах',
        'add' => 'Нэмэх',
        'no_students_in_class' => 'Энэ ангид одоогоор сурагч байхгүй.',
        'no_teachers_in_class' => 'Энэ ангид одоогоор багш хуваарилагдаагүй.',
        'confirm_remove_student' => 'Энэ сурагчийг ангиас хасах уу?',
        'confirm_remove_teacher' => 'Энэ багшийг ангиас хасах уу?',
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
echo "Done!\n";
