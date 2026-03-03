<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RegistrationRequest;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Show the public school registration form.
     */
    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('portal.register', compact('countries'));
    }

    /**
     * Handle form submission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:200',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'student_count' => 'nullable|integer|min:1|max:100000',
            'contact_name' => 'required|string|max:100',
            'contact_surname' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:2000',
        ]);

        RegistrationRequest::create([
            'school_name' => $request->input('school_name'),
            'country' => $request->input('country'),
            'state' => $request->input('state'),
            'city' => $request->input('city'),
            'student_count' => $request->input('student_count'),
            'contact_name' => $request->input('contact_name'),
            'contact_surname' => $request->input('contact_surname'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'notes' => $request->input('notes'),
            'status' => 'new',
        ]);

        return redirect()->route('register.create')
            ->with('success', __('admin.request_submitted'));
    }
}
