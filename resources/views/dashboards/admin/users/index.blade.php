@extends('layouts.admin')

@section('title', 'ຜູ້ໃຊ້')
@section('page-title', 'ຈັດການຜູ້ໃຊ້')

@section('page-title-actions')
    <a href="{{ route('admin.users.create') }}" class="fns-btn fns-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        ເພີ່ມຜູ້ໃຊ້
    </a>
@endsection

@section('content')
@include('dashboards.admin._resource-index-styles')
@php
    $activeFilters = collect(['search', 'role_id', 'department_id', 'is_active'])
        ->filter(fn ($key) => request()->filled($key) || request()->has($key) && request($key) !== '')
        ->count();
    $pageActiveUsers = $users->getCollection()->where('is_active', true)->count();
@endphp

<div class="admin-resource">
    <section class="admin-resource-bar">
        <div>
            <div class="admin-resource-kicker">USER DIRECTORY</div>
            <h2 class="admin-resource-title">ຄົ້ນຫາ ແລະຈັດການບັນຊີຜູ້ໃຊ້</h2>
            <p class="admin-resource-copy">ກຳນົດບົດບາດ, ພະແນກ ແລະສະຖານະໃຊ້ງານຂອງຜູ້ໃຊ້ໃນລະບົບ.</p>
        </div>
        <div class="admin-resource-actions">
            <a href="{{ route('admin.roles.index') }}" class="fns-btn fns-btn-secondary">ບົດບາດ</a>
            <a href="{{ route('admin.departments.index') }}" class="fns-btn fns-btn-secondary">ພະແນກ</a>
        </div>
    </section>

    <section class="admin-stats" aria-label="User summary">
        <div class="admin-stat"><span>ຜົນລັບທັງໝົດ</span><strong>{{ number_format($users->total()) }}</strong></div>
        <div class="admin-stat"><span>ໜ້ານີ້</span><strong>{{ number_format($users->count()) }}</strong></div>
        <div class="admin-stat"><span>ໃຊ້ງານໃນໜ້ານີ້</span><strong>{{ number_format($pageActiveUsers) }}</strong></div>
        <div class="admin-stat"><span>ຕົວກອງ</span><strong>{{ number_format($activeFilters) }}</strong></div>
    </section>

    <section class="admin-filter-panel">
        <form method="GET" action="{{ route('admin.users.index') }}" class="admin-filter-form" style="--filter-cols: 3;">
            <div class="admin-field">
                <label for="search">ຄົ້ນຫາ</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="ຊື່ຜູ້ໃຊ້ ຫຼື ຊື່ເຕັມ">
            </div>
            <div class="admin-field">
                <label for="role_id">ບົດບາດ</label>
                <select id="role_id" name="role_id">
                    <option value="">ທຸກບົດບາດ</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="department_id">ພະແນກ</label>
                <select id="department_id" name="department_id">
                    <option value="">ທຸກພະແນກ</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="is_active">ສະຖານະ</label>
                <select id="is_active" name="is_active">
                    <option value="">ທຸກສະຖານະ</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>ໃຊ້ງານ</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>ບໍ່ໃຊ້ງານ</option>
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="fns-btn fns-btn-primary">ຄົ້ນຫາ</button>
                <a href="{{ route('admin.users.index') }}" class="fns-btn fns-btn-secondary">ລ້າງ</a>
            </div>
        </form>
    </section>

    <section class="admin-table-panel">
        <div class="admin-table-head">
            <h2>ລາຍຊື່ຜູ້ໃຊ້</h2>
            <span>{{ number_format($users->firstItem() ?? 0) }}-{{ number_format($users->lastItem() ?? 0) }} / {{ number_format($users->total()) }}</span>
        </div>
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ຜູ້ໃຊ້</th>
                        <th>ບົດບາດ</th>
                        <th>ພະແນກ</th>
                        <th>ສະຖານະ</th>
                        <th class="text-right">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td><span class="admin-code">#{{ $user->id }}</span></td>
                            <td>
                                <div class="admin-cell-strong">{{ $user->full_name }}</div>
                                <div class="admin-muted">{{ $user->username }}</div>
                            </td>
                            <td><span class="admin-pill admin-pill-navy">{{ $user->role->role_name }}</span></td>
                            <td>{{ $user->department->department_name ?? '-' }}</td>
                            <td>
                                @if ($user->is_active)
                                    <span class="admin-pill admin-pill-green">ໃຊ້ງານ</span>
                                @else
                                    <span class="admin-pill admin-pill-red">ປິດໃຊ້</span>
                                @endif
                            </td>
                            <td>
                                <div class="admin-row-actions">
                                    <a href="{{ route('admin.users.show', $user) }}" class="admin-icon-btn" title="ເບິ່ງ">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="admin-icon-btn" title="ແກ້ໄຂ">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.688-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບຜູ້ໃຊ້ນີ້?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-icon-btn admin-icon-btn-danger" title="ລົບ">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="admin-empty">
                                    <x-icons.users />
                                    <span>ບໍ່ພົບຂໍ້ມູນຜູ້ໃຊ້</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="admin-pagination">
                {{ $users->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
