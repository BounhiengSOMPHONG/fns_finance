@extends('layouts.admin')

@section('title', 'ບົດບາດ')
@section('page-title', 'ຈັດການບົດບາດ')

@section('page-title-actions')
    <button type="button" class="fns-btn fns-btn-primary" x-data @click="$dispatch('open-create-modal')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        ເພີ່ມບົດບາດ
    </button>
@endsection

@section('content')
@include('dashboards.admin._resource-index-styles')
@php
    $pageUserLinks = $roles->getCollection()->sum('users_count');
    $emptyRoles = $roles->getCollection()->where('users_count', 0)->count();
@endphp

<div class="admin-resource" x-data="{ createModal: @js((bool) old('admin_create_modal')), editModal: @js(old('admin_edit_modal')), viewModal: null }" @open-create-modal.window="createModal = true">
    <section class="admin-resource-bar">
        <div>
            <div class="admin-resource-kicker">ACCESS CONTROL</div>
            <h2 class="admin-resource-title">ຄຸ້ມຄອງບົດບາດ ແລະກຸ່ມສິດ</h2>
            <p class="admin-resource-copy">ໃຊ້ບົດບາດເພື່ອແຍກໜ້າທີ່ຂອງ admin, ການເງິນ ແລະຜູ້ກວດທານ.</p>
        </div>
        <div class="admin-resource-actions">
            <a href="{{ route('admin.users.index') }}" class="fns-btn fns-btn-secondary">ຜູ້ໃຊ້</a>
            <a href="{{ route('admin.departments.index') }}" class="fns-btn fns-btn-secondary">ພະແນກ</a>
        </div>
    </section>

    <section class="admin-stats" aria-label="Role summary">
        <div class="admin-stat"><span>ຜົນລັບທັງໝົດ</span><strong>{{ number_format($roles->total()) }}</strong></div>
        <div class="admin-stat"><span>ໜ້ານີ້</span><strong>{{ number_format($roles->count()) }}</strong></div>
        <div class="admin-stat"><span>ຜູ້ໃຊ້ທີ່ຜູກໃນໜ້ານີ້</span><strong>{{ number_format($pageUserLinks) }}</strong></div>
        <div class="admin-stat"><span>ຍັງບໍ່ມີຜູ້ໃຊ້</span><strong>{{ number_format($emptyRoles) }}</strong></div>
    </section>

    <section class="admin-filter-panel">
        <form method="GET" action="{{ route('admin.roles.index') }}" class="admin-filter-form admin-filter-form-simple">
            <div class="admin-field">
                <label for="search">ຄົ້ນຫາ</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="ຊື່ບົດບາດ">
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="fns-btn fns-btn-primary">ຄົ້ນຫາ</button>
                <a href="{{ route('admin.roles.index') }}" class="fns-btn fns-btn-secondary">ລ້າງ</a>
            </div>
        </form>
    </section>

    <section class="admin-table-panel">
        <div class="admin-table-head">
            <h2>ລາຍຊື່ບົດບາດ</h2>
            <span>{{ number_format($roles->firstItem() ?? 0) }}-{{ number_format($roles->lastItem() ?? 0) }} / {{ number_format($roles->total()) }}</span>
        </div>
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ບົດບາດ</th>
                        <th>ຜູ້ໃຊ້</th>
                        <th class="text-right">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td><span class="admin-code">#{{ $role->id }}</span></td>
                            <td>
                                <div class="admin-cell-strong">{{ $role->role_name }}</div>
                                <div class="admin-muted">role id {{ $role->id }}</div>
                            </td>
                            <td>
                                <span class="admin-pill {{ $role->users_count > 0 ? 'admin-pill-green' : 'admin-pill-gray' }}">
                                    {{ number_format($role->users_count) }} ຄົນ
                                </span>
                            </td>
                            <td>
                                <div class="admin-row-actions">
                                    <button type="button" class="admin-icon-btn" title="ເບິ່ງ" @click="viewModal = 'role-{{ $role->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button type="button" class="admin-icon-btn" title="ແກ້ໄຂ" @click="editModal = 'role-{{ $role->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.688-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບບົດບາດນີ້?');">
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
                            <td colspan="4">
                                <div class="admin-empty">
                                    <x-icons.shield-check />
                                    <span>ບໍ່ພົບຂໍ້ມູນບົດບາດ</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="admin-pagination">
                {{ $roles->links() }}
            </div>
        @endif
    </section>

    <div x-cloak x-show="createModal" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="createModal = false">
        <div class="admin-modal" @click.outside="createModal = false">
            <div class="admin-modal-head">
                <div>
                    <h2>ເພີ່ມບົດບາດ</h2>
                    <p>ສ້າງກຸ່ມສິດສຳລັບຜູ້ໃຊ້ໃນລະບົບ</p>
                </div>
                <button type="button" class="admin-modal-close" @click="createModal = false" aria-label="Close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.roles.store') }}" method="POST" class="admin-modal-body">
                @csrf
                <input type="hidden" name="admin_create_modal" value="1">

                <div class="admin-modal-grid">
                    <div class="admin-modal-field admin-modal-field-full">
                        <label for="create-role-name">ຊື່ບົດບາດ *</label>
                        <input type="text" name="role_name" id="create-role-name" value="{{ old('admin_create_modal') ? old('role_name') : '' }}" placeholder="ຊື່ບົດບາດ">
                        @error('role_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="admin-modal-foot">
                    <button type="button" class="fns-btn fns-btn-secondary" @click="createModal = false">ຍົກເລີກ</button>
                    <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($roles as $role)
        @php $modalKey = 'role-' . $role->id; @endphp
        <div x-cloak x-show="viewModal === @js($modalKey)" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="viewModal = null">
            <div class="admin-modal" @click.outside="viewModal = null">
                <div class="admin-modal-head">
                    <div>
                        <h2>ລາຍລະອຽດບົດບາດ</h2>
                        <p>{{ $role->role_name }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="viewModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="admin-modal-body">
                    <div class="admin-detail-list">
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ID</div>
                            <div class="admin-detail-value"><span class="admin-code">#{{ $role->id }}</span></div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ຊື່ບົດບາດ</div>
                            <div class="admin-detail-value">{{ $role->role_name }}</div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ຈຳນວນຜູ້ໃຊ້</div>
                            <div class="admin-detail-value">
                                <span class="admin-pill {{ $role->users_count > 0 ? 'admin-pill-green' : 'admin-pill-gray' }}">
                                    {{ number_format($role->users_count) }} ຄົນ
                                </span>
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
            <div class="admin-modal" @click.outside="editModal = null">
                <div class="admin-modal-head">
                    <div>
                        <h2>ແກ້ໄຂບົດບາດ</h2>
                        <p>{{ $role->role_name }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="editModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="admin-modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="admin_edit_modal" value="{{ $modalKey }}">

                    <div class="admin-modal-grid">
                        <div class="admin-modal-field admin-modal-field-full">
                            <label for="role-name-{{ $role->id }}">ຊື່ບົດບາດ *</label>
                            <input type="text" name="role_name" id="role-name-{{ $role->id }}" value="{{ old('admin_edit_modal') === $modalKey ? old('role_name') : $role->role_name }}" placeholder="ຊື່ບົດບາດ">
                            @error('role_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
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
