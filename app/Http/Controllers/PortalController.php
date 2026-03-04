<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\School;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * DopiFuture Hub — Figma node-id: 1164-17862
     */
    public function home()
    {
        if (!auth()->check()) {
            return redirect()->route('portal.login');
        }

        // TODO: Reconnect real data after Figma parity is verified
        $avgLoginCount = 52;
        $avgLoginDuration = '2h 4m';

        $students = collect([
            (object)['id'=>1, 'name'=>'Ayşe',   'surname'=>'Yılmaz',   'email'=>'ayse.yilmaz@test.com',   'grade'=>'4', 'last_login'=>'9:20 am',         'total_time'=>'8h 47m',   'total_uses'=>4],
            (object)['id'=>2, 'name'=>'Mehmet',  'surname'=>'Kaya',     'email'=>'mehmet.kaya@test.com',   'grade'=>'4', 'last_login'=>'1:03 pm',         'total_time'=>'2h 6m',    'total_uses'=>4],
            (object)['id'=>3, 'name'=>'Elif',    'surname'=>'Demir',    'email'=>'elif.demir@test.com',    'grade'=>'4', 'last_login'=>'10:47 am',        'total_time'=>'28m',      'total_uses'=>4],
            (object)['id'=>4, 'name'=>'Ahmet',   'surname'=>'Çelik',    'email'=>'ahmet.celik@test.com',   'grade'=>'4', 'last_login'=>'3:15 pm',         'total_time'=>'6h 25m',   'total_uses'=>4],
            (object)['id'=>5, 'name'=>'Zeynep',  'surname'=>'Öztürk',   'email'=>'zeynep.ozturk@test.com', 'grade'=>'4', 'last_login'=>'8:30 am',         'total_time'=>'37m',      'total_uses'=>4],
            (object)['id'=>6, 'name'=>'Ali',     'surname'=>'Şahin',    'email'=>'ali.sahin@test.com',     'grade'=>'4', 'last_login'=>'11:22 am',        'total_time'=>'15m',      'total_uses'=>4],
            (object)['id'=>7, 'name'=>'Fatma',   'surname'=>'Aydın',    'email'=>'fatma.aydin@test.com',   'grade'=>'4', 'last_login'=>'2:45 pm',         'total_time'=>'3h 52m',   'total_uses'=>4],
            (object)['id'=>8, 'name'=>'Mustafa', 'surname'=>'Korkmaz',  'email'=>'mustafa.korkmaz@test.com','grade'=>'4', 'last_login'=>'9:10 am',        'total_time'=>'1h 17m',   'total_uses'=>4],
            (object)['id'=>9, 'name'=>'Büşra',   'surname'=>'Arslan',   'email'=>'busra.arslan@test.com',  'grade'=>'4', 'last_login'=>'4:05 pm',         'total_time'=>'54m',      'total_uses'=>4],
            (object)['id'=>10,'name'=>'Emre',    'surname'=>'Polat',    'email'=>'emre.polat@test.com',    'grade'=>'4', 'last_login'=>'12:30 pm',        'total_time'=>'4h 12m',   'total_uses'=>4],
        ]);

        return view('portal.home', compact('avgLoginCount', 'avgLoginDuration', 'students'));
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
