<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\Language;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(protected TranslationService $translationService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Translation::class);

        $query = Translation::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('group')) {
            $query->forGroup($request->input('group'));
        }
        if ($request->filled('locale')) {
            $query->forLocale($request->input('locale'));
        }
        if ($request->boolean('missing_only')) {
            $locale = $request->input('locale', 'en');
            $query->where('locale', $locale)->where(fn($q) => $q->whereNull('value')->orWhere('value', ''));
        }

        $translations = $query->orderBy('group')->orderBy('key')->orderBy('locale')
            ->paginate(30)->withQueryString();

        $groups = Translation::distinct()->pluck('group');
        $languages = Language::active()->ordered()->get();

        return view('admin.translations.index', compact('translations', 'groups', 'languages'));
    }

    public function create()
    {
        $this->authorize('create', Translation::class);

        $groups = Translation::distinct()->pluck('group');
        $languages = Language::active()->ordered()->get();
        return view('admin.translations.create', compact('groups', 'languages'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Translation::class);

        $request->validate([
            'group' => 'required|string|max:100',
            'key' => 'required|string|max:255',
            'values' => 'required|array',
            'values.*' => 'nullable|string',
        ]);

        foreach ($request->input('values', []) as $locale => $value) {
            $this->translationService->setTranslation(
                $request->input('group'),
                $request->input('key'),
                $locale,
                $value
            );
        }

        return redirect()->route('admin.translations.index')
            ->with('success', __('admin.translation_created'));
    }

    public function edit(Translation $translation)
    {
        $this->authorize('update', $translation);

        $allTranslations = Translation::where('group', $translation->group)
            ->where('key', $translation->key)
            ->get()
            ->keyBy('locale');

        $languages = Language::active()->ordered()->get();

        return view('admin.translations.edit', compact('translation', 'allTranslations', 'languages'));
    }

    public function update(Request $request, Translation $translation)
    {
        $this->authorize('update', $translation);

        $request->validate([
            'values' => 'required|array',
            'values.*' => 'nullable|string',
        ]);

        foreach ($request->input('values', []) as $locale => $value) {
            $this->translationService->setTranslation(
                $translation->group,
                $translation->key,
                $locale,
                $value
            );
        }

        return redirect()->route('admin.translations.index')
            ->with('success', __('admin.translation_updated'));
    }

    public function destroy(Translation $translation)
    {
        $this->authorize('delete', $translation);

        $this->translationService->deleteTranslation($translation->group, $translation->key);

        return redirect()->route('admin.translations.index')
            ->with('success', __('admin.translation_deleted'));
    }

    public function export(Request $request)
    {
        $this->authorize('export', Translation::class);

        $locale = $request->input('locale', 'tr');
        $data = $this->translationService->exportJson($locale);

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=translations_{$locale}.json",
        ]);
    }

    public function import(Request $request)
    {
        $this->authorize('import', Translation::class);

        $request->validate([
            'file' => 'required|file|mimes:json',
            'locale' => 'required|string|in:' . implode(',', Language::getActiveCodes()),
        ]);

        $content = file_get_contents($request->file('file')->getPathname());
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return back()->with('error', __('admin.invalid_json_file'));
        }

        $count = $this->translationService->importJson($request->input('locale'), $data);

        return back()->with('success', __('admin.translations_imported', ['count' => $count]));
    }
}
