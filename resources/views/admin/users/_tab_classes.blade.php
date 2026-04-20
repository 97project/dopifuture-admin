{{-- Classes Tab (for teacher, student roles) --}}
<div class="space-y-6">
    @php $userClasses = $classes ?? collect(); @endphp

    @if($userClasses->count())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($userClasses as $class)
                <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C]">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-[#1A3A5C]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $class->name }}</h3>
                                    <p class="text-[10px] text-gray-400">{{ $class->school->name ?? '' }} ·
                                        {{ $class->academic_year }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600">{{ ucfirst($class->pivot->role ?? 'üye') }}</span>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $class->is_active ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600' : 'bg-red-50 dark:bg-red-900/20 text-red-500' }}">
                                    {{ $class->is_active ? __('admin.active') : __('admin.inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center p-3 bg-gray-50 dark:bg-[#0A1628] rounded-lg">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $class->grade_level }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-0.5">Seviye</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 dark:bg-[#0A1628] rounded-lg">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $class->students->count() }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-0.5">{{ __('admin.auto_student') }}</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 dark:bg-[#0A1628] rounded-lg">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $class->teachers->count() }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mt-0.5">{{ __('admin.teachers') }}</p>
                            </div>
                        </div>

                        {{-- Students list --}}
                        @if($class->students->count())
                            <div>
                                <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('admin.students') }}</h4>
                                <div class="space-y-1.5 max-h-48 overflow-y-auto">
                                    @foreach($class->students->take(10) as $student)
                                        <a href="{{ route('admin.users.show', $student) }}"
                                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#0A1628] transition text-xs">
                                            <div
                                                class="w-6 h-6 rounded-full bg-gray-200 dark:bg-[#1A3A5C] flex items-center justify-center flex-shrink-0">
                                                <span
                                                    class="text-[10px] font-bold text-gray-500">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                            </div>
                                            <span
                                                class="text-gray-700 dark:text-gray-300 font-medium truncate">{{ $student->full_name }}</span>
                                            <span class="text-gray-400 ml-auto flex-shrink-0">→</span>
                                        </a>
                                    @endforeach
                                    @if($class->students->count() > 10)
                                        <p class="text-[10px] text-gray-400 px-3 py-1">+{{ $class->students->count() - 10 }} daha...</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Teachers list --}}
                        @if($class->teachers->count())
                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-[#1A3A5C]">
                                <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('admin.teachers') }}</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($class->teachers as $teacher)
                                        <a href="{{ route('admin.users.show', $teacher) }}"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-amber-50 dark:bg-amber-900/10 text-amber-700 dark:text-amber-400 rounded-lg text-[10px] font-medium hover:bg-amber-100 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ $teacher->full_name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#0A1628] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bu kullanıcı herhangi bir sınıfa kayıtlı değil
            </p>
        </div>
    @endif
</div>