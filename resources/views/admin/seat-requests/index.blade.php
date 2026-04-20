@extends('admin.layouts.app')

@section('title', __('admin.menu_seat_requests'))

@section('breadcrumb')
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.menu_seat_requests') }}</span>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-[#1A3A5C]">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.school_seat_requests') }}</h3>
            <p class="text-xs text-gray-400 mt-1">{{ __('admin.seat_requests_desc') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-400 bg-gray-50 dark:bg-[#0A1628]/60">
                        <th class="px-5 py-3">{{ __('admin.school') }}</th>
                        <th class="px-5 py-3">{{ __('admin.requested_by') }}</th>
                        <th class="px-5 py-3">{{ __('admin.seats') }}</th>
                        <th class="px-5 py-3">{{ __('admin.reason') }}</th>
                        <th class="px-5 py-3">{{ __('admin.date') }}</th>
                        <th class="px-5 py-3">{{ __('admin.status') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#1A3A5C]">
                    @forelse($requests as $req)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $req->school->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $req->user->name ?? '' }} {{ $req->user->surname ?? '' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">
                                +{{ $req->requested_seats }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 max-w-xs truncate">{{ $req->reason ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $req->created_at?->format('M d, Y H:i') }}</td>
                        <td class="px-5 py-3">
                            @if($req->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ __('admin.pending') }}
                                </span>
                            @elseif($req->status === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600">
                                    {{ __('admin.approved') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-500">
                                    {{ __('admin.rejected') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($req->status === 'pending')
                                <div class="flex items-center gap-2 justify-end">
                                    <form method="POST" action="{{ route('admin.seat-requests.approve', $req) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="seats_to_add" value="{{ $req->requested_seats }}">
                                        <button type="submit" onclick="return confirm('{{ __('admin.confirm_approve_seats', ['seats' => $req->requested_seats, 'school' => $req->school->name ?? '']) }}')"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ __('admin.approve') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.seat-requests.reject', $req) }}" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('{{ __('admin.confirm_reject_seat') }}')"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-[#1A3A5C] text-gray-500 hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            {{ __('admin.reject') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">
                                    {{ $req->reviewer->name ?? 'Admin' }} · {{ $req->reviewed_at?->diffForHumans() }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                            <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('admin.no_seat_requests') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
