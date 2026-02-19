@extends('admin.layouts.app')
@section('title', __('admin.edit_form'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.edit_form') }}: {{ $form->name }}</h1>

        <form action="{{ route('admin.forms.update', $form) }}" method="POST" id="form-builder"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.name') }}
                        *</label>
                    <input type="text" name="name" value="{{ old('name', $form->name) }}" required
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628] text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $form->slug) }}"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                </div>
            </div>

            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.description') }}</label>
                <textarea name="description" rows="2"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">{{ old('description', $form->description) }}</textarea>
            </div>

            <div>
                <div class="flex justify-between items-center mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.form_fields') }}
                        *</label>
                    <button type="button" onclick="addField()"
                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg">+
                        {{ __('admin.add_field') }}</button>
                </div>
                <div id="fields-container" class="space-y-3"></div>
            </div>

            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.notification_emails') }}</label>
                <input type="text" name="notification_emails_text"
                    value="{{ implode(', ', $form->notification_emails ?? []) }}"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
            </div>

            <div class="flex gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $form->is_active ? 'checked' : '' }}
                        class="rounded border-gray-300 text-[#0B6AB2]">
                    <span class="text-sm">{{ __('admin.active') }}</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="requires_captcha" value="1" {{ $form->requires_captcha ? 'checked' : '' }}
                        class="rounded border-gray-300 text-[#0B6AB2]">
                    <span class="text-sm">reCAPTCHA</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white font-medium rounded-lg">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.forms.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-[#0A1628] text-gray-700 dark:text-gray-300 font-medium rounded-lg">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>

    <script>
        let fieldIndex = 0;
        const fieldTypes = ['text', 'email', 'textarea', 'select', 'checkbox', 'radio', 'number', 'tel', 'date', 'file'];
        const existingFields = @json($form->fields ?? []);

        function addField(data = {}) {
            const container = document.getElementById('fields-container');
            const html = `
                        <div class="border border-gray-100 dark:border-[#1A3A5C] rounded-lg p-4 relative" id="field-${fieldIndex}">
                            <button type="button" onclick="removeField(${fieldIndex})" class="absolute top-2 right-2 text-red-400 hover:text-red-600">&times;</button>
                            <div class="grid grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Field Name *</label>
                                    <input type="text" name="fields[${fieldIndex}][name]" value="${data.name || ''}" required class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Type *</label>
                                    <select name="fields[${fieldIndex}][type]" required class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                                        ${fieldTypes.map(t => `<option value="${t}" ${t === (data.type || '') ? 'selected' : ''}>${t}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <label class="flex items-center gap-1">
                                        <input type="checkbox" name="fields[${fieldIndex}][required]" value="1" ${data.required ? 'checked' : ''} class="rounded border-gray-300 text-[#0B6AB2]">
                                        <span class="text-xs">Required</span>
                                    </label>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Label (TR) *</label>
                                    <input type="text" name="fields[${fieldIndex}][label][tr]" value="${(data.label && data.label.tr) || ''}" required class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Label (EN) *</label>
                                    <input type="text" name="fields[${fieldIndex}][label][en]" value="${(data.label && data.label.en) || ''}" required class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                                </div>
                            </div>
                        </div>
                    `;
            container.insertAdjacentHTML('beforeend', html);
            fieldIndex++;
        }

        function removeField(idx) { document.getElementById('field-' + idx)?.remove(); }

        document.getElementById('form-builder').addEventListener('submit', function () {
            const emailsInput = this.querySelector('[name="notification_emails_text"]');
            if (emailsInput && emailsInput.value) {
                emailsInput.value.split(',').forEach((email, i) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `notification_emails[${i}]`;
                    input.value = email.trim();
                    this.appendChild(input);
                });
            }
        });

        // Load existing fields
        existingFields.forEach(f => addField(f));
        if (!existingFields.length) addField();
    </script>
@endsection