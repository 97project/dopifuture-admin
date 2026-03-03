<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\AppUserProgress;
use App\Models\AppUserSession;
use App\Models\License;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Portal Demo Seeder — tüm sayfaları dolu veriyle doldurur.
 * php artisan db:seed --class=PortalDemoSeeder
 */
class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::find(12) ?? School::first();
        if (!$school) {
            $this->command->error('No school found. Run base seeders first.');
            return;
        }

        $schoolId = $school->id;

        // ── Ensure school-admin role exists ──
        $adminRole = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // ── Create Teacher Users ──
        $teacherNames = [
            ['name' => 'Mehmet', 'surname' => 'Yıldız', 'email' => 'mehmet.yildiz@demo.dopifuture.com'],
            ['name' => 'Zeynep', 'surname' => 'Kara', 'email' => 'zeynep.kara@demo.dopifuture.com'],
        ];

        $teachers = [];
        foreach ($teacherNames as $t) {
            $user = User::firstOrCreate(
                ['email' => $t['email']],
                ['name' => $t['name'], 'surname' => $t['surname'], 'password' => Hash::make('Test1234!')]
            );
            $user->syncRoles([$teacherRole]);
            DB::table('school_user')->insertOrIgnore([
                'school_id' => $schoolId, 'user_id' => $user->id, 'role' => 'teacher',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $teachers[] = $user;
        }

        // ── Create Student Users ──
        $studentNames = [
            ['name' => 'Elif', 'surname' => 'Demir', 'email' => 'elif.demir@demo.dopifuture.com'],
            ['name' => 'Ahmet', 'surname' => 'Çelik', 'email' => 'ahmet.celik@demo.dopifuture.com'],
            ['name' => 'Fatma', 'surname' => 'Şahin', 'email' => 'fatma.sahin@demo.dopifuture.com'],
            ['name' => 'Emre', 'surname' => 'Aydın', 'email' => 'emre.aydin@demo.dopifuture.com'],
            ['name' => 'Selin', 'surname' => 'Öztürk', 'email' => 'selin.ozturk@demo.dopifuture.com'],
            ['name' => 'Burak', 'surname' => 'Yılmaz', 'email' => 'burak.yilmaz@demo.dopifuture.com'],
            ['name' => 'Cansu', 'surname' => 'Koç', 'email' => 'cansu.koc@demo.dopifuture.com'],
            ['name' => 'Deniz', 'surname' => 'Arslan', 'email' => 'deniz.arslan@demo.dopifuture.com'],
        ];

        $students = [];
        foreach ($studentNames as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['email']],
                ['name' => $s['name'], 'surname' => $s['surname'], 'password' => Hash::make('Test1234!')]
            );
            $user->syncRoles([$studentRole]);
            DB::table('school_user')->insertOrIgnore([
                'school_id' => $schoolId, 'user_id' => $user->id, 'role' => 'student',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $students[] = $user;
        }

        // ── Create Classes ──
        $classNames = ['9-A', '10-B', '11-C'];
        $classes = [];
        foreach ($classNames as $i => $className) {
            $class = SchoolClass::firstOrCreate(
                ['school_id' => $schoolId, 'name' => $className],
                ['grade_level' => (string)(9 + $i), 'academic_year' => '2025-2026', 'is_active' => true]
            );
            $classes[] = $class;

            // Assign teacher
            if (isset($teachers[$i % count($teachers)])) {
                DB::table('class_user')->insertOrIgnore([
                    'class_id' => $class->id, 'user_id' => $teachers[$i % count($teachers)]->id,
                    'role' => 'teacher', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // Assign students to classes
        foreach ($students as $i => $student) {
            $classIndex = $i % count($classes);
            DB::table('class_user')->insertOrIgnore([
                'class_id' => $classes[$classIndex]->id, 'user_id' => $student->id,
                'role' => 'student', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ── Update existing License for school ──
        License::updateOrCreate(
            ['school_id' => $schoolId],
            ['seat_count' => 105, 'used_seats' => 72, 'starts_at' => '2026-02-27', 'expires_at' => '2027-02-27', 'is_active' => true]
        );

        // ── Seed App Progress & Sessions ──
        $apps = Application::active()->ordered()->get();
        $moduleTypes = [
            'mission-way' => ['simulation', 'step', 'practice'],
            'way-startup' => ['simulation', 'session', 'step'],
            'role-galaxy' => ['simulation', 'session'],
            'way-ai-coach' => ['chat', 'session', 'lecture'],
            'study-space' => ['session', 'practice', 'lecture'],
        ];

        $sessionTypes = [
            'mission-way' => ['simulation', 'practice'],
            'way-startup' => ['simulation', 'session'],
            'role-galaxy' => ['simulation', 'session'],
            'way-ai-coach' => ['chat', 'lecture'],
            'study-space' => ['session', 'practice'],
        ];

        foreach ($apps as $app) {
            $slug = $app->slug;
            $mTypes = $moduleTypes[$slug] ?? ['simulation'];
            $sTypes = $sessionTypes[$slug] ?? ['session'];

            foreach ($students as $si => $student) {
                // Assign app to user
                DB::table('application_user')->insertOrIgnore([
                    'application_id' => $app->id, 'user_id' => $student->id,
                    'granted_at' => now()->subDays(rand(10, 60)),
                    'created_at' => now(), 'updated_at' => now(),
                ]);

                // Create 3-6 progress records per student per app
                $progressCount = rand(3, 6);
                for ($p = 0; $p < $progressCount; $p++) {
                    $status = ['not_started', 'in_progress', 'completed'][rand(0, 2)];
                    $score = $status === 'completed' ? rand(40, 100) : ($status === 'in_progress' ? rand(10, 50) : null);
                    $duration = $status !== 'not_started' ? rand(120, 3600) : null;

                    AppUserProgress::firstOrCreate([
                        'user_id' => $student->id,
                        'application_id' => $app->id,
                        'module_type' => $mTypes[array_rand($mTypes)],
                        'module_id' => "mod_{$slug}_{$p}_{$si}",
                    ], [
                        'module_name' => "Module " . ($p + 1),
                        'status' => $status,
                        'score' => $score,
                        'max_score' => 100,
                        'duration_seconds' => $duration,
                        'attempts' => rand(1, 5),
                        'started_at' => $status !== 'not_started' ? now()->subDays(rand(1, 30)) : null,
                        'completed_at' => $status === 'completed' ? now()->subDays(rand(0, 10)) : null,
                    ]);
                }

                // Create 2-5 sessions per student per app
                $sessionCount = rand(2, 5);
                for ($s = 0; $s < $sessionCount; $s++) {
                    $startedAt = now()->subDays(rand(0, 29))->subHours(rand(0, 12));
                    $durationSec = rand(180, 2400);

                    AppUserSession::create([
                        'user_id' => $student->id,
                        'application_id' => $app->id,
                        'session_type' => $sTypes[array_rand($sTypes)],
                        'external_session_id' => "sess_{$slug}_{$si}_{$s}_" . uniqid(),
                        'session_name' => ucfirst($sTypes[array_rand($sTypes)]) . " Session " . ($s + 1),
                        'started_at' => $startedAt,
                        'ended_at' => $startedAt->copy()->addSeconds($durationSec),
                        'duration_seconds' => $durationSec,
                        'score' => rand(30, 95),
                    ]);
                }
            }
        }

        // ── Second school + licence for multi-school view ──
        $school2 = School::firstOrCreate(
            ['name' => json_encode(['tr' => 'Bilgi Koleji', 'en' => 'Bilgi College'])],
            ['city' => 'İstanbul', 'is_active' => true]
        );

        $admin2 = User::firstOrCreate(
            ['email' => 'admin@bilgikoleji.demo.com'],
            ['name' => 'Hakan', 'surname' => 'Bilgi', 'password' => Hash::make('Test1234!')]
        );
        $admin2->syncRoles([$adminRole]);
        DB::table('school_user')->insertOrIgnore([
            'school_id' => $school2->id, 'user_id' => $admin2->id, 'role' => 'school-admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        License::updateOrCreate(
            ['school_id' => $school2->id],
            ['seat_count' => 200, 'used_seats' => 45, 'starts_at' => '2026-03-01', 'expires_at' => '2027-03-01', 'is_active' => true]
        );

        $this->command->info('✅ Portal demo data seeded: 2 teachers, 8 students, 3 classes, 4 licenses, app progress & sessions for all 5 apps.');
    }
}
