@extends('admin.layouts.app')

@section('title', __('admin.notifications'))

@section('breadcrumb')
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">{{ __('admin.dashboard') }}</a>
        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ __('admin.notifications') }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Header Bar with Quick Stats + Tab Nav --}}
        <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] overflow-hidden">
            {{-- Quick Stats Row --}}
            <div class="grid grid-cols-4 border-b border-gray-100 dark:border-[#1A3A5C]">
                <div class="px-5 py-3 border-r border-gray-100 dark:border-[#1A3A5C]">
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format(\App\Models\NotificationLog::count()) }}</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">{{ __('admin.notif_stat_total_sent') }}</div>
                </div>
                <div class="px-5 py-3 border-r border-gray-100 dark:border-[#1A3A5C]">
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format(\App\Models\NotificationLog::sum('recipients_count')) }}</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">{{ __('admin.notif_stat_recipients') }}</div>
                </div>
                <div class="px-5 py-3 border-r border-gray-100 dark:border-[#1A3A5C]">
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $templates->count() }}</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">{{ __('admin.templates') }}</div>
                </div>
                <div class="px-5 py-3">
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($users->count()) }}</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">{{ __('admin.users') }}</div>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="flex items-center gap-1 px-2">
                <a href="{{ route('admin.notifications.index') }}"
                    class="px-4 py-2.5 text-xs font-semibold border-b-2 border-[#0B6AB2] text-[#0B6AB2] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    {{ __('admin.notif_compose') }}
                </a>
                <a href="{{ route('admin.notifications.history') }}"
                    class="px-4 py-2.5 text-xs font-medium text-gray-400 hover:text-gray-600 border-b-2 border-transparent hover:border-gray-300 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('admin.notif_history') }}
                </a>
                <a href="{{ route('admin.notifications.analytics') }}"
                    class="px-4 py-2.5 text-xs font-medium text-gray-400 hover:text-gray-600 border-b-2 border-transparent hover:border-gray-300 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    {{ __('admin.notif_analytics') }}
                </a>
                <div class="ml-auto py-2">
                    <a href="{{ route('admin.notification-templates.index') }}"
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 dark:border-[#1A3A5C] rounded-lg text-[10px] font-medium hover:bg-gray-50 dark:hover:bg-[#0A1628] transition">
                        📋 {{ __('admin.templates') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Compose Form --}}
        <form action="{{ route('admin.notifications.send') }}" method="POST" x-data="{
            mode: 'template',
            target: 'all',
            customTitle: '',
            customBody: '',
            selectedTemplate: ''
        }">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- ═══ LEFT COLUMN — Main Content (2/3) ═══ --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Mode Selection --}}
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.notification_mode') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer" @click="mode = 'template'">
                                <input type="radio" name="mode" value="template" class="sr-only" x-model="mode">
                                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all"
                                    :class="mode === 'template' ? 'border-[#0B6AB2] bg-blue-50/50 dark:bg-[#0B6AB2]/10 shadow-sm' : 'border-gray-100 dark:border-[#1A3A5C] hover:border-gray-200'">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.use_template') }}</div>
                                        <div class="text-[10px] text-gray-400 leading-tight">{{ __('admin.notif_template_desc') }}</div>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer" @click="mode = 'custom'">
                                <input type="radio" name="mode" value="custom" class="sr-only" x-model="mode">
                                <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all"
                                    :class="mode === 'custom' ? 'border-[#0B6AB2] bg-blue-50/50 dark:bg-[#0B6AB2]/10 shadow-sm' : 'border-gray-100 dark:border-[#1A3A5C] hover:border-gray-200'">
                                    <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ __('admin.custom_message') }}</div>
                                        <div class="text-[10px] text-gray-400 leading-tight">{{ __('admin.notif_custom_desc') }}</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Template Selector --}}
                    <div x-show="mode === 'template'" x-transition class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.template') }}</label>
                        @if($templates->count())
                            <div class="grid grid-cols-1 gap-2 max-h-72 overflow-y-auto pr-1">
                                @foreach ($templates as $template)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="template_key" value="{{ $template->key }}" class="sr-only" x-model="selectedTemplate">
                                        <div class="flex items-center gap-3 p-3 rounded-lg border transition-all"
                                            :class="selectedTemplate === '{{ $template->key }}' ? 'border-[#0B6AB2] bg-blue-50/30 dark:bg-[#0B6AB2]/10' : 'border-gray-100 dark:border-[#1A3A5C]/50 hover:border-gray-200 dark:hover:border-[#1A3A5C]'">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                                :class="selectedTemplate === '{{ $template->key }}' ? 'bg-[#0B6AB2] text-white' : 'bg-gray-100 dark:bg-[#0A1628] text-gray-400'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $template->getTranslation('title') }}</div>
                                                <div class="text-[10px] text-gray-400 truncate">{{ $template->key }}</div>
                                            </div>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                @foreach($template->channels ?? [] as $ch)
                                                    <span class="w-5 h-5 rounded flex items-center justify-center text-[9px]
                                                        {{ $ch === 'database' ? 'bg-blue-50 dark:bg-blue-900/20' : ($ch === 'fcm' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-orange-50 dark:bg-orange-900/20') }}">
                                                        {{ $ch === 'database' ? '💾' : ($ch === 'fcm' ? '📱' : '✉️') }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-gray-400 text-xs">
                                <p>{{ __('admin.no_data') }}</p>
                                <a href="{{ route('admin.notification-templates.create') }}" class="text-[#0B6AB2] font-semibold hover:underline mt-1 inline-block">+ {{ __('admin.create') }}</a>
                            </div>
                        @endif
                    </div>

                    {{-- Custom Message Editor --}}
                    <div x-show="mode === 'custom'" x-transition class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('admin.title') }}</label>
                                <span class="text-[9px] text-gray-300" x-text="customTitle.length + '/200'"></span>
                            </div>
                            <input type="text" name="custom_title" x-model="customTitle" maxlength="200" placeholder="{{ __('admin.notif_preview_title') }}"
                                class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] placeholder:text-gray-300">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('admin.body') }}</label>
                                <span class="text-[9px] text-gray-300" x-text="customBody.length + '/2000'"></span>
                            </div>
                            <textarea name="custom_body" rows="5" x-model="customBody" maxlength="2000" placeholder="{{ __('admin.notif_preview_body') }}"
                                class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] resize-y placeholder:text-gray-300"></textarea>
                        </div>

                        {{-- Variable Chips --}}
                        <div class="bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg p-3">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('admin.notif_variables') }}</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach(['{name}' => 'Ad Soyad', '{email}' => 'E-posta', '{school}' => 'Okul', '{role}' => 'Rol'] as $var => $label)
                                    <button type="button"
                                        @click="customBody += ' {{ $var }}'"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-medium bg-white dark:bg-[#0E2442] border border-gray-200 dark:border-[#1A3A5C] text-gray-600 dark:text-gray-300 hover:border-[#0B6AB2] hover:text-[#0B6AB2] transition cursor-pointer">
                                        <code class="text-[9px] font-mono text-[#0B6AB2]">{{ $var }}</code>
                                        <span class="text-gray-400">{{ $label }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Channels & Target — Combined Card --}}
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 space-y-5">
                        {{-- Channels --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.notif_channels') }}</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach (['database' => ['💾', __('admin.notif_channel_database'), 'blue'], 'fcm' => ['📱', __('admin.notif_channel_push'), 'green'], 'mail' => ['✉️', __('admin.notif_channel_email'), 'orange']] as $ch => [$icon, $label, $color])
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="channels[]" value="{{ $ch }}" class="peer sr-only"
                                            {{ $ch === 'database' ? 'checked' : '' }}>
                                        <div class="text-center p-3 rounded-xl border-2 border-gray-100 dark:border-[#1A3A5C] peer-checked:border-[#0B6AB2] peer-checked:bg-blue-50/30 dark:peer-checked:bg-[#0B6AB2]/10 hover:border-gray-200 transition cursor-pointer">
                                            <span class="text-lg block">{{ $icon }}</span>
                                            <div class="text-[11px] font-semibold text-gray-700 dark:text-gray-300 mt-1">{{ $label }}</div>
                                            <div class="text-[9px] text-gray-400 mt-0.5">{{ $ch === 'database' ? __('admin.notif_channel_database_desc') : ($ch === 'fcm' ? __('admin.notif_channel_push_desc') : __('admin.notif_channel_email_desc')) }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-[#1A3A5C]">

                        {{-- Target --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.target_audience') }}</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach (['all' => ['🌐', __('admin.all_users')], 'role' => ['🔑', __('admin.notif_by_role')], 'school' => ['🏫', __('admin.notif_by_school')], 'selected' => ['👤', __('admin.selected_users')]] as $t => [$icon, $label])
                                    <label class="cursor-pointer" @click="target = '{{ $t }}'">
                                        <input type="radio" name="target" value="{{ $t }}" class="sr-only" x-model="target">
                                        <div class="text-center p-2.5 rounded-xl border-2 transition-all cursor-pointer"
                                            :class="target === '{{ $t }}' ? 'border-[#0B6AB2] bg-blue-50/30 dark:bg-[#0B6AB2]/10' : 'border-gray-100 dark:border-[#1A3A5C] hover:border-gray-200'">
                                            <span class="text-base block">{{ $icon }}</span>
                                            <div class="text-[10px] font-semibold text-gray-700 dark:text-gray-300 mt-1 leading-tight">{{ $label }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Role picker --}}
                            <div x-show="target === 'role'" x-transition class="mt-3 p-3 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('admin.roles') }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($roles as $role)
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="role_names[]" value="{{ $role->name }}" class="peer sr-only">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold border border-gray-200 dark:border-[#1A3A5C] peer-checked:border-[#0B6AB2] peer-checked:bg-[#0B6AB2] peer-checked:text-white hover:border-gray-300 transition cursor-pointer">{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- School picker --}}
                            <div x-show="target === 'school'" x-transition class="mt-3 p-3 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('admin.schools') }}</p>
                                <select name="school_ids[]" multiple size="4"
                                    class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-xs focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[9px] text-gray-400 mt-1">{{ __('admin.hold_ctrl') }}</p>
                            </div>

                            {{-- User picker --}}
                            <div x-show="target === 'selected'" x-transition class="mt-3 p-3 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('admin.users') }}</p>
                                <select name="user_ids[]" multiple size="5"
                                    class="w-full rounded-lg border-gray-200 dark:border-[#1A3A5C] dark:bg-[#0A1628] text-xs focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} {{ $user->surname }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                <p class="text-[9px] text-gray-400 mt-1">{{ __('admin.hold_ctrl') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ RIGHT COLUMN — Preview & Actions (1/3) ═══ --}}
                <div class="space-y-5">

                    {{-- Live Preview --}}
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5 sticky top-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.notif_preview') }}</p>

                        {{-- Phone frame --}}
                        <div class="bg-gradient-to-b from-gray-900 to-gray-800 rounded-[1.5rem] p-3 mx-auto max-w-[260px] shadow-xl">
                            {{-- Status bar --}}
                            <div class="flex items-center justify-between px-3 pt-1 pb-2">
                                <span class="text-[9px] text-gray-400 font-medium">9:41</span>
                                <div class="w-16 h-4 bg-gray-700 rounded-full"></div>
                                <div class="flex items-center gap-1">
                                    <div class="w-3 h-3 border border-gray-400 rounded-sm flex items-center justify-center">
                                        <div class="w-1.5 h-1 bg-green-400 rounded-[1px]"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notification card --}}
                            <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur rounded-2xl p-3 shadow-lg space-y-1.5 mx-auto">
                                <div class="flex items-start gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-[#0B6AB2] to-[#13398E] flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-[8px] font-black">DF</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-gray-800 dark:text-gray-200">DopiFuture</span>
                                            <span class="text-[8px] text-gray-400">{{ __('admin.notif_now') }}</span>
                                        </div>
                                        <p class="text-[10px] font-semibold text-gray-700 dark:text-gray-300 mt-0.5 leading-snug"
                                            x-text="customTitle || '{{ __('admin.notif_preview_title') }}'"></p>
                                        <p class="text-[9px] text-gray-500 mt-0.5 leading-snug line-clamp-3"
                                            x-text="customBody || '{{ __('admin.notif_preview_body') }}'"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Home indicator --}}
                            <div class="flex justify-center pt-3 pb-1">
                                <div class="w-20 h-1 bg-gray-600 rounded-full"></div>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="mt-4 space-y-2 text-[10px]">
                            <div class="flex items-center justify-between py-1.5 px-2 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                                <span class="text-gray-400 font-semibold">{{ __('admin.notification_mode') }}</span>
                                <span class="font-bold text-gray-700 dark:text-gray-300" x-text="mode === 'template' ? '📋 {{ __('admin.use_template') }}' : '✏️ {{ __('admin.custom_message') }}'"></span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 px-2 bg-gray-50 dark:bg-[#0A1628]/60 rounded-lg">
                                <span class="text-gray-400 font-semibold">{{ __('admin.target_audience') }}</span>
                                <span class="font-bold text-gray-700 dark:text-gray-300"
                                    x-text="target === 'all' ? '🌐 {{ __('admin.all_users') }}' : target === 'role' ? '🔑 {{ __('admin.notif_by_role') }}' : target === 'school' ? '🏫 {{ __('admin.notif_by_school') }}' : '👤 {{ __('admin.selected_users') }}'"></span>
                            </div>
                        </div>

                        {{-- Send Button --}}
                        <button type="submit"
                            class="w-full mt-4 flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-[#13398E] to-[#0B6AB2] hover:from-[#0B6AB2] hover:to-[#13398E] text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-blue-900/20 hover:shadow-blue-900/40 active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            {{ __('admin.notif_send_now') }}
                        </button>
                    </div>

                    {{-- Recent Activity --}}
                    <div class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">{{ __('admin.notif_recent') }}</p>
                        @php $recent = \App\Models\NotificationLog::with('sender')->latest()->take(5)->get(); @endphp
                        @forelse ($recent as $log)
                            <div class="flex items-start gap-2 py-2 {{ !$loop->last ? 'border-b border-gray-50 dark:border-[#1A3A5C]/30' : '' }}">
                                <div class="w-6 h-6 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[10px] font-semibold text-gray-700 dark:text-gray-300 truncate">{{ Str::limit($log->title, 30) }}</div>
                                    <div class="text-[9px] text-gray-400">{{ $log->created_at->diffForHumans() }} · {{ $log->sender?->name ?? 'System' }}</div>
                                </div>
                                <span class="text-[9px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-1.5 py-0.5 rounded flex-shrink-0">{{ $log->recipients_count }}</span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-[10px] text-gray-400">
                                {{ __('admin.notif_no_history') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection