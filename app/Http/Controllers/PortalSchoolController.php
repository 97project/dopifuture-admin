<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Country;
use Illuminate\Http\Request;

class PortalSchoolController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();
        $schoolIds = $authUser?->schools()->pluck('schools.id') ?? collect();

        $query = School::withCount(['classes', 'users', 'licenses'])
            ->whereIn('id', $schoolIds);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $schools = $query->orderBy('name')->paginate(15)->withQueryString();

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
