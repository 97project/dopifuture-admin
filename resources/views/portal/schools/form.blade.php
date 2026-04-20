@extends('portal.app')
@section('title', __('portal.edit_school_title'))
@section('page-title', __('portal.edit_school_title'))
@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="font-size:18px;font-weight:600;">{{ __('portal.edit_school') }}</div>
        <a href="{{ route('portal.schools.index') }}" class="dp-btn-ghost">← {{ __('portal.back') }}</a>
    </div>

    <div>
        <form action="{{ route('portal.schools.update', $school) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="dp-card">
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('admin.school_name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $school->name ?? '') }}" required class="dp-form-input" placeholder="{{ __('portal.enter_school_name') }}">
                    @error('name') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>

                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">{{ __('portal.country') }}</label>
                        <select name="country" id="country-select" class="dp-form-select">
                            <option value="">- {{ __('portal.select_country') }} -</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" data-id="{{ $country->id }}" {{ old('country', $school->country ?? '') == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="dp-form-label">{{ __('portal.state') }}</label>
                        <select name="state" id="state-select" class="dp-form-select">
                            <option value="">- {{ __('portal.select_state') }} -</option>
                        </select>
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('portal.city') }}</label>
                    <select name="city" id="city-select" class="dp-form-select">
                        <option value="">- {{ __('portal.select_city') }} -</option>
                    </select>
                </div>

                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">{{ __('portal.phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="dp-form-input">
                    </div>
                    <div>
                        <label class="dp-form-label">{{ __('admin.email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $school->email) }}" class="dp-form-input">
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('portal.address') }}</label>
                    <textarea name="address" class="dp-form-textarea" rows="2">{{ old('address', $school->address) }}</textarea>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">{{ __('portal.website') }}</label>
                    <input type="url" name="website" value="{{ old('website', $school->website) }}" class="dp-form-input" placeholder="https://">
                </div>
            </div>

            <div style="display:flex;gap:12px;align-items:center;margin-top:16px;">
                <button type="submit" class="dp-btn">{{ __('admin.save') }}</button>
                <a href="{{ route('portal.schools.index') }}" class="dp-btn-ghost">{{ __('portal.cancel') }}</a>
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
        resetSelect(stateSelect, '- {{ __('portal.select_state') }} -');
        resetSelect(citySelect, '- {{ __('portal.select_city') }} -');
        if (!countryId) return;
        fetch(baseUrl + '/states/' + countryId).then(r => r.json()).then(states => {
            states.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.name; opt.dataset.id = s.id; opt.textContent = s.name;
                if (preselectState && s.name === preselectState) opt.selected = true;
                stateSelect.appendChild(opt);
            });
            if (preselectState) {
                const sel = stateSelect.querySelector('option:checked');
                if (sel && sel.dataset.id) loadCities(sel.dataset.id, savedCity);
            }
        });
    }
    function loadCities(stateId, preselectCity) {
        resetSelect(citySelect, '— Select City —');
        if (!stateId) return;
        fetch(baseUrl + '/cities/' + stateId).then(r => r.json()).then(cities => {
            cities.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.name; opt.textContent = c.name;
                if (preselectCity && c.name === preselectCity) opt.selected = true;
                citySelect.appendChild(opt);
            });
        });
    }
    countrySelect.addEventListener('change', function() { loadStates(this.options[this.selectedIndex].dataset.id || '', ''); });
    stateSelect.addEventListener('change', function() { loadCities(this.options[this.selectedIndex].dataset.id || '', ''); });
    const currentCountryOpt = countrySelect.querySelector('option:checked');
    if (currentCountryOpt && currentCountryOpt.dataset.id) loadStates(currentCountryOpt.dataset.id, savedState);
});
</script>
@endsection