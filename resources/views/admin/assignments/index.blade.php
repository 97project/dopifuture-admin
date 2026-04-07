@extends('admin.layouts.app')

@section('title', 'Görev Yönetimi')

@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.dashboard') }}</a>
<span class="mx-2">/</span>
<span class="text-gray-900 dark:text-gray-100 font-medium">Görev Yönetimi</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📋 Görev Yönetimi</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.assignments.create', ['platform' => 'way_startup']) }}"
               class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm">
                ➕ Startup Görev
            </a>
            <a href="{{ route('admin.assignments.create', ['platform' => 'mission_way']) }}"
               class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm">
                ➕ Mission Görev
            </a>
        </div>
    </div>

    {{-- Way Startup Assignments --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-sm">🚀</span>
            Way Startup Görevleri
            <span class="ml-2 px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium">{{ count($wsAssignments) }}</span>
        </h2>

        @if(count($wsAssignments) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-3 font-semibold text-gray-500">ID</th>
                        <th class="pb-3 font-semibold text-gray-500">Ad</th>
                        <th class="pb-3 font-semibold text-gray-500">Simülasyon</th>
                        <th class="pb-3 font-semibold text-gray-500">Son Tarih</th>
                        <th class="pb-3 font-semibold text-gray-500">Durum</th>
                        <th class="pb-3 font-semibold text-gray-500 text-right">Üye Sayısı</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($wsAssignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="py-3 text-xs font-mono text-gray-400">{{ $assignment['id'] ?? '-' }}</td>
                        <td class="py-3 font-medium text-gray-900 dark:text-white">{{ $assignment['name'] ?? '-' }}</td>
                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ $assignment['simulationId'] ?? $assignment['simulation']['name'] ?? '-' }}</td>
                        <td class="py-3 text-gray-500">
                            @if(isset($assignment['dueDate']))
                                {{ \Carbon\Carbon::parse($assignment['dueDate'])->format('d.m.Y H:i') }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @php $status = $assignment['status'] ?? 'active'; @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600' }}">
                                {{ $status === 'active' ? 'Aktif' : ucfirst($status) }}
                            </span>
                        </td>
                        <td class="py-3 text-right text-gray-500">{{ count($assignment['memberIds'] ?? $assignment['members'] ?? []) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-400">
            <p class="text-4xl mb-2">📭</p>
            <p class="text-sm">Henüz Way Startup görevi yok</p>
        </div>
        @endif
    </div>

    {{-- Mission Way Assignments --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-sm">🎯</span>
            Mission Way Görevleri
            <span class="ml-2 px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium">{{ count($mwAssignments) }}</span>
        </h2>

        @if(count($mwAssignments) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-3 font-semibold text-gray-500">ID</th>
                        <th class="pb-3 font-semibold text-gray-500">Ad</th>
                        <th class="pb-3 font-semibold text-gray-500">Simülasyon</th>
                        <th class="pb-3 font-semibold text-gray-500">Son Tarih</th>
                        <th class="pb-3 font-semibold text-gray-500">Durum</th>
                        <th class="pb-3 font-semibold text-gray-500 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($mwAssignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="py-3 text-xs font-mono text-gray-400">{{ $assignment['id'] ?? '-' }}</td>
                        <td class="py-3 font-medium text-gray-900 dark:text-white">{{ $assignment['name'] ?? '-' }}</td>
                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ $assignment['simulationId'] ?? '-' }}</td>
                        <td class="py-3 text-gray-500">
                            @if(isset($assignment['deadline']) || isset($assignment['dueDate']))
                                {{ \Carbon\Carbon::parse($assignment['deadline'] ?? $assignment['dueDate'])->format('d.m.Y H:i') }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @php $status = $assignment['status'] ?? 'active'; @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600' }}">
                                {{ $status === 'active' ? 'Aktif' : ucfirst($status) }}
                            </span>
                        </td>
                        <td class="py-3 text-right">
                            <span class="text-xs text-gray-400">{{ $assignment['grade'] ?? '-' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-400">
            <p class="text-4xl mb-2">📭</p>
            <p class="text-sm">Henüz Mission Way görevi yok</p>
        </div>
        @endif
    </div>

</div>
@endsection
