@extends('admin.layouts.app')
@section('title', __('admin.new_post'))
@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.new_post') }}</h1>

        <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-6">
            @csrf

            @foreach(['tr', 'en'] as $locale)
                <fieldset class="border border-gray-100 dark:border-[#1A3A5C] rounded-lg p-4">
                    <legend class="px-2 text-sm font-medium text-gray-500 uppercase">{{ strtoupper($locale) }}</legend>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.title') }}
                                *</label>
                            <input type="text" name="title[{{ $locale }}]" value="{{ old("title.{$locale}") }}" required
                                class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628] text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.content') }}</label>
                            <textarea name="content[{{ $locale }}]" rows="10"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628] text-gray-900 dark:text-white">{{ old("content.{$locale}") }}</textarea>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.excerpt') }}</label>
                            <textarea name="excerpt[{{ $locale }}]" rows="2"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">{{ old("excerpt.{$locale}") }}</textarea>
                        </div>
                    </div>
                </fieldset>
            @endforeach

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.status') }}
                        *</label>
                    <select name="status"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.featured_image') }}</label>
                    <input type="file" name="featured_image" accept="image/*"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700">
                </div>
            </div>

            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('admin.categories') }}</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($categories as $cat)
                        <label
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 dark:bg-[#0A1628] rounded-lg text-sm cursor-pointer hover:bg-gray-100">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}" {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-[#0B6AB2]">
                            {{ $cat->getTranslation('name') }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags</label>
                <input type="text" name="tags_input" value="{{ old('tags_input') }}"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg bg-white dark:bg-[#0A1628]"
                    placeholder="{{ __('admin.tags_placeholder') }}">
                <p class="text-xs text-gray-400 mt-1">{{ __('admin.comma_separated') }}</p>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-[#0B6AB2]">
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('admin.featured') }}</span>
            </label>

            <div class="flex gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                <button type="submit"
                    class="px-6 py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white font-medium rounded-lg">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.posts.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-[#0A1628] text-gray-700 dark:text-gray-300 font-medium rounded-lg">{{ __('admin.cancel') }}</a>
            </div>
        </form>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const tagsInput = this.querySelector('[name="tags_input"]');
            if (tagsInput && tagsInput.value) {
                tagsInput.value.split(',').forEach(tag => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tags[]';
                    input.value = tag.trim();
                    this.appendChild(input);
                });
            }
        });
    </script>
@endsection