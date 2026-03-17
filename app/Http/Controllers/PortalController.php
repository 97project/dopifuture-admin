<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\School;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Public landing page (tanıtım sayfası).
     * Auth users are redirected to their dashboard.
     */
    public function home()
    {
        if (auth()->check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.home', [
            'appCount' => \App\Models\Application::count(),
            'schoolCount' => \App\Models\School::count(),
            'students' => collect(),
            'avgLoginCount' => 0,
            'avgLoginDuration' => '0m',
        ]);
    }

    /**
     * Solutions / Applications listing.
     */
    public function solutions()
    {
        $applications = Application::active()->ordered()->get();

        return view('portal.solutions', compact('applications'));
    }

    /**
     * Contact page with form.
     */
    public function contact()
    {
        return view('portal.contact');
    }

    /**
     * Handle contact form submission.
     */
    public function contactStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:3000',
        ]);

        // Store as activity log for admin review
        \App\Models\ActivityLog::log('contact_form_submitted', 'portal', null, [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
        ]);

        return redirect()->route('portal.contact')
            ->with('success', __('admin.contact_sent'));
    }

    /**
     * Switch portal locale — saves to session and (if authenticated) to user DB.
     */
    public function switchLocale(Request $request)
    {
        $locale = $request->input('locale', 'tr');

        if (!in_array($locale, ['tr', 'en'])) {
            $locale = 'tr';
        }

        // Save to session (for guests and auth users alike)
        session(['locale' => $locale]);
        app()->setLocale($locale);

        // If user is logged in, persist preference to DB
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        return back();
    }
}
