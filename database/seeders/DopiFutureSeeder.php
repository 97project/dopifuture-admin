<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\License;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DopiFutureSeeder extends Seeder
{
    private string $password;
    private array $applications;

    public function run(): void
    {
        $this->password = Hash::make('password');

        // ─── 1. Applications (5 adet) ──────────────────────
        $this->seedApplications();
        $this->applications = Application::all()->pluck('id')->toArray();

        // ─── 2. System-level users ─────────────────────────
        $this->seedSystemUsers();

        // ─── 3. Schools (5 adet) ───────────────────────────
        $schools = $this->seedSchools();

        // ─── 4. Classes, Teachers, Students per school ─────
        foreach ($schools as $school) {
            $this->seedSchoolData($school);
        }

        $this->command->info('✅ DopiFuture seed data created successfully!');
        $this->command->info("   📊 Schools: " . School::count());
        $this->command->info("   📚 Classes: " . SchoolClass::count());
        $this->command->info("   👥 Users: " . User::count());
        $this->command->info("   📄 Licenses: " . License::count());
        $this->command->info("   🔑 All passwords: 'password'");
    }

    // ─────────────────────────────────────────────────────────
    // Applications
    // ─────────────────────────────────────────────────────────

    private function seedApplications(): void
    {
        $apps = [
            ['slug' => 'mission-way', 'name' => ['tr' => 'Mission Way', 'en' => 'Mission Way'], 'description' => ['tr' => 'Görev tabanlı öğrenme platformu ile öğrencileri keşfe yönlendirin.', 'en' => 'Guide students to explore with a mission-based learning platform.'], 'icon' => 'rocket', 'color' => '#3B82F6', 'sort_order' => 1],
            ['slug' => 'way-startup', 'name' => ['tr' => 'Way Startup', 'en' => 'Way Startup'], 'description' => ['tr' => 'Girişimcilik simülasyonu ile gençlere iş dünyası deneyimi.', 'en' => 'Entrepreneurship simulation giving youth real business experience.'], 'icon' => 'briefcase', 'color' => '#10B981', 'sort_order' => 2],
            ['slug' => 'role-galaxy', 'name' => ['tr' => 'Role Galaxy', 'en' => 'Role Galaxy'], 'description' => ['tr' => 'Rol yapma ve karakter geliştirme ile sosyal becerileri güçlendirin.', 'en' => 'Strengthen social skills through role-playing and character building.'], 'icon' => 'star', 'color' => '#8B5CF6', 'sort_order' => 3],
            ['slug' => 'way-ai-coach', 'name' => ['tr' => 'Way AI Coach', 'en' => 'Way AI Coach'], 'description' => ['tr' => 'Yapay zeka destekli kişisel koçluk ile her öğrenciye özel rehberlik.', 'en' => 'AI-powered personal coaching providing tailored guidance for every student.'], 'icon' => 'cpu', 'color' => '#F59E0B', 'sort_order' => 4],
            ['slug' => 'study-space', 'name' => ['tr' => 'Study Space', 'en' => 'Study Space'], 'description' => ['tr' => 'Odaklanma ve verimli çalışma alanı ile ders çalışma deneyimini dönüştürün.', 'en' => 'Transform study experience with focused and productive workspaces.'], 'icon' => 'book-open', 'color' => '#EF4444', 'sort_order' => 5],
        ];

        foreach ($apps as $data) {
            Application::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }

    // ─────────────────────────────────────────────────────────
    // System-level users (admin, moderator, license-manager)
    // ─────────────────────────────────────────────────────────

    private function seedSystemUsers(): void
    {
        $systemUsers = [
            ['email' => 'admin@dopifuture.com', 'name' => 'Admin', 'surname' => 'DopiFuture', 'role' => 'super-admin'],
            ['email' => 'moderator@dopifuture.com', 'name' => 'Moderatör', 'surname' => 'Yılmaz', 'role' => 'moderator'],
            ['email' => 'editor@dopifuture.com', 'name' => 'Editör', 'surname' => 'Demir', 'role' => 'editor'],
            ['email' => 'license@dopifuture.com', 'name' => 'Lisans', 'surname' => 'Yöneticisi', 'role' => 'license-manager'],
        ];

        foreach ($systemUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'surname' => $data['surname'],
                    'password' => $this->password,
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'locale' => 'tr',
                    'timezone' => 'Europe/Istanbul',
                ]
            );
            if (!$user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // Schools
    // ─────────────────────────────────────────────────────────

    private function seedSchools(): array
    {
        $schoolsData = [
            [
                'name' => ['tr' => 'İstanbul Fen Lisesi', 'en' => 'Istanbul Science High School'],
                'country' => 'Türkiye',
                'city' => 'İstanbul',
                'phone' => '+90 212 555 0001',
                'email' => 'info@istanbulfen.edu.tr',
            ],
            [
                'name' => ['tr' => 'Ankara Koleji', 'en' => 'Ankara College'],
                'country' => 'Türkiye',
                'city' => 'Ankara',
                'phone' => '+90 312 555 0002',
                'email' => 'info@ankarakoleji.edu.tr',
            ],
            [
                'name' => ['tr' => 'İzmir Özel Lisesi', 'en' => 'Izmir Private High School'],
                'country' => 'Türkiye',
                'city' => 'İzmir',
                'phone' => '+90 232 555 0003',
                'email' => 'info@izmirlise.edu.tr',
            ],
            [
                'name' => ['tr' => 'Bursa Anadolu Lisesi', 'en' => 'Bursa Anatolian High School'],
                'country' => 'Türkiye',
                'city' => 'Bursa',
                'phone' => '+90 224 555 0004',
                'email' => 'info@bursaanadolu.edu.tr',
            ],
            [
                'name' => ['tr' => 'Antalya Bilim Koleji', 'en' => 'Antalya Science College'],
                'country' => 'Türkiye',
                'city' => 'Antalya',
                'phone' => '+90 242 555 0005',
                'email' => 'info@antalyabilim.edu.tr',
            ],
        ];

        $schools = [];
        foreach ($schoolsData as $data) {
            $schools[] = School::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['is_active' => true])
            );
        }

        return $schools;
    }

    // ─────────────────────────────────────────────────────────
    // Per-school: admin, principal, classes, teachers, students
    // ─────────────────────────────────────────────────────────

    private function seedSchoolData(School $school): void
    {
        // Extract shortname from school email domain, e.g. "info@istanbulfen.edu.tr" → "istanbulfen"
        $domain = explode('@', $school->email)[1] ?? 'school';
        $shortName = explode('.', $domain)[0]; // "istanbulfen"

        // ─── School Admin ─────────────────────────────────
        $schoolAdmin = $this->createUser(
            "admin@{$shortName}.edu.tr",
            'Okul Admin',
            ucfirst($shortName),
            'school-admin'
        );
        $this->attachToSchool($school, $schoolAdmin, 'school-admin');

        // ─── School Principal ─────────────────────────────
        $principal = $this->createUser(
            "mudur@{$shortName}.edu.tr",
            'Müdür',
            ucfirst($shortName),
            'school-principal'
        );
        $this->attachToSchool($school, $principal, 'principal');

        // ─── Classes (5 per school) ───────────────────────
        $grades = ['9-A', '9-B', '10-A', '10-B', '11-A'];
        $gradeLevels = ['9', '9', '10', '10', '11'];
        $classes = [];

        foreach ($grades as $i => $className) {
            $classes[] = SchoolClass::firstOrCreate(
                ['school_id' => $school->id, 'name' => $className],
                [
                    'grade_level' => $gradeLevels[$i],
                    'academic_year' => '2025-2026',
                    'is_active' => true,
                ]
            );
        }

        // ─── Teachers (10 per school, distributed across classes) ──
        $teacherNames = [
            ['Ahmet', 'Yılmaz'],
            ['Fatma', 'Kaya'],
            ['Mehmet', 'Demir'],
            ['Ayşe', 'Çelik'],
            ['Ali', 'Şahin'],
            ['Zeynep', 'Türk'],
            ['Mustafa', 'Aydın'],
            ['Elif', 'Özkan'],
            ['Hüseyin', 'Arslan'],
            ['Hatice', 'Koç'],
        ];

        $teachers = [];
        foreach ($teacherNames as $idx => $nameParts) {
            $email = "ogretmen" . ($idx + 1) . "@{$shortName}.edu.tr";
            $teacher = $this->createUser($email, $nameParts[0], $nameParts[1], 'teacher');
            $this->attachToSchool($school, $teacher, 'teacher');
            $teachers[] = $teacher;

            // Each teacher assigned to 1-2 classes
            $classIdx = $idx % count($classes);
            $this->attachToClass($classes[$classIdx], $teacher, 'teacher');
            if ($idx < 5) {
                $nextIdx = ($classIdx + 1) % count($classes);
                $this->attachToClass($classes[$nextIdx], $teacher, 'teacher');
            }
        }

        // ─── Students (10 per class = 50 per school) ──────
        $firstNames = [
            'Arda',
            'Berk',
            'Can',
            'Deniz',
            'Ege',
            'Furkan',
            'Gizem',
            'Hande',
            'Ilgın',
            'Jade',
            'Kaan',
            'Lale',
            'Mert',
            'Nisa',
            'Onur',
            'Pelin',
            'Rüzgar',
            'Sinem',
            'Tolga',
            'Umut',
            'Volkan',
            'Yasemin',
            'Zehra',
            'Burak',
            'Ceren',
            'Doruk',
            'Esra',
            'Ferhat',
            'Güneş',
            'Hakan',
            'İrem',
            'Kerem',
            'Melis',
            'Nehir',
            'Ozan',
            'Pınar',
            'Rana',
            'Selim',
            'Tuğçe',
            'Utku',
            'Veli',
            'Yağmur',
            'Berke',
            'Cemre',
            'Defne',
            'Emre',
            'Fırat',
            'Gökhan',
            'Hazal',
            'İlker'
        ];
        $lastNames = ['Yılmaz', 'Kaya', 'Demir', 'Çelik', 'Şahin', 'Öztürk', 'Aydın', 'Özdemir', 'Arslan', 'Doğan'];

        $studentIdx = 0;
        foreach ($classes as $class) {
            for ($s = 0; $s < 10; $s++) {
                $fn = $firstNames[$studentIdx % count($firstNames)];
                $ln = $lastNames[$studentIdx % count($lastNames)];
                $email = "ogrenci" . ($studentIdx + 1) . "@{$shortName}.edu.tr";

                $student = $this->createUser($email, $fn, $ln, 'student');
                $this->attachToSchool($school, $student, 'student');
                $this->attachToClass($class, $student, 'student');

                // Random application permissions (1-3 apps per student)
                $appCount = rand(1, 3);
                $appIds = (array) array_rand(array_flip($this->applications), min($appCount, count($this->applications)));
                foreach ($appIds as $appId) {
                    DB::table('application_user')->insertOrIgnore([
                        'application_id' => $appId,
                        'user_id' => $student->id,
                        'granted_by' => $schoolAdmin->id,
                        'granted_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $studentIdx++;
            }
        }

        // ─── License (single per school) ──────────────────
        // License = student capacity. 50 students per school.
        License::firstOrCreate(
            ['school_id' => $school->id],
            [
                'user_id' => $schoolAdmin->id,
                'seat_count' => 100,
                'used_seats' => 50,
                'starts_at' => now()->subMonths(3)->toDateString(),
                'expires_at' => now()->addYear()->toDateString(),
                'is_active' => true,
                'notes' => "School license — {$school->getTranslation('name', 'tr')}",
            ]
        );
    }

    // ─────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────

    private function createUser(string $email, string $name, string $surname, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'surname' => $surname,
                'password' => $this->password,
                'email_verified_at' => now(),
                'status' => 'active',
                'locale' => 'tr',
                'timezone' => 'Europe/Istanbul',
            ]
        );

        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function attachToSchool(School $school, User $user, string $role): void
    {
        DB::table('school_user')->insertOrIgnore([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachToClass(SchoolClass $class, User $user, string $role): void
    {
        DB::table('class_user')->insertOrIgnore([
            'class_id' => $class->id,
            'user_id' => $user->id,
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
