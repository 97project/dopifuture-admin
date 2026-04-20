@extends('admin.layouts.app')
@section('title', isset($school) ? __('admin.edit_school') : __('admin.new_school'))
@section('content')
        <div class="space-y-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ isset($school) ? __('admin.edit_school') : __('admin.new_school') }}
                </h1>
                <form action="{{ isset($school) ? route('admin.schools.update', $school) : route('admin.schools.store') }}"
                        method="POST"
                        class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
                        @csrf @if(isset($school)) @method('PUT') @endif

                        {{-- School Name --}}
                        <div>
                                <label class="block text-sm font-medium mb-1">{{ __('admin.school_name') }} *</label>
                                <input type="text" name="name"
                                        value="{{ old('name', $school->name ?? '') }}"
                                        required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm"
                                        placeholder="{{ __('admin.school_name_placeholder') }}">
                                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Country / State / City --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <div>
                                        <label class="block text-sm font-medium mb-1">{{ __('admin.country') }}</label>
                                        <select name="country" id="country-select"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                                <option value="">{{ __('admin.select_country') }}</option>
                                                @foreach($countries as $country)
                                                        <option value="{{ $country->name }}"
                                                                data-id="{{ $country->id }}"
                                                                {{ old('country', $school->country ?? '') == $country->name ? 'selected' : '' }}>
                                                                {{ $country->name }}
                                                        </option>
                                                @endforeach
                                        </select>
                                        @error('country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                        <label class="block text-sm font-medium mb-1">{{ __('admin.state') }}</label>
                                        <select name="state" id="state-select"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                                <option value="">{{ __('admin.select_state') }}</option>
                                        </select>
                                        @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                        <label class="block text-sm font-medium mb-1">{{ __('admin.city') }}</label>
                                        <select name="city" id="city-select"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                                <option value="">{{ __('admin.select_city') }}</option>
                                        </select>
                                        @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                        </div>

                        {{-- Address --}}
                        <div><label class="block text-sm font-medium mb-1">{{ __('admin.address') }}</label><textarea
                                        name="address" rows="2"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">{{ old('address', $school->address ?? '') }}</textarea>
                                        @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Phone / Email / Website --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.phone') }}</label><input
                                                type="text" name="phone" value="{{ old('phone', $school->phone ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.email') }}</label><input
                                                type="email" name="email" value="{{ old('email', $school->email ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div><label class="block text-sm font-medium mb-1">{{ __('admin.website') }}</label><input
                                                type="url" name="website" value="{{ old('website', $school->website ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                        @error('website') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                        </div>

                        {{-- Active --}}
                        <div><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1"
                                                class="rounded border-gray-300 text-[#0B6AB2]" {{ old('is_active', $school->is_active ?? true) ? 'checked' : '' }}>
                                        {{ __('admin.active') }}</label>
                                        @error('is_active') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-3 pt-4 border-t"><button type="submit"
                                        class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg">{{ __('admin.save') }}</button><a
                                        href="{{ route('admin.schools.index') }}"
                                        class="px-6 py-2 text-sm text-gray-600 hover:text-gray-900">{{ __('admin.cancel') }}</a>
                        </div>
                </form>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country-select');
    const stateSelect   = document.getElementById('state-select');
    const citySelect    = document.getElementById('city-select');

    const savedState = @json(old('state', $school->state ?? ''));
    const savedCity  = @json(old('city', $school->city ?? ''));

    const baseUrl = '{{ url("admin/api") }}';

    function resetSelect(select, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
    }

    function loadStates(countryId, preselectState) {
        resetSelect(stateSelect, '{{ __("admin.select_state") }}');
        resetSelect(citySelect, '{{ __("admin.select_city") }}');
        if (!countryId) return;

        fetch(baseUrl + '/states/' + countryId)
            .then(r => r.json())
            .then(states => {
                states.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.name;
                    opt.dataset.id = s.id;
                    opt.textContent = s.name;
                    if (preselectState && s.name === preselectState) opt.selected = true;
                    stateSelect.appendChild(opt);
                });
                // Auto-load cities if state was preselected
                if (preselectState) {
                    const sel = stateSelect.querySelector('option:checked');
                    if (sel && sel.dataset.id) loadCities(sel.dataset.id, savedCity);
                }
            });
    }

    function loadCities(stateId, preselectCity) {
        resetSelect(citySelect, '{{ __("admin.select_city") }}');
        if (!stateId) return;

        fetch(baseUrl + '/cities/' + stateId)
            .then(r => r.json())
            .then(cities => {
                cities.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.textContent = c.name;
                    if (preselectCity && c.name === preselectCity) opt.selected = true;
                    citySelect.appendChild(opt);
                });
            });
    }

    countrySelect.addEventListener('change', function() {
        const sel = this.options[this.selectedIndex];
        loadStates(sel.dataset.id || '', '');
    });

    stateSelect.addEventListener('change', function() {
        const sel = this.options[this.selectedIndex];
        loadCities(sel.dataset.id || '', '');
    });

    // On page load, if country is already selected, load its states (and cascade to cities)
    const currentCountryOpt = countrySelect.querySelector('option:checked');
    if (currentCountryOpt && currentCountryOpt.dataset.id) {
        loadStates(currentCountryOpt.dataset.id, savedState);
    }
});
</script>
@endsection