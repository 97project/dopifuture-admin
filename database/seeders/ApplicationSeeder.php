<?php

namespace Database\Seeders;

use App\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $apps = [
            [
                'slug' => 'mission-way',
                'name' => ['tr' => 'Mission Way', 'en' => 'Mission Way'],
                'description' => [
                    'tr' => 'Görev tabanlı öğrenme platformu ile öğrencileri keşfe yönlendirin.',
                    'en' => 'Guide students to explore with a mission-based learning platform.',
                ],
                'icon' => 'rocket',
                'color' => '#3B82F6',
                'connector_class' => 'App\\Connectors\\MissionWayConnector',
                'sort_order' => 1,
            ],
            [
                'slug' => 'way-startup',
                'name' => ['tr' => 'Way Startup', 'en' => 'Way Startup'],
                'description' => [
                    'tr' => 'Girişimcilik simülasyonu ile gençlere iş dünyası deneyimi.',
                    'en' => 'Entrepreneurship simulation giving youth real business experience.',
                ],
                'icon' => 'briefcase',
                'color' => '#10B981',
                'connector_class' => 'App\\Connectors\\WayStartupConnector',
                'sort_order' => 2,
            ],
            [
                'slug' => 'role-galaxy',
                'name' => ['tr' => 'Role Galaxy', 'en' => 'Role Galaxy'],
                'description' => [
                    'tr' => 'Rol yapma ve karakter geliştirme ile sosyal becerileri güçlendirin.',
                    'en' => 'Strengthen social skills through role-playing and character building.',
                ],
                'icon' => 'star',
                'color' => '#8B5CF6',
                'connector_class' => 'App\\Connectors\\RoleGalaxyConnector',
                'sort_order' => 3,
            ],
            [
                'slug' => 'way-ai-coach',
                'name' => ['tr' => 'Way AI Coach', 'en' => 'Way AI Coach'],
                'description' => [
                    'tr' => 'Yapay zeka destekli kişisel koçluk ile her öğrenciye özel rehberlik.',
                    'en' => 'AI-powered personal coaching providing tailored guidance for every student.',
                ],
                'icon' => 'cpu',
                'color' => '#F59E0B',
                'connector_class' => 'App\\Connectors\\WayAiCoachConnector',
                'sort_order' => 4,
            ],
            [
                'slug' => 'study-space',
                'name' => ['tr' => 'Study Space', 'en' => 'Study Space'],
                'description' => [
                    'tr' => 'Odaklanma ve verimli çalışma alanı ile ders çalışma deneyimini dönüştürün.',
                    'en' => 'Transform study experience with focused and productive workspaces.',
                ],
                'icon' => 'book-open',
                'color' => '#EF4444',
                'connector_class' => 'App\\Connectors\\StudySpaceConnector',
                'sort_order' => 5,
            ],
        ];

        foreach ($apps as $data) {
            Application::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
