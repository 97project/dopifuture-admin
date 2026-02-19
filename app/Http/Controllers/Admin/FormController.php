<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Form::class);

        $forms = Form::withCount(['submissions', 'unreadSubmissions'])
            ->latest()
            ->paginate(15);

        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        $this->authorize('create', Form::class);
        return view('admin.forms.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Form::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:forms,slug',
            'description' => 'nullable|string|max:1000',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:100',
            'fields.*.type' => 'required|in:text,email,textarea,select,checkbox,radio,number,tel,date,file',
            'fields.*.label' => 'required|array',
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'nullable|array',
            'notification_emails' => 'nullable|array',
            'notification_emails.*' => 'email',
            'success_message' => 'nullable|array',
            'is_active' => 'boolean',
            'requires_captcha' => 'boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        Form::create($data);

        return redirect()->route('admin.forms.index')
            ->with('success', __('admin.form_created'));
    }

    public function edit(Form $form)
    {
        $this->authorize('update', $form);
        return view('admin.forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:forms,slug,' . $form->id,
            'description' => 'nullable|string|max:1000',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:100',
            'fields.*.type' => 'required|in:text,email,textarea,select,checkbox,radio,number,tel,date,file',
            'fields.*.label' => 'required|array',
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'nullable|array',
            'notification_emails' => 'nullable|array',
            'notification_emails.*' => 'email',
            'success_message' => 'nullable|array',
            'is_active' => 'boolean',
            'requires_captcha' => 'boolean',
        ]);

        $form->update($data);

        return redirect()->route('admin.forms.index')
            ->with('success', __('admin.form_updated'));
    }

    public function destroy(Form $form)
    {
        $this->authorize('delete', $form);
        $form->delete();

        return redirect()->route('admin.forms.index')
            ->with('success', __('admin.form_deleted'));
    }

    // ── Submissions ─────────────────────────────────────────

    public function submissions(Form $form, Request $request)
    {
        $this->authorize('viewSubmissions', $form);

        $query = $form->submissions()->latest();

        if ($request->boolean('unread')) {
            $query->unread();
        }

        $submissions = $query->paginate(20)->withQueryString();

        return view('admin.forms.submissions', compact('form', 'submissions'));
    }

    public function showSubmission(Form $form, FormSubmission $submission)
    {
        $this->authorize('viewSubmissions', $form);

        if (!$submission->is_read) {
            $submission->update(['is_read' => true]);
        }

        return view('admin.forms.show-submission', compact('form', 'submission'));
    }

    public function destroySubmission(Form $form, FormSubmission $submission)
    {
        $this->authorize('delete', $form);
        $submission->delete();

        return redirect()->route('admin.forms.submissions', $form)
            ->with('success', __('admin.submission_deleted'));
    }
}
