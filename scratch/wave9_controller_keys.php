<?php
$base = dirname(__DIR__);
$keys = [
    'tr' => [
        'no_permission'=>'Bu işlem için yetkiniz yok.',
        'selected_students_not_found'=>'Seçili öğrenciler bulunamadı.',
        'mw_students_not_mapped'=>'Seçili öğrencilerin MW hesabı eşleştirilemedi.',
        'ws_students_not_found'=>'Seçili öğrencilerin Way Startup hesabı bulunamadı.',
        'mission_added_success'=>'Yeni görev başarıyla eklendi.',
        'mission_create_error'=>'Görev oluşturulamadı: :msg (HTTP :code).',
        'mission_create_exception'=>'Görev oluşturma hatası: :msg',
        'assignment_added_success'=>'Yeni atama başarıyla eklendi.',
        'assignment_create_error'=>'Atama oluşturulamadı: :msg (HTTP :code).',
        'assignment_create_exception'=>'Atama oluşturma hatası: :msg',
        'member_removed_mission'=>'Üye görevden çıkarıldı.',
        'member_removed_project'=>'Üye projeden çıkarıldı.',
        'member_remove_error'=>'Üye çıkarılamadı (HTTP :code)',
        'operation_error'=>'İşlem hatası: :msg',
        'password_reset_success'=>':name için şifre sıfırlandı. Yeni şifre: :password',
    ],
    'en' => [
        'no_permission'=>'You do not have permission for this action.',
        'selected_students_not_found'=>'Selected students not found.',
        'mw_students_not_mapped'=>'Selected students could not be mapped to MW backend accounts.',
        'ws_students_not_found'=>'Selected students\' Way Startup accounts not found.',
        'mission_added_success'=>'New Mission added successfully.',
        'mission_create_error'=>'Could not create mission: :msg (HTTP :code).',
        'mission_create_exception'=>'Error creating mission: :msg',
        'assignment_added_success'=>'New Assignment added successfully.',
        'assignment_create_error'=>'Could not create assignment: :msg (HTTP :code).',
        'assignment_create_exception'=>'Error creating assignment: :msg',
        'member_removed_mission'=>'Member removed from mission.',
        'member_removed_project'=>'Member removed from project.',
        'member_remove_error'=>'Could not remove member (HTTP :code)',
        'operation_error'=>'Operation error: :msg',
        'password_reset_success'=>'Password reset for :name. New password: :password',
    ],
    'mn' => [
        'no_permission'=>'Энэ үйлдлийг хийх зөвшөөрөл байхгүй.',
        'selected_students_not_found'=>'Сонгосон сурагчид олдсонгүй.',
        'mw_students_not_mapped'=>'Сонгосон сурагчдын MW бүртгэл тохируулж чадсангүй.',
        'ws_students_not_found'=>'Сонгосон сурагчдын Way Startup бүртгэл олдсонгүй.',
        'mission_added_success'=>'Шинэ даалгавар амжилттай нэмэгдлээ.',
        'mission_create_error'=>'Даалгавар үүсгэж чадсангүй: :msg (HTTP :code).',
        'mission_create_exception'=>'Даалгавар үүсгэх алдаа: :msg',
        'assignment_added_success'=>'Шинэ хуваарилалт амжилттай нэмэгдлээ.',
        'assignment_create_error'=>'Хуваарилалт үүсгэж чадсангүй: :msg (HTTP :code).',
        'assignment_create_exception'=>'Хуваарилалт үүсгэх алдаа: :msg',
        'member_removed_mission'=>'Гишүүнийг даалгавраас хассан.',
        'member_removed_project'=>'Гишүүнийг төслөөс хассан.',
        'member_remove_error'=>'Гишүүнийг хасаж чадсангүй (HTTP :code)',
        'operation_error'=>'Үйлдлийн алдаа: :msg',
        'password_reset_success'=>':name-ийн нууц үг шинэчлэгдсэн. Шинэ нууц үг: :password',
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
echo "Done: controller keys\n";
