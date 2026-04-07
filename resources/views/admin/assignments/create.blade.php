@extends('admin.layouts.app')

@section('title', 'Yeni Görev Oluştur')

@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('admin.dashboard') }}</a>
<span class="mx-2">/</span>
<a href="{{ route('admin.assignments.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Görev Yönetimi</a>
<span class="mx-2">/</span>
<span class="text-gray-900 dark:text-gray-100 font-medium">Yeni Görev</span>
@endsection

@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        ➕ Yeni Görev —
        @if($platform === 'mission_way')
            <span class="text-cyan-600">Mission Way</span>
        @else
            <span class="text-purple-600">Way Startup</span>
        @endif
    </h1>

    <form action="{{ route('admin.assignments.store') }}" method="POST"
          class="bg-white dark:bg-[#0E2442]/50 rounded-xl border border-gray-100 dark:border-[#1A3A5C] p-6 space-y-5">
        @csrf
        <input type="hidden" name="platform" value="{{ $platform }}">

        {{-- Görev Adı --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Görev Adı *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                   placeholder="Ör: Hafta 1 - Liderlik Simülasyonu"
                   class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2] @error('name') border-red-500 @enderror">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Simülasyon Seçimi --}}
        <div>
            <label for="simulationId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Simülasyon *</label>
            @if(count($simulations) > 0)
            <select id="simulationId" name="simulationId" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
                <option value="">Simülasyon seçin...</option>
                @foreach($simulations as $sim)
                <option value="{{ $sim['id'] }}" {{ old('simulationId') == $sim['id'] ? 'selected' : '' }}>
                    {{ $sim['name'] ?? $sim['title'] ?? 'Simülasyon #'.$sim['id'] }}
                </option>
                @endforeach
            </select>
            @else
            <div class="text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                ⚠️ Simülasyon listesi alınamadı. API bağlantısını kontrol edin.
                <input type="number" name="simulationId" value="{{ old('simulationId') }}" required
                       placeholder="Simülasyon ID giriniz"
                       class="mt-2 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm">
            </div>
            @endif
            @error('simulationId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Açıklama --}}
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Açıklama</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Görev hakkında kısa açıklama..."
                      class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">{{ old('description') }}</textarea>
        </div>

        {{-- Son Tarih --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="dueDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Son Tarih</label>
                <input type="datetime-local" id="dueDate" name="dueDate" value="{{ old('dueDate') }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm focus:ring-[#0B6AB2] focus:border-[#0B6AB2]">
            </div>
        </div>

        {{-- Üye Seçimi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Atanacak {{ $platform === 'mission_way' ? 'Oyuncular' : 'Üyeler' }} *
            </label>
            @if(count($members) > 0)
            <div class="max-h-60 overflow-y-auto border border-gray-200 dark:border-[#1A3A5C] rounded-lg p-3 space-y-2">
                @foreach($members as $member)
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/30 cursor-pointer transition-colors">
                    <input type="checkbox" name="memberIds[]"
                           value="{{ $member['id'] ?? $member['userId'] ?? '' }}"
                           class="rounded border-gray-300 text-[#0B6AB2] focus:ring-[#0B6AB2]"
                           {{ in_array($member['id'] ?? '', old('memberIds', [])) ? 'checked' : '' }}>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                            {{ mb_strtoupper(mb_substr($member['name'] ?? $member['email'] ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $member['name'] ?? $member['email'] ?? 'Üye #'.($member['id'] ?? '?') }}</span>
                            @if(isset($member['email']))
                            <span class="text-xs text-gray-400 ml-2">{{ $member['email'] }}</span>
                            @endif
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @else
            <div class="text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                ⚠️ Üye listesi alınamadı. Manuel olarak ID girin (virgülle ayırın):
                <input type="text" id="manualMemberIds" placeholder="1,2,3"
                       class="mt-2 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#1A3A5C] bg-white dark:bg-[#0A1628] text-sm"
                       oninput="updateHiddenMembers(this.value)">
                <div id="hiddenMemberInputs"></div>
            </div>
            @endif
            @error('memberIds') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-[#1A3A5C]">
            <button type="submit" class="px-6 py-2 bg-[#0B6AB2] hover:bg-[#13398E] text-white text-sm font-medium rounded-lg transition-colors">
                Görev Oluştur
            </button>
            <a href="{{ route('admin.assignments.index') }}" class="px-6 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                {{ __('admin.cancel') }}
            </a>
        </div>
    </form>

</div>

@push('scripts')
<script>
function updateHiddenMembers(value) {
    const container = document.getElementById('hiddenMemberInputs');
    container.innerHTML = '';
    value.split(',').forEach(id => {
        id = id.trim();
        if (id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'memberIds[]';
            input.value = id;
            container.appendChild(input);
        }
    });
}
</script>
@endpush
@endsection
