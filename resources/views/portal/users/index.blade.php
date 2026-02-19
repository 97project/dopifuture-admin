@extends('portal.layout')
@section('title', app()->getLocale() === 'tr' ? 'Kullanıcılar' : 'Users')
@php $isTr = app()->getLocale() === 'tr'; @endphp

@section('content')
    <div class="page-header">
        <h1>{{ $isTr ? 'Kullanıcılar' : 'Users' }}</h1>
        <p>{{ $isTr ? 'Sisteme kayıtlı kullanıcıları yönetin.' : 'Manage registered users.' }}</p>
    </div>

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <form style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ $isTr ? 'Ad / E-posta ara...' : 'Search name / email...' }}" class="form-input"
                style="width: 220px;">
            <select name="role" class="form-select" style="width: 150px;" onchange="this.form.submit()">
                <option value="">{{ $isTr ? 'Tüm Roller' : 'All Roles' }}</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-ghost">{{ $isTr ? 'Filtrele' : 'Filter' }}</button>
        </form>
        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'license-manager', 'school-admin']))
            <a href="{{ route('portal.users.create') }}" class="btn-primary"
                style="padding: 0.6rem 1.5rem; font-size: 0.85rem;">
                + {{ $isTr ? 'Yeni Kullanıcı' : 'New User' }}
            </a>
        @endif
    </div>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ $isTr ? 'Ad Soyad' : 'Name' }}</th>
                    <th>E-posta</th>
                    <th>{{ $isTr ? 'Rol' : 'Role' }}</th>
                    <th>{{ $isTr ? 'Durum' : 'Status' }}</th>
                    <th>{{ $isTr ? 'Kayıt' : 'Created' }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td style="font-weight: 500; color: white;">{{ $u->name }} {{ $u->surname }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @foreach($u->roles as $r)
                                <span class="badge badge-info" style="margin-right: 0.25rem;">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($u->status === 'active')
                                <span class="badge badge-success">{{ $isTr ? 'Aktif' : 'Active' }}</span>
                            @else
                                <span class="badge badge-danger">{{ $isTr ? 'Pasif' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td style="font-size: 0.8rem; color: var(--gray-500);">{{ $u->created_at?->format('d.m.Y') }}</td>
                        <td style="text-align: right; display: flex; gap: 0.25rem; justify-content: flex-end;">
                            <a href="{{ route('portal.users.show', $u) }}"
                                class="btn btn-sm btn-ghost">{{ $isTr ? 'Detay' : 'Detail' }}</a>
                            <a href="{{ route('portal.users.edit', $u) }}"
                                class="btn btn-sm btn-ghost">{{ $isTr ? 'Düzenle' : 'Edit' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--gray-500);">
                            {{ $isTr ? 'Kullanıcı bulunamadı.' : 'No users found.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 1rem;">{{ $users->withQueryString()->links() }}</div>
    @endif
@endsection