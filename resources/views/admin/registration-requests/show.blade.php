@extends('admin.layouts.app')

@section('title', __('admin.registration_request'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.registration-requests.index') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.registration_requests') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">#{{ $registrationRequest->id }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Status Badge --}}
        @php
            $statusColors = ['new' => 'blue', 'processing' => 'amber', 'approved' => 'emerald', 'rejected' => 'red'];
            $sc = $statusColors[$registrationRequest->status] ?? 'gray';
            $isConvertable = in_array($registrationRequest->status, ['new', 'processing']);
        @endphp

        {{-- Details Card --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.registration_request') }}
                    #{{ $registrationRequest->id }}</h3>
                <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $sc }}-50 dark:bg-{{ $sc }}-900/20 text-{{ $sc }}-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $sc }}-500"></span>
                    {{ __('admin.request_' . $registrationRequest->status) }}
                </span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-5">
                    @foreach([
                        __('admin.school_name') => $registrationRequest->school_name,
                        __('admin.country') => $registrationRequest->country ?? '—',
                        __('admin.state') => $registrationRequest->state ?? '—',
                        __('admin.city') => $registrationRequest->city ?? '—',
                        __('admin.contact_name') => $registrationRequest->contact_name . ' ' . $registrationRequest->contact_surname,
                        __('admin.email') => $registrationRequest->email,
                        __('admin.phone') => $registrationRequest->phone ?? '—',
                        (__('admin.rep_student_count')) => $registrationRequest->student_count ?? '—',
                        __('admin.created_at') => $registrationRequest->created_at->format('d.m.Y H:i'),
                    ] as $label => $value)
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</span>
                            <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
                @if($registrationRequest->notes)
                    <div class="mt-5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('admin.notes') }}</span>
                        <div class="mt-1 p-3 bg-gray-50 dark:bg-[#0A1628] rounded-lg text-sm text-gray-600 dark:text-gray-400">{{ $registrationRequest->notes }}</div>
                    </div>
                @endif
                @if($registrationRequest->admin_notes)
                    <div class="mt-4">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('admin.admin_notes') }}</span>
                        <div class="mt-1 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-lg text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $registrationRequest->admin_notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Convert to School Card --}}
        @if($isConvertable)
            @can('registration_requests.edit')
                <div x-data="convertForm()" class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-emerald-200 dark:border-emerald-800/50 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-3 border-b border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/50 dark:bg-emerald-900/10">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <h3 class="text-sm font-bold text-emerald-800 dark:text-emerald-400">{{ __('admin.convert_to_school') }}</h3>
                    </div>
                    <form action="{{ route('admin.registration-requests.convert', $registrationRequest) }}" method="POST" class="p-6 space-y-5">
                        @csrf

                        {{-- School Info --}}
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('admin.school_info') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1">{{ __('admin.school_name') }} *</label>
                                    <input type="text" name="school_name" value="{{ $registrationRequest->school_name }}" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">{{ __('admin.country') }}</label>
                                    <select name="country" id="convert_country"
                                        @change="loadStates($event.target.value)"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                        <option value="">{{ __('admin.select_country') }}</option>
                                        @foreach($countries as $c)
                                            <option value="{{ $c->name }}" data-id="{{ $c->id }}" {{ $registrationRequest->country == $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">{{ __('admin.state') }}</label>
                                    <select name="state" id="convert_state"
                                        @change="loadCities($event.target.value)"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                        <option value="">{{ __('admin.select_state') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">{{ __('admin.city') }}</label>
                                    <select name="city" id="convert_city"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                        <option value="">{{ __('admin.select_city') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">{{ __('admin.address') }}</label>
                                    <input type="text" name="address" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">{{ __('admin.phone') }}</label>
                                    <input type="text" name="phone" value="{{ $registrationRequest->phone }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">{{ __('admin.email') }}</label>
                                    <input type="email" name="email" value="{{ $registrationRequest->email }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-[#1A3A5C]">

                        {{-- License Option --}}
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="create_license" value="1" x-model="createLicense"
                                    class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.create_license_with_school') }}</span>
                            </label>
                            <div x-show="createLicense" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-6 p-4 bg-gray-50 dark:bg-[#0A1628] rounded-lg">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('admin.seat_count') }} *</label>
                                    <input type="number" name="seat_count" value="30" min="1"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('admin.license_duration') }}</label>
                                    <select name="license_months" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
                                        <option value="6">6 {{ __('admin.months') }}</option>
                                        <option value="12" selected>12 {{ __('admin.months') }}</option>
                                        <option value="24">24 {{ __('admin.months') }}</option>
                                        <option value="36">36 {{ __('admin.months') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- User Option --}}
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="create_user" value="1" x-model="createUser"
                                    class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.create_portal_user') }}</span>
                            </label>
                            <div x-show="createUser" x-transition class="ml-6 p-4 bg-gray-50 dark:bg-[#0A1628] rounded-lg">
                                <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>
                                        <strong>{{ $registrationRequest->contact_name }} {{ $registrationRequest->contact_surname }}</strong>
                                        ({{ $registrationRequest->email }})
                                        {{ __('admin.will_be_created_as_portal_user') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition"
                                onclick="return confirm('{{ __('admin.confirm_convert') }}')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('admin.convert_and_create') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endcan
        @elseif($registrationRequest->status === 'approved')
            <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-xl border border-emerald-200 dark:border-emerald-800/50 p-5">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium text-emerald-800 dark:text-emerald-400">{{ __('admin.request_already_approved') }}</span>
                </div>
            </div>
        @endif

        {{-- Status Update Form --}}
        @can('registration_requests.edit')
            <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('admin.request_status') }}</h3>
                </div>
                <form action="{{ route('admin.registration-requests.update', $registrationRequest) }}" method="POST" class="p-6 space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">{{ __('admin.request_status') }}</label>
                        <select name="status" class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                            @foreach(['new', 'processing', 'approved', 'rejected'] as $s)
                                <option value="{{ $s }}" {{ $registrationRequest->status === $s ? 'selected' : '' }}>{{ __('admin.request_' . $s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">{{ __('admin.admin_notes') }}</label>
                        <textarea name="admin_notes" rows="3" class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">{{ old('admin_notes', $registrationRequest->admin_notes) }}</textarea>
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition">{{ __('admin.save') }}</button>
                </form>
            </div>
        @endcan
    </div>

    @push('scripts')
    <script>
    function convertForm() {
        return {
            createLicense: false,
            createUser: false,
            async loadStates(countryName) {
                const select = document.getElementById('convert_state');
                const citySelect = document.getElementById('convert_city');
                select.innerHTML = '<option value="">{{ __("admin.select_state") }}</option>';
                citySelect.innerHTML = '<option value="">{{ __("admin.select_city") }}</option>';

                const opt = document.querySelector(`#convert_country option[value="${countryName}"]`);
                if (!opt) return;
                const countryId = opt.dataset.id;
                if (!countryId) return;

                const res = await fetch(`/admin/api/states/${countryId}`);
                const states = await res.json();
                states.forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.name;
                    o.dataset.id = s.id;
                    o.textContent = s.name;
                    select.appendChild(o);
                });
            },
            async loadCities(stateName) {
                const citySelect = document.getElementById('convert_city');
                citySelect.innerHTML = '<option value="">{{ __("admin.select_city") }}</option>';

                const opt = document.querySelector(`#convert_state option[value="${stateName}"]`);
                if (!opt) return;
                const stateId = opt.dataset.id;
                if (!stateId) return;

                const res = await fetch(`/admin/api/cities/${stateId}`);
                const cities = await res.json();
                cities.forEach(c => {
                    const o = document.createElement('option');
                    o.value = c.name;
                    o.textContent = c.name;
                    citySelect.appendChild(o);
                });
            }
        }
    }
    </script>
    @endpush
@endsection
