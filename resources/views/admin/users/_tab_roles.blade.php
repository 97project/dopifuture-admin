{{-- Roles & Permissions Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Roles --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C]">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                {{ __('admin.roles') }}
            </h3>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <div class="space-y-2">
                    @foreach($allRoles as $role)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-[#1A3A5C] hover:border-[#0B6AB2]/30 hover:bg-[#0B6AB2]/5 cursor-pointer transition {{ $user->roles->contains('id', $role->id) ? 'bg-[#0B6AB2]/5 border-[#0B6AB2]/20' : '' }}">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                               class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]"
                               {{ $user->roles->contains('id', $role->id) ? 'checked' : '' }}>
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $role->name }}</span>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $role->permissions->count() }} izin</p>
                        </div>
                        @if($user->roles->contains('id', $role->id))
                        <span class="w-2 h-2 rounded-full bg-[#0B6AB2]"></span>
                        @endif
                    </label>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-xs font-bold rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('admin.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Permissions --}}
    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C] flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0B6AB2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                {{ __('admin.permissions') }}
            </h3>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-[#0A1628] text-gray-500">{{ $user->getAllPermissions()->count() }}</span>
        </div>
        <div class="p-6">
            @php
                $groupedPerms = $user->getAllPermissions()->groupBy(function($perm) {
                    return explode('.', $perm->name)[0] ?? 'other';
                });
            @endphp
            @forelse($groupedPerms as $group => $perms)
            <div class="mb-4 last:mb-0">
                <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ ucfirst($group) }}</h4>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($perms as $perm)
                    <span class="px-2 py-1 bg-gray-100 dark:bg-[#0A1628] text-gray-600 dark:text-gray-400 rounded-lg text-[10px] font-medium">{{ explode('.', $perm->name)[1] ?? $perm->name }}</span>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-8">{{ __('admin.no_data') }}</p>
            @endforelse
        </div>
    </div>
</div>
