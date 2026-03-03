{{--
    Modal Partial — Figma node-id: 1405-9077
    Usage:
    @include('portal.partials._modal', [
        'id' => 'addStudentModal',
        'title' => 'Add New Student',
        'subtitle' => 'Fill in the details below to add a new student.',
    ])
    Slot: $slot (content between @component tags) or use dp-modal-body
--}}
<div id="{{ $id }}" class="dp-modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="dp-modal-card">
        <button type="button" class="dp-modal-close" onclick="document.getElementById('{{ $id }}').style.display='none'">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="dp-modal-title">{{ $title }}</div>
        @if(isset($subtitle))
            <p class="dp-modal-subtitle">{{ $subtitle }}</p>
        @endif
        <div class="dp-modal-body">
            {!! $slot ?? '' !!}
        </div>
    </div>
</div>
