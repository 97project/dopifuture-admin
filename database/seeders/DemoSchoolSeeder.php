<?php

namespace Database\Seeders;

use App\Models\License;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a fully populated demo school with complete hierarchy.
 *
 * Hierarchy:
 *   School → License
 *     ├── School Admin
 *     ├── School Principal
 *     ├── Class 9-A
 *     │     ├── Teacher (Matematik)
 *     │     ├── Student × 3
 *     ├── Class 9-B
 *     │     ├── Teacher (Fen Bilimleri)
 *     │     ├── Student × 3
 *     ├── Class 10-A
 *     │     ├── Teacher (shared from 9-A)
 *     │     ├── Student × 2
 */
class DemoSchoolSeeder extends Seeder
{
    private const PASSWORD = 'Demo2026!';

    public function run(): void
    {
        // Clean up previous demo data — use DB::table to bypass global scopes
        $userIds = DB::table('users')->where('email', 'like', '%@demo.tr')->pluck('id');
        if ($userIds->count()) {
            DB::table('class_user')->whereIn('user_id', $userIds)->delete();
            DB::table('school_user')->whereIn('user_id', $userIds)->delete();
            DB::table('model_has_roles')->whereIn('model_id', $userIds)->where('model_type', 'App\\Models\\User')->delete();
            DB::table('users')->whereIn('id', $userIds)->delete();
        }
        $existingSchool = School::where('email', 'info@ataturklisesi.demo.tr')->first();
        if ($existingSchool) {
            SchoolClass::where('school_id', $existingSchool->id)->delete();
            License::where('school_id', $existingSchool->id)->delete();
            $existingSchool->delete();
        }
        if ($userIds->count() || $existingSchool) {
            $this->command->warn("Previous demo data cleaned ({$userIds->count()} users)");
        }

        $school = School::create([
            'name'      => 'Atatürk Anadolu Lisesi',
            'country'   => 'Türkiye',
            'state'     => 'İstanbul',
            'city'      => 'Beşiktaş',
            'phone'     => '+90 212 555 0100',
            'email'     => 'info@ataturklisesi.demo.tr',
            'address'   => 'Beşiktaş Mahallesi, Eğitim Caddesi No:42, Beşiktaş/İstanbul',
            'website'   => 'https://ataturklisesi.demo.tr',
            'is_active' => true,
        ]);
        $this->command->info("✅ School: {$school->name} (ID: {$school->id})");

        // ── License (30 seats) ────────────────────────
        $license = License::create([
            'school_id'  => $school->id,
            'user_id'    => 1, // Super Admin
            'seat_count' => 30,
            'used_seats' => 0,
            'starts_at'  => now(),
            'expires_at' => now()->addYear(),
            'is_active'  => true,
            'notes'      => 'Demo lisans — 30 öğrenci kontenjanı',
        ]);
        $this->command->info("✅ License: {$license->seat_count} seats");

        // ── School Admin ──────────────────────────────
        $admin = $this->createUser(
            'Ayşe', 'Yılmaz', 'ayse.yilmaz@demo.tr', 'school-admin', $school
        );

        // ── School Principal ──────────────────────────
        $principal = $this->createUser(
            'Mehmet', 'Kaya', 'mehmet.kaya@demo.tr', 'school-principal', $school
        );

        // ── Teachers ──────────────────────────────────
        $teacherMat = $this->createUser(
            'Fatma', 'Demir', 'fatma.demir@demo.tr', 'teacher', $school
        );
        $teacherFen = $this->createUser(
            'Ali', 'Çelik', 'ali.celik@demo.tr', 'teacher', $school
        );

        // ── Classes ───────────────────────────────────
        $class9A = SchoolClass::create([
            'school_id'     => $school->id,
            'name'          => '9-A',
            'grade_level'   => '9',
            'academic_year' => '2025-2026',
            'is_active'     => true,
        ]);

        $class9B = SchoolClass::create([
            'school_id'     => $school->id,
            'name'          => '9-B',
            'grade_level'   => '9',
            'academic_year' => '2025-2026',
            'is_active'     => true,
        ]);

        $class10A = SchoolClass::create([
            'school_id'     => $school->id,
            'name'          => '10-A',
            'grade_level'   => '10',
            'academic_year' => '2025-2026',
            'is_active'     => true,
        ]);

        $this->command->info("✅ Classes: 9-A, 9-B, 10-A");

        // ── Assign Teachers to Classes ────────────────
        // class_user pivot with role column
        DB::table('class_user')->insert([
            ['class_id' => $class9A->id,  'user_id' => $teacherMat->id, 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => $class9B->id,  'user_id' => $teacherFen->id, 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => $class10A->id, 'user_id' => $teacherMat->id, 'role' => 'teacher', 'created_at' => now(), 'updated_at' => now()], // shared
        ]);
        $this->command->info("✅ Teachers assigned (Fatma→9A+10A, Ali→9B)");

        // ── Students ──────────────────────────────────
        $students9A = [
            $this->createStudent('Zeynep', 'Arslan',   'zeynep.arslan@demo.tr',   $school),
            $this->createStudent('Emre',   'Şahin',    'emre.sahin@demo.tr',      $school),
            $this->createStudent('Elif',   'Öztürk',   'elif.ozturk@demo.tr',     $school),
        ];

        $students9B = [
            $this->createStudent('Burak',  'Aydın',    'burak.aydin@demo.tr',     $school),
            $this->createStudent('Selin',  'Korkmaz',  'selin.korkmaz@demo.tr',   $school),
            $this->createStudent('Cem',    'Yıldırım', 'cem.yildirim@demo.tr',    $school),
        ];

        $students10A = [
            $this->createStudent('Deniz',  'Kılıç',    'deniz.kilic@demo.tr',     $school),
            $this->createStudent('Aslı',   'Koç',      'asli.koc@demo.tr',        $school),
        ];

        // ── Assign Students to Classes ────────────────
        foreach ($students9A as $s)  { DB::table('class_user')->insert(['class_id' => $class9A->id, 'user_id' => $s->id, 'role' => 'student', 'created_at' => now(), 'updated_at' => now()]); }
        foreach ($students9B as $s)  { DB::table('class_user')->insert(['class_id' => $class9B->id, 'user_id' => $s->id, 'role' => 'student', 'created_at' => now(), 'updated_at' => now()]); }
        foreach ($students10A as $s) { DB::table('class_user')->insert(['class_id' => $class10A->id, 'user_id' => $s->id, 'role' => 'student', 'created_at' => now(), 'updated_at' => now()]); }

        $this->command->info("✅ Students: 8 students assigned to 3 classes");

        // Update license used_seats
        $license->update(['used_seats' => 8]);

        $this->command->newLine();
        $this->command->info("╔══════════════════════════════════════════════════════════╗");
        $this->command->info("║  DEMO OKUL HESABI OLUŞTURULDU                            ║");
        $this->command->info("╠══════════════════════════════════════════════════════════╣");
        $this->command->info("║  Tüm şifre: " . self::PASSWORD . "                              ║");
        $this->command->info("╠══════════════════════════════════════════════════════════╣");
        $this->command->info("║  Okul Yöneticisi : ayse.yilmaz@demo.tr                   ║");
        $this->command->info("║  Müdür Yardımcısı: mehmet.kaya@demo.tr                   ║");
        $this->command->info("║  Öğretmen (Mat)  : fatma.demir@demo.tr                   ║");
        $this->command->info("║  Öğretmen (Fen)  : ali.celik@demo.tr                     ║");
        $this->command->info("║  Öğrenci (9-A)   : zeynep.arslan / emre.sahin / elif     ║");
        $this->command->info("║  Öğrenci (9-B)   : burak.aydin / selin / cem             ║");
        $this->command->info("║  Öğrenci (10-A)  : deniz.kilic / asli.koc                ║");
        $this->command->info("╚══════════════════════════════════════════════════════════╝");
    }

    private function createUser(string $name, string $surname, string $email, string $role, School $school): User
    {
        $user = User::create([
            'name'              => $name,
            'surname'           => $surname,
            'email'             => $email,
            'password'          => Hash::make(self::PASSWORD),
            'status'            => 'active',
            'locale'            => 'tr',
            'timezone'          => 'Europe/Istanbul',
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        DB::table('school_user')->insert([
            'school_id'  => $school->id,
            'user_id'    => $user->id,
            'role'       => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("  👤 {$role}: {$name} {$surname} ({$email})");
        return $user;
    }

    private function createStudent(string $name, string $surname, string $email, School $school): User
    {
        return $this->createUser($name, $surname, $email, 'student', $school);
    }
}
