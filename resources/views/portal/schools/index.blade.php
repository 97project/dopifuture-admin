@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Okullar' : 'Schools')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $isTr ? 'Okullar' : 'Schools' }}</h1>
        <p>{{ $isTr ? 'Sisteme kayıtlı okulları görüntüleyin ve yönetin.' : 'View and manage registered schools.' }}</p>
    </div>

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <form style="display: flex; gap: 0.5rem;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ $isTr ? 'Okul ara...' : 'Search school...' }}" class="form-input" style="width: 260px;">
            <button type="submit" class="btn btn-ghost">{{ $isTr ? 'Ara' : 'Search' }}</button>
        </form>
        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager']))
            <a href="{{ route('portal.schools.create') }}" class="btn-primary"
                style="padding: 0.6rem 1.5rem; font-size: 0.85rem;">
                + {{ $isTr ? 'Yeni Okul' : 'New School' }}
            </a>
        @endif
    </div>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ $isTr ? 'Okul Adı' : 'School Name' }}</th>
                    <th>{{ $isTr ? 'Şehir' : 'City' }}</th>
                    <th>{{ $isTr ? 'Sınıf' : 'Classes' }}</th>
                    <th>{{ $isTr ? 'Kullanıcı' : 'Users' }}</th>
                    <th>{{ $isTr ? 'Lisans' : 'Licenses' }}</th>
                    <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($schools as $school)
                    <tr>
                        <td style="font-weight: 500; color: white;">{{ $school->getTranslation('name') }}</td>
                        <td>{{ $school->city ?? '—' }}</td>
                        <td>{{ $school->classes_count }}</td>
                        <td>{{ $school->users_count }}</td>
                        <td>{{ $school->licenses_count }}</td>
                        <td>
                            @if($school->is_active)
                                <span class="badge badge-success">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                            @else
                                <span class="badge badge-danger">{{ $isTr ? 'Pasif' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td style="text-align: right; display: flex; gap: 0.25rem; justify-content: flex-end;">
                            <a href="{{ route('portal.schools.show', $school) }}"
                                class="btn btn-sm btn-ghost">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            <a href="{{ route('portal.schools.edit', $school) }}"
                                class="btn btn-sm btn-ghost">{{ $isTr ? 'Düzenle' : 'Edit' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--gray-500);">
                            {{ $isTr ? 'Henüz okul bulunamadı.' : 'No schools found.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($schools->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 1rem;">{{ $schools->links() }}</div>
    @endif
@endsection