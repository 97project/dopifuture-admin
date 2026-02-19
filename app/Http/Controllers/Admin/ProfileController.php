<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(private readonly AvatarService $avatarService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $recoveryCodes = session('recovery_codes');

        return view('admin.profile', compact('user', 'recoveryCodes'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'locale' => 'required|in:tr,en',
            'timezone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar_path'] = $this->avatarService->upload($request->file('avatar'), $user);
        }

        unset($validated['avatar']);
        $user->update($validated);

        ActivityLog::log('profile.update', 'users', $user->id, $request);

        return redirect()->route('admin.profile')
            ->with('success', __('admin.updated'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (!Hash::check($value, $request->user()->password)) {
                        $fail(__('auth.password'));
                    }
                }
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::log('password.change', 'users', $request->user()->id, $request);

        return redirect()->route('admin.profile')
            ->with('success', __('admin.password_changed'));
    }
}
