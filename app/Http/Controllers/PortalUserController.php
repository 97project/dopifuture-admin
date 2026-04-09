<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUserToAppsJob;
use App\Jobs\UpdateUserInAppsJob;
use App\Jobs\RemoveUserFromAppsJob;
use App\Models\License;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PortalUserController extends Controller
{
    public function index(Request $request)
    {
        $currentRole = $request->get('role', 'student');
        $authUser = auth()->user();
        $schoolIds = $authUser?->schools()->pluck('schools.id') ?? collect();

        // Gerçek kullanıcı sorgusu — okul + rol bazlı
        $query = User::query()
            ->whereHas('schools', fn($q) => $q->whereIn('schools.id', $schoolIds));

        if ($currentRole === 'teacher') {
            $query->role('teacher')->with('classes');
        } else {
            $query->role('student')->with('classes.teachers');
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Gerçek lisans istatistikleri — okulun aktif lisansından
        $activeLicense = License::whereIn('school_id', $schoolIds)
            ->where('is_active', true)
            ->orderByDesc('expires_at')
            ->first();

        $totalSeats = License::whereIn('school_id', $schoolIds)
            ->where('is_active', true)
            ->sum('seat_count');

        $usedSeats = User::whereHas('schools', fn($q) => $q->whereIn('schools.id', $schoolIds))
            ->role('student')
            ->count();

        $licenseStats = (object) [
            'totalLicence'    => $totalSeats ?: 0,
            'usedLicence'     => $usedSeats ?: 0,
            'licenceDuration' => $activeLicense?->expires_at
                ? $activeLicense->expires_at->format('m/d/Y')
                : '-',
        ];

        $roles = $this->getAllowedRoles();

        // Real tab counts
        $studentCount = User::whereHas('schools', fn($q) => $q->whereIn('schools.id', $schoolIds))
            ->role('student')->count();
        $teacherCount = User::whereHas('schools', fn($q) => $q->whereIn('schools.id', $schoolIds))
            ->role('teacher')->count();

        return view('portal.users.index', compact('users', 'roles', 'licenseStats', 'studentCount', 'teacherCount'));
    }

    public function show(User $user)
    {
        $authUser = auth()->user();

        // Student can only see themselves
        if ($authUser->hasRole('student') && $authUser->id !== $user->id) {
            abort(403);
        }

        // Teacher can only see their own students
        if ($authUser->hasRole('teacher')) {
            $classIds = $authUser->classes()->pluck('school_classes.id');
            $studentIds = \DB::table('class_user')->whereIn('class_id', $classIds)->pluck('user_id');
            if (!$studentIds->contains($user->id) && $authUser->id !== $user->id) {
                abort(403);
            }
        }

        $user->load(['roles', 'schools', 'classes.school', 'applications']);

        // Load per-app report data
        $reportService = app(\App\Services\ReportService::class);
        $reportData = $reportService->getStudentReport($user);

        return view('portal.users.show', compact('user', 'reportData'));
    }

    /**
     * Return user details as JSON for AJAX modals.
     */
    public function userJson(User $user)
    {
        $this->guardManageRoles();

        return response()->json([
            'id'      => $user->id,
            'name'    => $user->name,
            'surname' => $user->surname ?? '',
            'email'   => $user->email,
            'role'    => $user->roles->first()?->name ?? '-',
            'status'  => $user->status ?? 'active',
            'school'  => $user->schools->first()?->name ?? '-',
        ]);
    }

    public function create()
    {
        $this->guardManageRoles();
        $roles = $this->getAllowedRoles();
        $schools = $this->getAvailableSchools();
        $schoolIds = auth()->user()->schools()->pluck('schools.id');
        $classes = \App\Models\SchoolClass::with('school')->whereIn('school_id', $schoolIds)->where('is_active', true)->orderBy('name')->get();
        return view('portal.users.form', ['editUser' => new User, 'roles' => $roles, 'schools' => $schools, 'classes' => $classes]);
    }

    public function store(Request $request)
    {
        $this->guardManageRoles();

        $data = $request->validate([
            'name' => 'required|string|max:60',
            'surname' => 'nullable|string|max:60',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:student,teacher,school-principal',
        ]);

        // Rol ve okul otomatik atanır
        $role = $data['role'] ?? 'student';
        $schoolId = $request->input('school_id') ?? auth()->user()->schools()->value('schools.id');

        // License seat check for student role
        if ($role === 'student' && $schoolId) {
            $license = License::where('school_id', $schoolId)
                ->where('is_active', true)
                ->first();

            if ($license && $license->used_seats >= $license->seat_count) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => __('admin.seat_limit_reached')]);
            }
        }

        // Call Vega API FIRST to generate Master ID
        $dummyUser = new User([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email' => $data['email'],
        ]);
        
        $vegaResult = app(\App\Connectors\VegaConnector::class)->syncUser($dummyUser, $data['password']);
        if (!$vegaResult['success']) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Vega API ile bağlantı kurulamadı veya kullanıcı oluşturulamadı: ' . $vegaResult['error']]);
        }

        $vegaId = $vegaResult['response']['id'] ?? $vegaResult['response']['user']['id'] ?? null;
        if (!$vegaId) {
            return back()->withInput()->withErrors(['email' => 'Vega sisteminden Master ID alınamadı.']);
        }

        $newUser = User::create([
            'id' => $vegaId,
            'name' => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'locale' => 'tr',
            'timezone' => 'Europe/Istanbul',
            'email_verified_at' => now(),
        ]);

        $newUser->assignRole($role);

        // Attach to school (otomatik)
        if ($schoolId) {
            \DB::table('school_user')->insertOrIgnore([
                'school_id' => $schoolId,
                'user_id' => $newUser->id,
                'role' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Increment used_seats if student
            if ($role === 'student') {
                License::where('school_id', $schoolId)
                    ->where('is_active', true)
                    ->increment('used_seats');
            }
        }

        // Sınıfa ata (opsiyonel)
        if ($data['role'] === 'student' && $request->filled('class_id')) {
            $classId = $request->input('class_id');
            \DB::table('class_user')->insertOrIgnore([
                'class_id' => $classId,
                'user_id' => $newUser->id,
                'role' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Auto-sync to connector applications (MissionWay, WayStartup limits etc.)
        // Vega is already synced above!
        SyncUserToAppsJob::dispatch($newUser, $data['password']);

        // Hoş geldin e-postası gönder
        try {
            \Illuminate\Support\Facades\Mail::to($newUser->email)
                ->send(new \App\Mail\WelcomeCredentials($newUser, $data['password']));
        } catch (\Throwable $e) {
            \Log::channel('daily')->error('[PortalUser] Hoş geldin maili gönderilemedi', [
                'user_id' => $newUser->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_created'));
    }

    public function edit(User $user)
    {
        $this->guardManageRoles();
        $this->guardPrincipalCannotEditAdmin($user);

        $roles = $this->getAllowedRoles();
        $schools = $this->getAvailableSchools();
        return view('portal.users.form', ['editUser' => $user, 'roles' => $roles, 'schools' => $schools]);
    }

    public function update(Request $request, User $user)
    {
        $this->guardManageRoles();
        $this->guardPrincipalCannotEditAdmin($user);

        $data = $request->validate([
            'name' => 'required|string|max:60',
            'surname' => 'nullable|string|max:60',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'status' => 'nullable|in:active,inactive',
        ]);

        $user->update([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? $user->surname,
            'email' => $data['email'],
            'status' => $data['status'] ?? $user->status,
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        // Rol değiştirme yok — mevcut rol korunur

        // Sync updated user to external applications
        UpdateUserInAppsJob::dispatch($user);

        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_updated'));
    }

    public function destroy(User $user)
    {
        $this->guardManageRoles();
        $this->guardPrincipalCannotEditAdmin($user);

        // Remove user from external applications BEFORE deleting locally
        RemoveUserFromAppsJob::dispatchSync($user);

        // Öğrenci siliniyorsa used_seats azalt
        if ($user->hasRole('student')) {
            $schoolIds = $user->schools()->pluck('schools.id');
            if ($schoolIds->isNotEmpty()) {
                License::whereIn('school_id', $schoolIds)
                    ->where('is_active', true)
                    ->where('used_seats', '>', 0)
                    ->decrement('used_seats');
            }
        }

        $user->delete();
        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_deleted'));
    }

    public function resetPassword(User $user)
    {
        $this->guardManageRoles();
        $this->guardPrincipalCannotEditAdmin($user);

        $newPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'), 0, 8);
        $user->update(['password' => Hash::make($newPassword)]);

        // Sync password change to external applications
        UpdateUserInAppsJob::dispatch($user);

        // Send new password to user via email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\WelcomeCredentials($user, $newPassword));
        } catch (\Throwable $e) {
            \Log::channel('daily')->error('[PortalUser] Password reset mail failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Password reset for ' . $user->name . '. New password: ' . $newPassword);
    }

    /* -- Guards -------------------------------------------------- */

    /**
     * Only school-admin and school-principal can manage users.
     * Teacher and student -> 403.
     */
    private function guardManageRoles(): void
    {
        if (!auth()->user()->hasAnyRole(['school-admin', 'school-principal'])) {
            abort(403);
        }
    }

    /**
     * Principal cannot edit/delete school-admin users.
     */
    private function guardPrincipalCannotEditAdmin(User $target): void
    {
        $user = auth()->user();
        if ($user->hasRole('school-principal') && $target->hasRole('school-admin')) {
            abort(403, __('auth.insufficient_permissions'));
        }
    }

    /* -- Helpers -------------------------------------------------- */

    private function getAllowedRoles(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if ($user->hasRole('school-admin')) {
            return collect(['teacher', 'student', 'school-principal']);
        }
        if ($user->hasRole('school-principal')) {
            return collect(['teacher', 'student']);
        }
        return collect([]);
    }

    private function getAvailableSchools()
    {
        return auth()->user()->schools()->get();
    }

    /* ─── 5.1 Toplu Öğrenci Import (CSV) ─────────────────── */

    public function importForm()
    {
        $this->guardManageRoles();
        $schools = $this->getAvailableSchools();
        return view('portal.users.import', compact('schools'));
    }

    public function import(Request $request)
    {
        $this->guardManageRoles();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'school_id' => 'required|exists:schools,id',
        ]);

        $schoolId = $request->input('school_id');
        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_map('strtolower', array_map('trim', array_shift($rows)));

        $nameIdx    = array_search('name', $header) ?? array_search('ad', $header);
        $surnameIdx = array_search('surname', $header) ?? array_search('soyad', $header);
        $emailIdx   = array_search('email', $header) ?? array_search('e-posta', $header);

        $passIdx = array_search('password', $header);
        if ($passIdx === false) $passIdx = array_search('şifre', $header);
        if ($passIdx === false) $passIdx = array_search('sifre', $header);

        if ($nameIdx === false || $emailIdx === false) {
            return back()->withErrors(['csv_file' => 'CSV dosyasında name/ad ve email/e-posta sütunları gereklidir.'])->withInput();
        }

        // Lisans kontrolü
        $license = License::where('school_id', $schoolId)->where('is_active', true)->first();
        $availableSeats = $license ? $license->availableSeats() : 0;

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $idx => $row) {
            if (empty(array_filter($row))) continue; // boş satır

            $name    = trim($row[$nameIdx] ?? '');
            $surname = $surnameIdx !== false ? trim($row[$surnameIdx] ?? '') : '';
            $email   = trim($row[$emailIdx] ?? '');
            $plainPassword = ($passIdx !== false && isset($row[$passIdx])) ? trim($row[$passIdx]) : null;

            if (!$name || !$email) {
                $errors[] = "Satır " . ($idx + 2) . ": Ad veya e-posta eksik.";
                $skipped++;
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Satır " . ($idx + 2) . ": Geçersiz e-posta: $email";
                $skipped++;
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $errors[] = "Satır " . ($idx + 2) . ": Zaten kayıtlı: $email";
                $skipped++;
                continue;
            }

            // Koltuk kontrolü
            if ($license && $availableSeats <= 0) {
                $errors[] = "Satır " . ($idx + 2) . ": Lisans kotası doldu.";
                $skipped++;
                continue;
            }

            // Şifre: CSV'den geldiyse onu kullan, yoksa rastgele üret
            $passwordToStore = $plainPassword ?: ('Dopi' . rand(1000, 9999) . '!');

            // 1. Vega'da kullanıcı oluştur ve ID al
            $dummyUser = new User([
                'name'    => $name,
                'surname' => $surname,
                'email'   => $email,
            ]);
            
            $vegaResult = app(\App\Connectors\VegaConnector::class)->syncUser($dummyUser, $passwordToStore);
            if (!$vegaResult['success']) {
                $errors[] = "Satır " . ($idx + 2) . ": Vega API Hatası: " . ($vegaResult['error'] ?? 'Bilinmeyen Hata');
                $skipped++;
                continue;
            }

            $vegaId = $vegaResult['response']['id'] ?? $vegaResult['response']['user']['id'] ?? null;
            if (!$vegaId) {
                $errors[] = "Satır " . ($idx + 2) . ": Vega'dan geçerli bir ID alınamadı.";
                $skipped++;
                continue;
            }

            $newUser = User::create([
                'id'       => $vegaId,
                'name'     => $name,
                'surname'  => $surname,
                'email'    => $email,
                'password' => bcrypt($passwordToStore),
                'status'   => 'active',
            ]);

            $newUser->assignRole('student');
            $newUser->schools()->syncWithoutDetaching([$schoolId]);

            if ($license) {
                $license->increment('used_seats');
                $availableSeats--;
            }

            \App\Jobs\SyncUserToAppsJob::dispatch($newUser, $passwordToStore);

            // Hoş geldin e-postası
            try {
                \Illuminate\Support\Facades\Mail::to($newUser->email)
                    ->send(new \App\Mail\WelcomeCredentials($newUser, $passwordToStore));
            } catch (\Throwable $e) {
                // Mail hatası import'u durdurmamalı
            }

            $created++;
        }

        $isTr = app()->getLocale() === 'tr';
        $msg = $isTr
            ? "{$created} öğrenci oluşturuldu, {$skipped} satır atlandı."
            : "{$created} students created, {$skipped} rows skipped.";

        return redirect()->route('portal.users.index')
            ->with('success', $msg)
            ->with('import_errors', $errors);
    }
}
