@extends('admin.layouts.app')

@section('title', __('admin.users'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.users') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search') }}..."
                    class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <select name="status" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('admin.active') }}
                    </option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                        {{ __('admin.inactive') }}</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>
                <select name="role" class="rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm">
                    <option value="">{{ __('admin.all_roles') }}</option>
                    @foreach($roles as $role)<option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>@endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-[#0B6AB2] text-white rounded-lg text-sm hover:bg-[#13398E] transition">{{ __('admin.filter') }}</button>
                    <a href="{{ route('admin.users.index') }}"
                        class="px-3 py-2 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">✕</a>
                </div>
            </form>
        </div>

        <form id="bulkForm" action="{{ route('admin.users.bulk-action') }}" method="POST">
            @csrf
            <div id="bulkBar"
                class="hidden bg-[#0B6AB2]/10 dark:bg-[#0B6AB2]/20 rounded-xl border border-[#0B6AB2]/30 p-3 flex items-center gap-3 mb-4">
                <span class="text-sm text-[#0B6AB2] dark:text-blue-300" id="selectedCount">0
                    {{ __('admin.items_selected', ['count' => 0]) }}</span>
                <select name="action" class="px-3 py-1.5 rounded-lg border-[#0B6AB2]/30 bg-white dark:bg-[#0A1628] text-sm">
                    <option value="activate">{{ __('admin.active') }}</option>
                    <option value="deactivate">{{ __('admin.inactive') }}</option>
                    <option value="delete">{{ __('admin.delete') }}</option>
                </select>
                <button type="submit"
                    class="px-3 py-1.5 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-xs font-medium rounded-lg transition">{{ __('admin.confirm') }}</button>
            </div>

            <div
                class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-[#1A3A5C]">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.users') }} <span
                            class="text-gray-400 font-normal">({{ $users->total() }})</span></h3>
                    @can('users.create')
                        <a href="{{ route('admin.users.create') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0B6AB2] text-white rounded-lg text-xs font-medium hover:bg-[#13398E] transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('admin.new_user') }}
                        </a>
                    @endcan
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-[#1A3A5C]">
                            <th class="w-10 px-5 py-3"><input type="checkbox" id="selectAll"
                                    class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]"></th>
                            @php $sort = request('sort');
                            $dir = request('direction', 'desc'); @endphp
                            <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $dir === 'asc' && $sort === 'name' ? 'desc' : 'asc']) }}"
                                    class="hover:text-gray-600">{{ __('admin.name') }}
                                    @if($sort === 'name'){{ $dir === 'asc' ? '↑' : '↓' }}@endif</a>
                            </th>
                            <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => $dir === 'asc' && $sort === 'email' ? 'desc' : 'asc']) }}"
                                    class="hover:text-gray-600">{{ __('admin.email') }}
                                    @if($sort === 'email'){{ $dir === 'asc' ? '↑' : '↓' }}@endif</a>
                            </th>
                            <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.roles') }}</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.status') }}</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $dir === 'asc' && $sort === 'created_at' ? 'desc' : 'asc']) }}"
                                    class="hover:text-gray-600">{{ __('admin.date') }}
                                    @if($sort === 'created_at'){{ $dir === 'asc' ? '↑' : '↓' }}@endif</a>
                            </th>
                            <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-[#1A3A5C]/50">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-[#0A1628]/30 transition">
                                <td class="px-5 py-3"><input type="checkbox" name="ids[]" value="{{ $user->id }}"
                                        class="bulk-check rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]"></td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-[#0B6AB2]/10 flex items-center justify-center"><span
                                                    class="text-[#0B6AB2] text-xs font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="font-medium text-gray-900 dark:text-white hover:text-[#0B6AB2] transition">{{ $user->full_name }}</a>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{{ $user->email }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @php $sc = ['active' => 'emerald', 'banned' => 'red', 'inactive' => 'gray'][$user->status] ?? 'gray'; @endphp
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $sc }}-50 dark:bg-{{ $sc }}-900/20 text-{{ $sc }}-600"><span
                                            class="w-1.5 h-1.5 rounded-full bg-{{ $sc }}-500"></span>{{ $user->status }}</span>
                                </td>
                                <td class="px-5 py-3 text-center text-xs text-gray-500">
                                    {{ $user->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="text-xs text-gray-500 hover:underline">{{ __('admin.view') }}</a>
                                        @can('users.edit')<a href="{{ route('admin.users.edit', $user) }}"
                                        class="text-xs text-gray-500 hover:underline">{{ __('admin.edit') }}</a>@endcan
                                        @can('users.delete')
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('{{ __('admin.confirm') }}?')">@csrf @method('DELETE')
                                                    <button
                                                        class="text-xs text-red-500 hover:underline">{{ __('admin.delete') }}</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-400">{{ __('admin.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($users->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100 dark:border-[#1A3A5C]">{{ $users->links() }}</div>
                @endif
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.getElementById('selectAll')?.addEventListener('change', function () {
                document.querySelectorAll('.bulk-check').forEach(cb => cb.checked = this.checked);
                updateBulkBar();
            });
            document.querySelectorAll('.bulk-check').forEach(cb => cb.addEventListener('change', updateBulkBar));

            function updateBulkBar() {
                const checked = document.querySelectorAll('.bulk-check:checked').length;
                const bar = document.getElementById('bulkBar');
                if (checked > 0) {
                    bar.classList.remove('hidden');
                    document.getElementById('selectedCount').textContent = checked + ' {{ __('admin.items_selected', ['count' => '']) }}';
                } else {
                    bar.classList.add('hidden');
                }
            }
        </script>
    @endpush
@endsection