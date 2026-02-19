@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Sınıflar' : 'Classes')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $isTr ? 'Sınıflar' : 'Classes' }}</h1>
        <p>{{ $isTr ? 'Okullara ait sınıfları görüntüleyin ve yönetin.' : 'View and manage school classes.' }}</p>
    </div>

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <form style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ $isTr ? 'Sınıf ara...' : 'Search class...' }}" class="form-input" style="width: 260px;">
            <button type="submit" class="btn btn-ghost">{{ $isTr ? 'Ara' : 'Search' }}</button>
        </form>
        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager', 'school-admin']))
            <a href="{{ route('portal.classes.create') }}" class="btn-primary"
                style="padding: 0.6rem 1.5rem; font-size: 0.85rem;">
                + {{ $isTr ? 'Yeni Sınıf' : 'New Class' }}
            </a>
        @endif
    </div>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ $isTr ? 'Sınıf' : 'Class' }}</th>
                    <th>{{ $isTr ? 'Okul' : 'School' }}</th>
                    <th>{{ $isTr ? 'Seviye' : 'Grade' }}</th>
                    <th>{{ $isTr ? 'Yıl' : 'Year' }}</th>
                    <th>{{ $isTr ? 'Öğrenci' : 'Students' }}</th>
                    <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $cls)
                    <tr>
                        <td style="font-weight: 500; color: white;">{{ $cls->name }}</td>
                        <td>{{ $cls->school?->getTranslation('name') ?? '—' }}</td>
                        <td>{{ $cls->grade_level ?? '—' }}</td>
                        <td>{{ $cls->academic_year ?? '—' }}</td>
                        <td>{{ $cls->students_count }}</td>
                        <td>
                            @if($cls->is_active)
                                <span class="badge badge-success">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                            @else
                                <span class="badge badge-danger">{{ $isTr ? 'Pasif' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td style="text-align: right; display: flex; gap: 0.25rem; justify-content: flex-end;">
                            <a href="{{ route('portal.classes.show', $cls) }}"
                                class="btn btn-sm btn-ghost">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            <a href="{{ route('portal.classes.edit', $cls) }}"
                                class="btn btn-sm btn-ghost">{{ $isTr ? 'Düzenle' : 'Edit' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--gray-500);">
                            {{ $isTr ? 'Henüz sınıf bulunamadı.' : 'No classes found.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($classes->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 1rem;">{{ $classes->links() }}</div>
    @endif
@endsection