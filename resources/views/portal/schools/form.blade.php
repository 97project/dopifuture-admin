@extends('portal.app')
@section('title', $school->exists ? ('Edit School') : ('New School'))
@section('page-title', $school->exists ? ('Edit School') : ('New School'))
@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div style="font-size:18px;font-weight:600;">{{ $school->exists ? 'Edit School' : 'New School' }}</div>
        <a href="{{ route('portal.schools.index') }}" class="dp-btn-ghost">← Back to Schools</a>
    </div>

    <div>
        <form action="{{ $school->exists ? route('portal.schools.update', $school) : route('portal.schools.store') }}" method="POST">
            @csrf
            @if($school->exists) @method('PUT') @endif

            <div class="dp-card">
                <div class="dp-form-group">
                    <label class="dp-form-label">School Name *</label>
                    <input type="text" name="name" value="{{ old('name', $school->name ?? '') }}" required class="dp-form-input" placeholder="Enter school name">
                    @error('name') <p class="dp-form-error">{{ $message }}</p> @enderror
                </div>

                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">Country</label>
                        <select name="country" id="country-select" class="dp-form-select">
                            <option value="">— Select Country —</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" data-id="{{ $country->id }}" {{ old('country', $school->country ?? '') == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="dp-form-label">State</label>
                        <select name="state" id="state-select" class="dp-form-select">
                            <option value="">— Select State —</option>
                        </select>
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">City</label>
                    <select name="city" id="city-select" class="dp-form-select">
                        <option value="">— Select City —</option>
                    </select>
                </div>

                <div class="dp-form-grid" style="margin-bottom:16px;">
                    <div>
                        <label class="dp-form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="dp-form-input">
                    </div>
                    <div>
                        <label class="dp-form-label">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $school->email) }}" class="dp-form-input">
                    </div>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">Address</label>
                    <textarea name="address" class="dp-form-textarea" rows="2">{{ old('address', $school->address) }}</textarea>
                </div>
                <div class="dp-form-group">
                    <label class="dp-form-label">Website</label>
                    <input type="url" name="website" value="{{ old('website', $school->website) }}" class="dp-form-input" placeholder="https://">
                </div>
                @if($school->exists)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:8px;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $school->is_active ? 'checked' : '' }} style="accent-color:var(--primary);">
                        <span class="dp-form-label" style="margin:0;">Active</span>
                    </label>
                @endif
            </div>

            <div style="display:flex;gap:12px;align-items:center;margin-top:16px;">
                <button type="submit" class="dp-btn">Save</button>
                <a href="{{ route('portal.schools.index') }}" class="dp-btn-ghost">Cancel</a>
            </div>
        </form>

        @if($school->exists)
            <form action="{{ route('portal.schools.destroy', $school) }}" method="POST" style="margin-top:24px;padding-top:24px;border-top:1px solid var(--color-row-border);" onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" style="background:var(--color-error-red);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;">Delete This School</button>
            </form>
        @endif
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
        resetSelect(stateSelect, '— Select State —');
        resetSelect(citySelect, '— Select City —');
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