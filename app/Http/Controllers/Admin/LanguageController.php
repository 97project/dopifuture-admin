<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Language::class);

        $languages = Language::ordered()->paginate(20);
        return view('admin.languages.index', compact('languages'));
    }

    public function create()
    {
        $this->authorize('create', Language::class);

        return view('admin.languages.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Language::class);

        $request->validate([
            'code' => 'required|string|max:5|unique:languages,code',
            'name' => 'required|string|max:50',
            'native_name' => 'required|string|max:50',
            'direction' => 'required|in:ltr,rtl',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'fallback_code' => 'nullable|string|max:5',
        ]);

        if ($request->boolean('is_default')) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language = Language::create($request->only([
            'code',
            'name',
            'native_name',
            'direction',
            'sort_order',
            'is_active',
            'is_default',
            'fallback_code'
        ]));

        ActivityLog::log('created', 'languages', $language);

        return redirect()->route('admin.languages.index')
            ->with('success', __('admin.language_created'));
    }

    public function edit(Language $language)
    {
        $this->authorize('update', $language);

        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, Language $language)
    {
        $this->authorize('update', $language);

        $request->validate([
            'code' => 'required|string|max:5|unique:languages,code,' . $language->id,
            'name' => 'required|string|max:50',
            'native_name' => 'required|string|max:50',
            'direction' => 'required|in:ltr,rtl',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'fallback_code' => 'nullable|string|max:5',
        ]);

        if ($request->boolean('is_default')) {
            Language::where('is_default', true)->where('id', '!=', $language->id)
                ->update(['is_default' => false]);
        }

        $language->update($request->only([
            'code',
            'name',
            'native_name',
            'direction',
            'sort_order',
            'is_active',
            'is_default',
            'fallback_code'
        ]));

        return redirect()->route('admin.languages.index')
            ->with('success', __('admin.language_updated'));
    }

    public function destroy(Language $language)
    {
        $this->authorize('delete', $language);

        if ($language->is_default) {
            return back()->with('error', __('admin.cannot_delete_default_language'));
        }

        ActivityLog::log('deleted', 'languages', $language);
        $language->delete();

        return redirect()->route('admin.languages.index')
            ->with('success', __('admin.language_deleted'));
    }
}
