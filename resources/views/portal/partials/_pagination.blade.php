{{--
    Pagination Partial — Figma style
    Usage: @include('portal.partials._pagination', ['paginator' => $users])
--}}
@if($paginator->hasPages())
<div style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;">
    @if($paginator->onFirstPage())
        <span style="font-size:13px;color:var(--color-txt-muted);cursor:default;">{{ __('portal.previous') }}</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" style="font-size:13px;color:var(--color-primary);text-decoration:none;font-weight:500;">{{ __('portal.previous') }}</a>
    @endif

    <span style="font-size:13px;color:var(--color-txt-muted);">
        Page {{ $paginator->currentPage() }} {{ __('portal.of') }} {{ $paginator->lastPage() }}
    </span>

    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" style="font-size:13px;color:var(--color-primary);text-decoration:none;font-weight:500;">{{ __('portal.next') }}</a>
    @else
        <span style="font-size:13px;color:var(--color-txt-muted);cursor:default;">{{ __('portal.next') }}</span>
    @endif
</div>
@endif
