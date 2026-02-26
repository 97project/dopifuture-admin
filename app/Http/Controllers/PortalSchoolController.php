<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class PortalSchoolController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = School::query()->withCount(['classes', 'users', 'licenses']);

        // Scope: school-admin sees only their schools
        if ($user->hasRole('school-admin') || $user->hasRole('school-principal')) {
            $query->whereIn('id', $user->schools()->pluck('schools.id'));
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%");
            });
        }

        $schools = $query->latest()->paginate(15);
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
        return view('portal.schools.form', ['school' => new School]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_tr' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ]);

        School::create([
            'name' => ['tr' => $data['name_tr'], 'en' => $data['name_en'] ?? $data['name_tr']],
            'country' => $data['country'] ?? null,
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
        $this->authorizeSchool($school);
        return view('portal.schools.form', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $this->authorizeSchool($school);

        $data = $request->validate([
            'name_tr' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $school->update([
            'name' => ['tr' => $data['name_tr'], 'en' => $data['name_en'] ?? $data['name_tr']],
            'country' => $data['country'] ?? $school->country,
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
        $this->authorizeSchool($school);
        $school->delete();
        return redirect()->route('portal.schools.index')
            ->with('success', __('admin.school_deleted'));
    }

    private function authorizeSchool(School $school): void
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin', 'license-manager']))
            return;
        if ($user->schools()->where('schools.id', $school->id)->exists())
            return;
        abort(403);
    }
}
