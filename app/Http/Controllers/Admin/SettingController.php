<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Setting::class);

        $groups = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', Setting::class);

        $settings = $request->input('settings', []);

        foreach ($settings as $item) {
            $setting = Setting::where('group', $item['group'] ?? '')
                ->where('key', $item['key'] ?? '')
                ->first();

            if (!$setting) {
                continue;
            }

            $oldValue = $setting->value;
            $newValue = $item['value'] ?? '';

            if ($setting->is_encrypted && $newValue) {
                $newValue = encrypt($newValue);
            }

            if ($setting->is_translatable && is_array($newValue)) {
                $newValue = json_encode($newValue);
            }

            $setting->update(['value' => $newValue]);

            ActivityLog::log('setting_updated', 'settings', $setting, [
                'key' => $setting->group . '.' . $setting->key,
                'old_value' => $setting->is_encrypted ? '***' : $oldValue,
                'new_value' => $setting->is_encrypted ? '***' : $newValue,
            ]);
        }

        Cache::flush();

        return redirect()->route('admin.settings.index')
            ->with('success', __('admin.settings_updated'));
    }
}
