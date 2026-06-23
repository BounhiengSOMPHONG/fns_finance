@extends('layouts.admin')

@section('title', 'ຜູ້ໃຊ້')
@section('page-title', 'ຈັດການຜູ້ໃຊ້')

@section('page-title-actions')
    <button type="button" class="fns-btn fns-btn-primary" x-data @click="$dispatch('open-create-modal')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        ເພີ່ມຜູ້ໃຊ້
    </button>
@endsection

@section('content')
@include('dashboards.admin._resource-index-styles')
@php
    $activeFilters = collect(['search', 'role_id', 'department_id', 'is_active'])
        ->filter(fn ($key) => request()->filled($key) || request()->has($key) && request($key) !== '')
        ->count();
    $pageActiveUsers = $users->getCollection()->where('is_active', true)->count();
@endphp

<div class="admin-resource" x-data="{ createModal: @js((bool) old('admin_create_modal')), editModal: @js(old('admin_edit_modal')), viewModal: null }" @open-create-modal.window="createModal = true">
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
                                    <button type="button" class="admin-icon-btn" title="ເບິ່ງ" @click="viewModal = 'user-{{ $user->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button type="button" class="admin-icon-btn" title="ແກ້ໄຂ" @click="editModal = 'user-{{ $user->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.688-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/></svg>
                                    </button>
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

    <div x-cloak x-show="createModal" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="createModal = false">
        <div class="admin-modal admin-modal-wide" @click.outside="createModal = false">
            <div class="admin-modal-head">
                <div>
                    <h2>ເພີ່ມຜູ້ໃຊ້</h2>
                    <p>ສ້າງບັນຊີໃໝ່ ແລະກຳນົດບົດບາດໃຫ້ພ້ອມໃຊ້ງານ</p>
                </div>
                <button type="button" class="admin-modal-close" @click="createModal = false" aria-label="Close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="admin-modal-body">
                @csrf
                <input type="hidden" name="admin_create_modal" value="1">

                <div class="admin-modal-grid">
                    <div class="admin-modal-field">
                        <label for="create-username">ຊື່ຜູ້ໃຊ້ *</label>
                        <input type="text" name="username" id="create-username" value="{{ old('admin_create_modal') ? old('username') : '' }}" placeholder="ຊື່ຜູ້ໃຊ້">
                        @error('username')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-field">
                        <label for="create-full-name">ຊື່ເຕັມ *</label>
                        <input type="text" name="full_name" id="create-full-name" value="{{ old('admin_create_modal') ? old('full_name') : '' }}" placeholder="ຊື່ເຕັມ">
                        @error('full_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-field">
                        <label for="create-password">ລະຫັດຜ່ານ *</label>
                        <input type="password" name="password" id="create-password" placeholder="ຢ່າງນ້ອຍ 6 ໂຕ">
                        @error('password')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-field">
                        <label for="create-role">ບົດບາດ *</label>
                        <select name="role_id" id="create-role">
                            <option value="">ເລືອກບົດບາດ</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ old('admin_create_modal') && (string) old('role_id') === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-field">
                        <label for="create-department">ພະແນກ</label>
                        <select name="department_id" id="create-department">
                            <option value="">ເລືອກພະແນກ</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('admin_create_modal') && (string) old('department_id') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-check">
                        <input type="checkbox" name="is_active" id="create-active" value="1" {{ old('admin_create_modal') ? (old('is_active') ? 'checked' : '') : 'checked' }}>
                        <label for="create-active">ເປີດໃຊ້ງານ</label>
                    </div>
                </div>

                <div class="admin-modal-foot">
                    <button type="button" class="fns-btn fns-btn-secondary" @click="createModal = false">ຍົກເລີກ</button>
                    <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($users as $user)
        @php $modalKey = 'user-' . $user->id; @endphp
        <div x-cloak x-show="viewModal === @js($modalKey)" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="viewModal = null">
            <div class="admin-modal" @click.outside="viewModal = null">
                <div class="admin-modal-head">
                    <div>
                        <h2>ລາຍລະອຽດຜູ້ໃຊ້</h2>
                        <p>{{ $user->full_name }} · {{ $user->username }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="viewModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="admin-modal-body">
                    <div class="admin-detail-list">
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ID</div>
                            <div class="admin-detail-value"><span class="admin-code">#{{ $user->id }}</span></div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ຊື່ຜູ້ໃຊ້</div>
                            <div class="admin-detail-value">{{ $user->username }}</div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ຊື່ເຕັມ</div>
                            <div class="admin-detail-value">{{ $user->full_name }}</div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ບົດບາດ</div>
                            <div class="admin-detail-value"><span class="admin-pill admin-pill-navy">{{ $user->role->role_name }}</span></div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ພະແນກ</div>
                            <div class="admin-detail-value">{{ $user->department->department_name ?? '-' }}</div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ສະຖານະ</div>
                            <div class="admin-detail-value">
                                @if ($user->is_active)
                                    <span class="admin-pill admin-pill-green">ໃຊ້ງານ</span>
                                @else
                                    <span class="admin-pill admin-pill-red">ປິດໃຊ້</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="admin-modal-foot">
                        <button type="button" class="fns-btn fns-btn-secondary" @click="viewModal = null">ປິດ</button>
                        <button type="button" class="fns-btn fns-btn-primary" @click="viewModal = null; editModal = @js($modalKey)">ແກ້ໄຂ</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-cloak x-show="editModal === @js($modalKey)" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="editModal = null">
            <div class="admin-modal admin-modal-wide" @click.outside="editModal = null">
                <div class="admin-modal-head">
                    <div>
                        <h2>ແກ້ໄຂຜູ້ໃຊ້</h2>
                        <p>{{ $user->full_name }} · {{ $user->username }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="editModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="admin_edit_modal" value="{{ $modalKey }}">

                    <div class="admin-modal-grid">
                        <div class="admin-modal-field">
                            <label for="username-{{ $user->id }}">ຊື່ຜູ້ໃຊ້ *</label>
                            <input type="text" name="username" id="username-{{ $user->id }}" value="{{ old('admin_edit_modal') === $modalKey ? old('username') : $user->username }}" placeholder="ຊື່ຜູ້ໃຊ້">
                            @error('username')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-field">
                            <label for="full_name-{{ $user->id }}">ຊື່ເຕັມ *</label>
                            <input type="text" name="full_name" id="full_name-{{ $user->id }}" value="{{ old('admin_edit_modal') === $modalKey ? old('full_name') : $user->full_name }}" placeholder="ຊື່ເຕັມ">
                            @error('full_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-field">
                            <label for="password-{{ $user->id }}">ລະຫັດຜ່ານໃໝ່</label>
                            <input type="password" name="password" id="password-{{ $user->id }}" placeholder="ປ່ອຍວ່າງຖ້າບໍ່ປ່ຽນ">
                            @error('password')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-field">
                            <label for="role-edit-{{ $user->id }}">ບົດບາດ *</label>
                            <select name="role_id" id="role-edit-{{ $user->id }}">
                                <option value="">ເລືອກບົດບາດ</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ (string) (old('admin_edit_modal') === $modalKey ? old('role_id') : $user->role_id) === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-field">
                            <label for="department-edit-{{ $user->id }}">ພະແນກ</label>
                            <select name="department_id" id="department-edit-{{ $user->id }}">
                                <option value="">ເລືອກພະແນກ</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (string) (old('admin_edit_modal') === $modalKey ? old('department_id') : $user->department_id) === (string) $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-check">
                            <input type="checkbox" name="is_active" id="active-edit-{{ $user->id }}" value="1" {{ old('admin_edit_modal') === $modalKey ? (old('is_active') ? 'checked' : '') : ($user->is_active ? 'checked' : '') }}>
                            <label for="active-edit-{{ $user->id }}">ເປີດໃຊ້ງານ</label>
                        </div>
                    </div>

                    <div class="admin-modal-foot">
                        <button type="button" class="fns-btn fns-btn-secondary" @click="editModal = null">ຍົກເລີກ</button>
                        <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
