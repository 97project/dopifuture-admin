{{-- Audit Log Tab --}}
<div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
    <div
        class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ __('admin.audit_log') }}
        </h3>
        <div class="flex gap-1 bg-gray-100 dark:bg-[#0A1628] rounded-lg p-1">
            <a href="{{ route('admin.users.show', ['user' => $user, 'tab' => 'audit', 'log_type' => 'actor']) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ ($logType ?? 'actor') === 'actor' ? 'bg-white dark:bg-[#0E2442] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ __('admin.actor') }}
            </a>
            <a href="{{ route('admin.users.show', ['user' => $user, 'tab' => 'audit', 'log_type' => 'subject']) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ ($logType ?? 'actor') === 'subject' ? 'bg-white dark:bg-[#0E2442] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ __('admin.subject') }}
            </a>
        </div>
    </div>

    @if(isset($activityLogs) && $activityLogs->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                        <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            {{ __('admin.action') }}</th>
                        <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            {{ __('admin.module') }}</th>
                        <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            {{ __('admin.ip_address') }}</th>
                        <th class="text-left px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            {{ __('admin.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                    @foreach($activityLogs as $log)
                        @php
                            $actionColors = [
                                'created' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600',
                                'updated' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600',
                                'deleted' => 'bg-red-50 dark:bg-red-900/20 text-red-600',
                                'login' => 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600',
                                'logout' => 'bg-gray-50 dark:bg-gray-900/20 text-gray-600',
                            ];
                            $actionColor = $actionColors[$log->action] ?? 'bg-gray-50 dark:bg-gray-900/20 text-gray-600';
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                            <td class="px-6 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold {{ $actionColor }}">{{ $log->action }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-[#0A1628] text-gray-500">{{ $log->module }}</span>
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-500 font-mono">{{ $log->ip_address }}</td>
                            <td class="px-6 py-3 text-xs text-gray-500">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">
            {{ $activityLogs->appends(['tab' => 'audit', 'log_type' => $logType ?? 'actor'])->links() }}</div>
    @else
        <div class="p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.no_data') }}</p>
            <p class="text-xs text-gray-400 mt-1">Bu kullanıcı için aktivite kaydı bulunmuyor</p>
        </div>
    @endif
</div>