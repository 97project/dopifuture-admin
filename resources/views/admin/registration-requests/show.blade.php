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
                                            __('admin.contact_name') => $registrationRequest->contact_name . ' ' . $registrationRequest->contact_surname,
                                            __('admin.email') => $registrationRequest->email,
                                            __('admin.phone') => $registrationRequest->phone ?? '—',
                                            __('admin.created_at') => $registrationRequest->created_at->format('d.m.Y H:i'),
                                        ] as $label => $value)
                                        <div>
                                            <
                        s                   pan class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</span>
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
            </div>
        </div>


                   {{-- Status Update Form --}}
        @can('registration_requests.edit')
            <div class="bg-whi
                           te dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                <div class="px-5 py-3 border-
                           b border-gray-100 dark:border-[#1A3A5C]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('admin.request_status') }}</h3>
                </div>

                                        <form action="{{ route('admin.registration-requests.update', $registrationRequest) }}" method="POST" class="p-6 space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">{{ __('admin.request_status') }}</label>
                        <selec
                           t name="status" class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
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
@endsection
