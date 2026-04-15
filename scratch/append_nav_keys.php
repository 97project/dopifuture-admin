<?php
$nav_keys = [
    'tr' => [
        'nav_startup'     => 'Startup',
        'nav_role_galaxy' => 'Role Galaxy',
        'nav_study_space' => 'Study Space',
        'nav_way_ai_coach'=> 'WAY AI Coach',
        'nav_students'    => 'Öğrenciler',
        'nav_teachers'    => 'Öğretmenler',
        'nav_admin_panel' => 'Admin Panel',
        'search_students' => 'Öğrenci ara...',
    ],
    'en' => [
        'nav_startup'     => 'Startup',
        'nav_role_galaxy' => 'Role Galaxy',
        'nav_study_space' => 'Study Space',
        'nav_way_ai_coach'=> 'WAY AI Coach',
        'nav_students'    => 'Students',
        'nav_teachers'    => 'Teachers',
        'nav_admin_panel' => 'Admin Panel',
        'search_students' => 'Search students...',
    ],
    'mn' => [
        'nav_startup'     => 'Startup',
        'nav_role_galaxy' => 'Role Galaxy',
        'nav_study_space' => 'Study Space',
        'nav_way_ai_coach'=> 'WAY AI Coach',
        'nav_students'    => 'Сурагчид',
        'nav_teachers'    => 'Багш нар',
        'nav_admin_panel' => 'Админ самбар',
        'search_students' => 'Сурагч хайх...',
    ],
];

function appendToPortal($path, $data) {
    if (!file_exists($path)) return;
    $existing = require $path;
    $merged = array_merge($existing, $data);
    file_put_contents($path, "<?php\n\nreturn " . var_export($merged, true) . ";\n");
}

appendToPortal(dirname(__DIR__) . '/lang/tr/portal.php', $nav_keys['tr']);
appendToPortal(dirname(__DIR__) . '/lang/en/portal.php', $nav_keys['en']);
appendToPortal(dirname(__DIR__) . '/lang/mn/portal.php', $nav_keys['mn']);

echo "Nav and search keys appended!";
