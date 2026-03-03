{{--
    Toast Notification — Figma success toast
    Auto-included in app.blade.php via session flash
--}}
@if(session('success') || session('error'))
<div id="dpToast" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:10px;padding:14px 20px;border-radius:10px;font-size:13px;font-weight:500;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,0.15);transition:opacity 0.3s,transform 0.3s;{{ session('success') ? 'background:#0E9F6E;' : 'background:#E33131;' }}">
    @if(session('success'))
        <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    @else
        <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    @endif
    <button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;cursor:pointer;margin-left:8px;">
        <svg width="14" height="14" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
<script>setTimeout(()=>{const t=document.getElementById('dpToast');if(t){t.style.opacity='0';t.style.transform='translateY(10px)';setTimeout(()=>t.remove(),300);}},4000);</script>
@endif
