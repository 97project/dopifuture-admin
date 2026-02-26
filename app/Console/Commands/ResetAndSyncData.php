<?php

namespace App\Console\Commands;

use App\Connectors\VegaConnector;
use App\Models\Application;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ResetAndSyncData extends Command
{
    protected $signature = 'data:reset-and-sync';
    protected $description = 'Super/Admin hariç kullanıcıları temizle, her connector\'dan kendi kullanıcılarını çek, uygulamalara ata';

    private array $protectedRoles = ['super-admin', 'admin'];

    private array $testClasses = [
        ['name' => '9-A Sınıfı', 'grade_level' => '9', 'academic_year' => '2025-2026'],
        ['name' => '9-B Sınıfı', 'grade_level' => '9', 'academic_year' => '2025-2026'],
        ['name' => '10-A Sınıfı', 'grade_level' => '10', 'academic_year' => '2025-2026'],
        ['name' => '10-B Sınıfı', 'grade_level' => '10', 'academic_year' => '2025-2026'],
        ['name' => '11-A Sınıfı', 'grade_level' => '11', 'academic_year' => '2025-2026'],
    ];

    private array $testTeachers = [
        ['name' => 'Ahmet', 'surname' => 'Yılmaz', 'email' => 'ahmet.yilmaz@dopifuture.com'],
        ['name' => 'Fatma', 'surname' => 'Kaya', 'email' => 'fatma.kaya@dopifuture.com'],
        ['name' => 'Mehmet', 'surname' => 'Demir', 'email' => 'mehmet.demir@dopifuture.com'],
        ['name' => 'Ayşe', 'surname' => 'Çelik', 'email' => 'ayse.celik@dopifuture.com'],
        ['name' => 'Mustafa', 'surname' => 'Şahin', 'email' => 'mustafa.sahin@dopifuture.com'],
    ];

    // Vega session type → uygulama slug eşlemesi
    private array $vegaModuleMap = [
        'lecturer' => 'way-ai-coach',
        'simulator' => 'role-galaxy',
        // study-space için özel session type yok, ayrı kontrol edilecek
    ];

    public function handle(): int
    {
        $this->info('=== VERİ TEMİZLEME VE SENKRON BAŞLIYOR ===');
        $this->newLine();

        // ─── 1. Korunacak kullanıcıları belirle ───
        $protectedIds = User::role($this->protectedRoles)->pluck('id')->toArray();
        $this->info("Korunan kullanıcılar (super-admin/admin): " . implode(', ', $protectedIds));

        // ─── 2. Temizlik ───
        $this->warn('Adım 1: Temizlik...');
        $usersToDelete = User::whereNotIn('id', $protectedIds)->pluck('id')->toArray();
        $this->info("  Silinecek: " . count($usersToDelete) . " kullanıcı");

        DB::table('application_user')->delete();
        DB::table('school_user')->delete();
        DB::table('class_user')->delete();
        DB::table('model_has_roles')->whereIn('model_id', $usersToDelete)
            ->where('model_type', User::class)->delete();
        DB::table('model_has_permissions')->whereIn('model_id', $usersToDelete)
            ->where('model_type', User::class)->delete();

        User::whereNotIn('id', $protectedIds)->forceDelete();
        SchoolClass::query()->delete();
        School::query()->delete();
        $this->info("  ✓ Temizlik tamamlandı");

        // ─── 3. Okul ───
        $this->warn('Adım 2: DopiFuture Test okulu...');
        $school = School::create([
            'name' => json_encode(['tr' => 'DopiFuture Test', 'en' => 'DopiFuture Test']),
            'country' => 'Türkiye',
            'city' => 'İstanbul',
            'address' => 'Test Mahallesi, Test Sokak No:1',
            'email' => 'test@dopifuture.com',
            'is_active' => true,
        ]);
        $this->info("  ✓ Okul #{$school->id}");

        // ─── 4. Sınıflar ───
        $this->warn('Adım 3: Test sınıfları...');
        $classes = collect();
        foreach ($this->testClasses as $classData) {
            $class = SchoolClass::create(array_merge($classData, [
                'school_id' => $school->id,
                'is_active' => true,
            ]));
            $classes->push($class);
        }
        $this->info("  ✓ {$classes->count()} sınıf oluşturuldu");

        // ─── 5. Öğretmenler ───
        $this->warn('Adım 4: Test öğretmenleri...');
        $teachers = collect();
        foreach ($this->testTeachers as $i => $tData) {
            $teacher = User::create([
                'name' => $tData['name'],
                'surname' => $tData['surname'],
                'email' => $tData['email'],
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);
            $teacher->assignRole('teacher');
            $teachers->push($teacher);

            $school->users()->attach($teacher->id, ['role' => 'teacher']);
            $classes[$i % $classes->count()]->users()->attach($teacher->id, ['role' => 'teacher']);
        }
        $this->info("  ✓ {$teachers->count()} öğretmen oluşturuldu ve atandı");

        // ─── 6. CONNECTOR BAZLI KULLANICI KEŞFİ ───
        $this->warn('Adım 5: Connector bazlı kullanıcı keşfi...');
        $this->newLine();

        $apps = Application::active()->where('slug', '!=', 'test-app')->get();
        $allStudents = collect(); // email => User (birleşik havuz)
        $appUserMap = [];         // app_id => [user_ids] — her uygulama kendi kullanıcıları

        foreach ($apps as $app) {
            $appUserMap[$app->id] = [];
        }

        // ══════════════════════════════════════
        // VEGA CONNECTOR — 3 uygulama paylaşır
        // ══════════════════════════════════════
        $vegaApps = $apps->filter(fn($a) => $a->connector_class === VegaConnector::class);

        if ($vegaApps->isNotEmpty()) {
            $this->info('  ┌── VEGA Connector ──────────────────────');
            $this->info('  │ Uygulamalar: ' . $vegaApps->pluck('slug')->implode(', '));
            $vegaConnector = new VegaConnector();

            // 6a. Tüm Vega kullanıcılarını çek
            $allVegaUsers = [];
            $page = 1;
            do {
                $result = $vegaConnector->listRemoteUsers($page, 100);
                if (!$result['success']) {
                    $this->error("  │ ✗ API hatası: " . ($result['error'] ?? '?'));
                    break;
                }
                $allVegaUsers = array_merge($allVegaUsers, $result['data']);
                $lastPage = $result['pagination']['last_page'] ?? 1;
                $this->info("  │ Sayfa {$page}/{$lastPage} — " . count($result['data']) . " kullanıcı");
                $page++;
            } while ($page <= $lastPage);

            $this->info("  │ Toplam: " . count($allVegaUsers) . " Vega kullanıcısı");

            // 6b. Lokal kullanıcıları oluştur & Vega ID eşle
            $vegaIdMap = []; // local_user_id => vega_id

            foreach ($allVegaUsers as $vu) {
                $email = $vu['email'] ?? null;
                if (!$email)
                    continue;
                if (in_array($email, ['admin@dopifuture.com', 'admin@panel26.com']))
                    continue;

                $existing = User::where('email', $email)->first();
                if ($existing && in_array($existing->id, $protectedIds))
                    continue;

                $student = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $vu['name'] ?? 'Öğrenci',
                        'surname' => $vu['surname'] ?? '',
                        'password' => Hash::make('password'),
                        'status' => 'active',
                    ]
                );

                if (!$student->hasRole('student')) {
                    $student->assignRole('student');
                }

                $allStudents->put($email, $student);
                $vegaId = $vu['id'] ?? null;
                if ($vegaId) {
                    $vegaIdMap[$student->id] = $vegaId;
                }
            }

            $this->info("  │ {$allStudents->count()} öğrenci oluşturuldu");

            // 6c. Her kullanıcının Vega oturumlarını kontrol et → hangi modülde aktif?
            $this->info("  │ Oturum verileri kontrol ediliyor...");

            $roleGalaxyApp = $vegaApps->firstWhere('slug', 'role-galaxy');
            $wayAiCoachApp = $vegaApps->firstWhere('slug', 'way-ai-coach');
            $studySpaceApp = $vegaApps->firstWhere('slug', 'study-space');

            $moduleStats = ['role-galaxy' => 0, 'way-ai-coach' => 0, 'study-space' => 0];

            foreach ($allStudents as $email => $student) {
                $vegaId = $vegaIdMap[$student->id] ?? null;
                if (!$vegaId)
                    continue;

                $hasLecturer = false;
                $hasSimulator = false;

                // Lecturer (Way AI Coach) oturumlarını kontrol et
                try {
                    $sessResult = $vegaConnector->getUserSessions($vegaId, 'lecturer');
                    if ($sessResult['success'] && !empty($sessResult['sessions'])) {
                        $hasLecturer = true;
                    }
                } catch (\Throwable $e) {
                    // sessional check başarısız — atla
                }

                // Simulator (Role Galaxy) — şimdilik session list endpoint yok,
                // ama Vega'da kayıtlıysa simulator'a erişimi var kabul et
                // TODO: Simulator session endpoint eklendiğinde burası güncellenmeli
                $hasSimulator = true; // Vega kullanıcısı → simulator erişimi var

                // Uygulamalara ata
                if ($hasLecturer && $wayAiCoachApp) {
                    $appUserMap[$wayAiCoachApp->id][] = $student->id;
                    $moduleStats['way-ai-coach']++;
                }

                if ($hasSimulator && $roleGalaxyApp) {
                    $appUserMap[$roleGalaxyApp->id][] = $student->id;
                    $moduleStats['role-galaxy']++;
                }

                // Study Space: lecturer oturumu olan kullanıcılar study-space'e de erişebilir
                if ($hasLecturer && $studySpaceApp) {
                    $appUserMap[$studySpaceApp->id][] = $student->id;
                    $moduleStats['study-space']++;
                }
            }

            foreach ($moduleStats as $slug => $count) {
                $this->info("  │ → {$slug}: {$count} kullanıcı");
            }
            $this->info('  └──────────────────────────────────────');
        }

        // ══════════════════════════════════════
        // MISSION WAY CONNECTOR — ayrı API
        // ══════════════════════════════════════
        $missionWayApp = $apps->firstWhere('slug', 'mission-way');
        if ($missionWayApp) {
            $this->newLine();
            $this->info('  ┌── MissionWay Connector ────────────────');
            $mwConnector = $missionWayApp->resolveConnector();

            if ($mwConnector) {
                // MissionWay getUser panel ID ile çalışır.
                // Mevcut öğrencileri kontrol et (email eşlemesi yapamıyoruz çünkü
                // MissionWay API'si email ile arama desteklemiyor, sadece userId ile).
                // Ama syncUser ile MissionWay'e push edip var olanları keşfedebiliriz.
                $mwFound = 0;
                $mwCreated = 0;
                $mwFailed = 0;

                foreach ($allStudents as $student) {
                    $result = $mwConnector->syncUser($student);
                    if ($result['success'] ?? false) {
                        $appUserMap[$missionWayApp->id][] = $student->id;
                        // duplicate = zaten vardı, diğeri = yeni oluşturuldu
                        $response = $result['response'] ?? [];
                        if (isset($response['message']) && str_contains(strtolower($response['message'] ?? ''), 'already exists')) {
                            $mwFound++;
                        } else {
                            $mwCreated++;
                        }
                    } else {
                        $mwFailed++;
                    }
                }

                $this->info("  │ Zaten mevcut: {$mwFound}");
                $this->info("  │ Yeni oluşturuldu: {$mwCreated}");
                $this->info("  │ Başarısız: {$mwFailed}");
                $this->info("  │ Toplam atanan: " . count($appUserMap[$missionWayApp->id]));
            } else {
                $this->warn("  │ ⚠ Connector çözümlenemedi");
            }

            $this->info('  └──────────────────────────────────────');
        }

        // ══════════════════════════════════════
        // WAY STARTUP CONNECTOR — ayrı API
        // ══════════════════════════════════════
        $wayStartupApp = $apps->firstWhere('slug', 'way-startup');
        if ($wayStartupApp) {
            $this->newLine();
            $this->info('  ┌── WayStartup Connector ────────────────');
            $wsConnector = $wayStartupApp->resolveConnector();

            if ($wsConnector) {
                $wsFound = 0;
                $wsCreated = 0;
                $wsFailed = 0;

                foreach ($allStudents as $student) {
                    $result = $wsConnector->syncUser($student);
                    if ($result['success'] ?? false) {
                        $appUserMap[$wayStartupApp->id][] = $student->id;
                        $response = $result['response'] ?? [];
                        if (isset($response['message']) && str_contains(strtolower($response['message'] ?? ''), 'already exists')) {
                            $wsFound++;
                        } else {
                            $wsCreated++;
                        }
                    } else {
                        $wsFailed++;
                    }
                }

                $this->info("  │ Zaten mevcut: {$wsFound}");
                $this->info("  │ Yeni oluşturuldu: {$wsCreated}");
                $this->info("  │ Başarısız: {$wsFailed}");
                $this->info("  │ Toplam atanan: " . count($appUserMap[$wayStartupApp->id]));
            } else {
                $this->warn("  │ ⚠ Connector çözümlenemedi");
            }

            $this->info('  └──────────────────────────────────────');
        }

        // ─── 7. application_user pivot kayıtlarını oluştur ───
        $this->newLine();
        $this->warn('Adım 6: Application-User pivot kayıtları...');

        foreach ($apps as $app) {
            $userIds = array_unique($appUserMap[$app->id] ?? []);
            if (empty($userIds)) {
                $this->info("  {$app->slug}: 0 kullanıcı (atlanıyor)");
                continue;
            }

            foreach ($userIds as $uid) {
                DB::table('application_user')->updateOrInsert(
                    ['application_id' => $app->id, 'user_id' => $uid],
                    [
                        'granted_by' => $protectedIds[0] ?? 1,
                        'granted_at' => now(),
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_error' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $this->info("  {$app->slug}: " . count($userIds) . " kullanıcı atandı ✓");
        }

        // ─── 8. Öğrencileri okula ve sınıflara ata ───
        $this->warn('Adım 7: Okul ve sınıf atamaları...');

        if ($allStudents->isEmpty()) {
            $this->error('  Hiçbir connector\'dan öğrenci çekilemedi!');
            return Command::FAILURE;
        }

        $studentIds = $allStudents->pluck('id')->unique()->toArray();

        foreach ($studentIds as $sid) {
            $school->users()->syncWithoutDetaching([$sid => ['role' => 'student']]);
        }
        $this->info("  ✓ " . count($studentIds) . " öğrenci → DopiFuture Test");

        // Sınıflara random dağıt
        $shuffled = collect($studentIds)->shuffle();
        $chunks = $shuffled->split($classes->count());
        foreach ($classes as $i => $class) {
            $cs = $chunks[$i] ?? collect();
            foreach ($cs as $sid) {
                $class->users()->syncWithoutDetaching([$sid => ['role' => 'student']]);
            }
            $this->info("  ✓ {$class->name}: " . $cs->count() . " öğrenci");
        }

        // Admin'leri okula ata
        foreach ($protectedIds as $pid) {
            $school->users()->syncWithoutDetaching([$pid => ['role' => 'school-admin']]);
        }
        $this->info("  ✓ Admin'ler okul admini olarak atandı");

        // ─── ÖZET ───
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════╗');
        $this->info('║            SENKRON TAMAMLANDI                  ║');
        $this->info('╠════════════════════════════════════════════════╣');
        $this->info('║ Okul       : DopiFuture Test                   ║');
        $this->info('║ Sınıflar   : ' . $classes->count() . '                                    ║');
        $this->info('║ Öğretmenler: ' . $teachers->count() . '                                    ║');
        $this->info('║ Öğrenciler : ' . count($studentIds) . '                                   ║');
        $this->info('╠════════════════════════════════════════════════╣');

        foreach ($apps as $app) {
            $count = count(array_unique($appUserMap[$app->id] ?? []));
            $pad = str_repeat(' ', max(0, 34 - strlen($app->slug) - strlen((string) $count)));
            $this->info("║ {$app->slug}: {$count} kullanıcı{$pad}║");
        }

        $this->info('╚════════════════════════════════════════════════╝');

        return Command::SUCCESS;
    }
}
