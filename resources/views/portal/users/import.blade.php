@extends('portal.app')
@section('title', ($isTr = app()->getLocale() === 'tr') ? 'Toplu Öğrenci Yükle' : 'Bulk Student Import')
@section('page-title', $isTr ? 'Toplu Öğrenci Yükle' : 'Bulk Student Import')

@section('content')
@php $isTr = app()->getLocale() === 'tr'; @endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="font-size:18px;font-weight:600;">📤 {{ $isTr ? 'CSV ile Toplu Öğrenci Yükle' : 'Bulk Student Import via CSV' }}</div>
    <a href="{{ route('portal.users.index') }}" class="dp-btn-ghost">← {{ $isTr ? 'Geri' : 'Back' }}</a>
</div>

<div style="max-width:600px;">
    <div class="dp-card" style="margin-bottom:20px;">
        <div style="padding:16px 20px;background:rgba(67,100,247,0.06);border-radius:10px;margin-bottom:16px;">
            <div style="font-size:13px;font-weight:600;color:#4364F7;margin-bottom:6px;">📋 {{ $isTr ? 'CSV Format' : 'CSV Format' }}</div>
            <div style="font-size:12px;color:var(--text-muted);line-height:1.6;">
                {{ $isTr ? 'İlk satır başlık olmalıdır. Zorunlu sütunlar:' : 'First row must be headers. Required columns:' }}<br>
                <code>name</code> ({{ $isTr ? 'veya' : 'or' }} <code>ad</code>), <code>email</code> ({{ $isTr ? 'veya' : 'or' }} <code>e-posta</code>)<br>
                {{ $isTr ? 'Opsiyonel:' : 'Optional:' }} <code>surname</code> ({{ $isTr ? 'veya' : 'or' }} <code>soyad</code>)
            </div>
        </div>
        <form action="{{ route('portal.users.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="dp-form-group">
                <label class="dp-form-label">{{ $isTr ? 'Okul' : 'School' }} *</label>
                <select name="school_id" class="dp-form-select" required>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dp-form-group">
                <label class="dp-form-label">{{ $isTr ? 'CSV Dosyası' : 'CSV File' }} *</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required class="dp-form-input" style="padding:8px;">
                @error('csv_file') <p class="dp-form-error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="dp-btn">📤 {{ $isTr ? 'Yükle ve Oluştur' : 'Upload & Create' }}</button>
        </form>
    </div>

    @if(session('import_errors'))
    <div class="dp-card" style="border-left:3px solid #F59E0B;">
        <div style="font-size:13px;font-weight:600;color:#F59E0B;margin-bottom:8px;">⚠️ {{ $isTr ? 'Uyarılar' : 'Warnings' }}</div>
        <ul style="font-size:12px;color:var(--text-muted);margin:0;padding-left:16px;">
            @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
