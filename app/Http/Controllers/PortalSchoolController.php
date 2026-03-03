<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Country;
use Illuminate\Http\Request;

class PortalSchoolController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Reconnect real data after Figma visual verification
        $mockSchools = collect([
            (object)['id'=>1, 'name'=>'Bahçeşehir Koleji',       'city'=>'İstanbul',  'classes_count'=>24, 'users_count'=>580, 'licenses_count'=>500, 'is_active'=>true],
            (object)['id'=>2, 'name'=>'TED Ankara Koleji',       'city'=>'Ankara',    'classes_count'=>18, 'users_count'=>420, 'licenses_count'=>350, 'is_active'=>true],
            (object)['id'=>3, 'name'=>'Özel Doğa Koleji',        'city'=>'İstanbul',  'classes_count'=>22, 'users_count'=>510, 'licenses_count'=>420, 'is_active'=>true],
            (object)['id'=>4, 'name'=>'Özel Enka Okulları',      'city'=>'İstanbul',  'classes_count'=>14, 'users_count'=>320, 'licenses_count'=>280, 'is_active'=>true],
            (object)['id'=>5, 'name'=>'Özel FMV Işık Okulları',  'city'=>'İstanbul',  'classes_count'=>16, 'users_count'=>380, 'licenses_count'=>310, 'is_active'=>true],
            (object)['id'=>6, 'name'=>'Özel Darüşşafaka Lisesi', 'city'=>'İstanbul',  'classes_count'=>10, 'users_count'=>240, 'licenses_count'=>200, 'is_active'=>true],
            (object)['id'=>7, 'name'=>'Özel Bilfen Koleji',      'city'=>'İstanbul',  'classes_count'=>20, 'users_count'=>460, 'licenses_count'=>380, 'is_active'=>false],
            (object)['id'=>8, 'name'=>'Özel Koç Okulu',          'city'=>'İstanbul',  'classes_count'=>12, 'users_count'=>290, 'licenses_count'=>250, 'is_active'=>true],
        ]);

        $page = $request->get('page', 1);
        $schools = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockSchools->forPage($page, 15),
            $mockSchools->count(),
            15,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('portal.schools.index', compact('schools'));
    }

    public function show(School $school)
    {
        $this->authorizeSchool($school);
        $school->loadCount(['classes', 'users', 'licenses']);
        $school->load([
            'classes' => fn($q) => $q->withCount('students')->orderBy('name'),
            'users' => fn($q) => $q->select('users.id', 'users.name', 'users.surname', 'users.email', 'users.status'),
            'licenses',
        ]);
        return view('portal.schools.show', compact('school'));
    }

    public function create()
    {
        $this->guardSchoolAdmin();
        $countries = Country::orderBy('name')->get(['id', 'name']);
        return view('portal.schools.form', ['school' => new School, 'countries' => $countries]);
    }

    public function store(Request $request)
    {
        $this->guardSchoolAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:150',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ]);

        School::create([
            'name' => $data['name'],
            'country' => $data['country'] ?? null,
            'state' => $data['state'] ?? null,
            'city' => $data['city'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'website' => $data['website'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('portal.schools.index')
            ->with('success', __('admin.school_created'));
    }

    public function edit(School $school)
    {
        $this->guardSchoolAdmin();
        $this->authorizeSchool($school);
        $countries = Country::orderBy('name')->get(['id', 'name']);
        return view('portal.schools.form', compact('school', 'countries'));
    }

    public function update(Request $request, School $school)
    {
        $this->guardSchoolAdmin();
        $this->authorizeSchool($school);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:150',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $school->update([
            'name' => $data['name'],
            'country' => $data['country'] ?? $school->country,
            'state' => $data['state'] ?? $school->state,
            'city' => $data['city'] ?? $school->city,
            'phone' => $data['phone'] ?? $school->phone,
            'email' => $data['email'] ?? $school->email,
            'address' => $data['address'] ?? $school->address,
            'website' => $data['website'] ?? $school->website,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('portal.schools.index')
            ->with('success', __('admin.school_updated'));
    }

    public function destroy(School $school)
    {
        $this->guardSchoolAdmin();
        $this->authorizeSchool($school);
        $school->delete();
        return redirect()->route('portal.schools.index')
            ->with('success', __('admin.school_deleted'));
    }

    private function authorizeSchool(School $school): void
    {
        $user = auth()->user();
        if ($user->schools()->where('schools.id', $school->id)->exists())
            return;
        abort(403);
    }

    /**
     * Only school-admin can create/edit/delete schools.
     * Principal, teacher, student → 403.
     */
    private function guardSchoolAdmin(): void
    {
        if (!auth()->user()->hasRole('school-admin')) {
            abort(403, __('auth.insufficient_permissions'));
        }
    }
}
