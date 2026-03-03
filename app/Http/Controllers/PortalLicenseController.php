<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\LicensePurchase;
use App\Models\School;
use Illuminate\Http\Request;

class PortalLicenseController extends Controller
{
    // Teacher/student access is blocked by PortalRole middleware in routes.
    // guardLicenseAdmin() handles mutation guard for school-principal.

    /**
     * Guard: only school-admin can mutate licenses.
     * school-principal → read-only (index, show).
     */
    private function guardLicenseAdmin(): void
    {
        if (auth()->user()->hasRole('school-principal')) {
            abort(403, __('auth.insufficient_permissions'));
        }
    }

    public function index(Request $request)
    {
        // TODO: Reconnect real data after Figma parity is verified
        // Mock data matching Figma frame 1117-25324
        $mockItems = collect([
            (object)['id'=>1, 'school_name'=>'Özel Doğa Koleji',       'city'=>'İstanbul/Kadıköy',       'total_licenses'=>4, 'status'=>'active',      'purchase_date'=>'01/01/2026', 'license_duration'=>'12/31/2026', 'email'=>'admin@dogakoleji.com'],
            (object)['id'=>2, 'school_name'=>'Özel Bilfen Koleji',     'city'=>'İstanbul/Bakırköy',      'total_licenses'=>3, 'status'=>'not_started',  'purchase_date'=>'03/01/2026', 'license_duration'=>'02/28/2027', 'email'=>'info@bilfen.edu.tr'],
            (object)['id'=>3, 'school_name'=>'TED Ankara Koleji',      'city'=>'Ankara/Çankaya',         'total_licenses'=>4, 'status'=>'active',      'purchase_date'=>'01/01/2026', 'license_duration'=>'12/31/2026', 'email'=>'lisans@tedankara.k12.tr'],
            (object)['id'=>4, 'school_name'=>'Özel Enka Okulları',     'city'=>'İstanbul/Sarıyer',       'total_licenses'=>5, 'status'=>'cancelled',   'purchase_date'=>'01/01/2026', 'license_duration'=>'12/31/2026', 'email'=>'license@enka.k12.tr'],
            (object)['id'=>5, 'school_name'=>'Özel Darüşşafaka Lisesi','city'=>'İstanbul/Maslak',        'total_licenses'=>2, 'status'=>'active',      'purchase_date'=>'03/16/2026', 'license_duration'=>'12/31/2026', 'email'=>'bilgi@darussafaka.org'],
            (object)['id'=>6, 'school_name'=>'Özel Koç Okulu',         'city'=>'İstanbul/Tuzla',         'total_licenses'=>5, 'status'=>'active',      'purchase_date'=>'01/01/2026', 'license_duration'=>'12/31/2026', 'email'=>'admin@kocschool.k12.tr'],
            (object)['id'=>7, 'school_name'=>'Özel FMV Işık Okulları', 'city'=>'İstanbul/Nişantaşı',     'total_licenses'=>4, 'status'=>'not_started',  'purchase_date'=>'04/01/2026', 'license_duration'=>'03/31/2027', 'email'=>'isik@fmvisik.k12.tr'],
            (object)['id'=>8, 'school_name'=>'Özel Hisar Okulları',    'city'=>'İstanbul/Göktürk',       'total_licenses'=>1, 'status'=>'cancelled',   'purchase_date'=>'01/01/2026', 'license_duration'=>'12/31/2026', 'email'=>'lisans@hisarschool.k12.tr'],
            (object)['id'=>9, 'school_name'=>'Özel SEV Amerikan Lisesi','city'=>'İstanbul/Üsküdar',      'total_licenses'=>5, 'status'=>'active',      'purchase_date'=>'01/01/2026', 'license_duration'=>'12/31/2026', 'email'=>'sev@sev.org.tr'],
            (object)['id'=>10,'school_name'=>'Özel Irmak Okulları',    'city'=>'İstanbul/Çekmeköy',      'total_licenses'=>4, 'status'=>'expired',     'purchase_date'=>'01/01/2025', 'license_duration'=>'12/31/2025', 'email'=>'info@irmak.k12.tr'],
            (object)['id'=>11,'school_name'=>'Özel Bahçeşehir Koleji', 'city'=>'İstanbul/Bahçeşehir',    'total_licenses'=>4, 'status'=>'active',      'purchase_date'=>'01/01/2026', 'license_duration'=>'12/31/2026', 'email'=>'lisans@bahcesehir.k12.tr'],
        ]);

        // Simulate pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $licenses = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockItems->forPage($page, $perPage),
            120, // total (Figma shows "Page1of 12")
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return view('portal.licenses.index', compact('licenses'));
    }

    public function show(License $license)
    {
        $this->authorizeSchool($license->school_id);
        $license->load(['school', 'purchases' => fn($q) => $q->latest('purchased_at')]);
        return view('portal.licenses.show', compact('license'));
    }

    public function addPurchase(Request $request, License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);

        $data = $request->validate([
            'seat_count' => 'required|integer|min:1',
            'amount' => 'nullable|numeric|min:0',
            'purchased_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $license->purchases()->create($data);

        return redirect()->route('portal.licenses.show', $license)
            ->with('success', __('admin.purchase_added'));
    }

    public function create()
    {
        $this->guardLicenseAdmin();
        $schools = $this->getAvailableSchools();
        return view('portal.licenses.form', ['license' => new License, 'schools' => $schools]);
    }

    public function store(Request $request)
    {
        $this->guardLicenseAdmin();
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'seat_count' => 'required|integer|min:1',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->authorizeSchool($data['school_id']);

        // Single license per school — prevent duplicates
        if (License::where('school_id', $data['school_id'])->exists()) {
            $msg = __('admin.license_already_exists');
            return back()->withErrors(['school_id' => $msg])->withInput();
        }

        License::create([
            'school_id' => $data['school_id'],
            'user_id' => auth()->id(),
            'seat_count' => $data['seat_count'],
            'used_seats' => 0,
            'starts_at' => $data['starts_at'],
            'expires_at' => $data['expires_at'],
            'is_active' => true,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('portal.licenses.index')
            ->with('success', __('admin.license_created'));
    }

    public function edit(License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);
        $schools = $this->getAvailableSchools();
        return view('portal.licenses.form', compact('license', 'schools'));
    }

    public function update(Request $request, License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);

        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'seat_count' => 'required|integer|min:1',
            'used_seats' => 'nullable|integer|min:0',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $license->update([
            'school_id' => $data['school_id'],
            'seat_count' => $data['seat_count'],
            'used_seats' => $data['used_seats'] ?? $license->used_seats,
            'starts_at' => $data['starts_at'],
            'expires_at' => $data['expires_at'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $data['notes'] ?? $license->notes,
        ]);

        return redirect()->route('portal.licenses.index')
            ->with('success', __('admin.license_updated'));
    }

    public function destroy(License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);
        $license->delete();
        return redirect()->route('portal.licenses.index')
            ->with('success', __('admin.license_deleted'));
    }

    private function getAvailableSchools()
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin', 'license-manager'])) {
            return School::active()->get();
        }
        return $user->schools()->get();
    }

    private function authorizeSchool(int $schoolId): void
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin', 'license-manager']))
            return;
        if ($user->schools()->where('schools.id', $schoolId)->exists())
            return;
        abort(403);
    }
}
