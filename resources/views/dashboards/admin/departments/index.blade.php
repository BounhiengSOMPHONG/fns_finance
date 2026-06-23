@extends('layouts.admin')

@section('title', 'ພະແນກ')
@section('page-title', 'ຈັດການພະແນກ')

@section('page-title-actions')
    <button type="button" class="fns-btn fns-btn-primary" x-data @click="$dispatch('open-create-modal')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        ເພີ່ມພະແນກ
    </button>
@endsection

@section('content')
@include('dashboards.admin._resource-index-styles')
@php
    $pageUserLinks = $departments->getCollection()->sum('users_count');
    $activeFilters = collect(['search', 'department_type'])
        ->filter(fn ($key) => request()->filled($key))
        ->count();
@endphp

<div class="admin-resource" x-data="{ createModal: @js((bool) old('admin_create_modal')), editModal: @js(old('admin_edit_modal')), viewModal: null }" @open-create-modal.window="createModal = true">
    <section class="admin-resource-bar">
        <div>
            <div class="admin-resource-kicker">ORGANIZATION</div>
            <h2 class="admin-resource-title">ຈັດການພະແນກ ແລະໜ່ວຍງານ</h2>
            <p class="admin-resource-copy">ຜູກຜູ້ໃຊ້ກັບພະແນກໃຫ້ຖືກຕ້ອງ ເພື່ອໃຊ້ໃນການອ້າງອີງໜ້າທີ່ ແລະການກວດທານ.</p>
        </div>
        <div class="admin-resource-actions">
            <a href="{{ route('admin.users.index') }}" class="fns-btn fns-btn-secondary">ຜູ້ໃຊ້</a>
            <a href="{{ route('admin.roles.index') }}" class="fns-btn fns-btn-secondary">ບົດບາດ</a>
        </div>
    </section>

    <section class="admin-stats" aria-label="Department summary">
        <div class="admin-stat"><span>ຜົນລັບທັງໝົດ</span><strong>{{ number_format($departments->total()) }}</strong></div>
        <div class="admin-stat"><span>ໜ້ານີ້</span><strong>{{ number_format($departments->count()) }}</strong></div>
        <div class="admin-stat"><span>ຜູ້ໃຊ້ທີ່ຜູກໃນໜ້ານີ້</span><strong>{{ number_format($pageUserLinks) }}</strong></div>
        <div class="admin-stat"><span>ຕົວກອງ</span><strong>{{ number_format($activeFilters) }}</strong></div>
    </section>

    <section class="admin-filter-panel">
        <form method="GET" action="{{ route('admin.departments.index') }}" class="admin-filter-form admin-filter-form-auto" style="--filter-cols: 1;" x-data="{ filterTimer: null, submitFilter() { clearTimeout(this.filterTimer); this.filterTimer = setTimeout(() => this.$el.requestSubmit(), 450); } }" @input="submitFilter()" @change="$el.requestSubmit()">
            <div class="admin-field">
                <label for="search">ຄົ້ນຫາ</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="ຊື່ພະແນກ ຫຼື ປະເພດ">
            </div>
            <div class="admin-field">
                <label for="department_type">ປະເພດ</label>
                <select id="department_type" name="department_type">
                    <option value="">ທຸກປະເພດ</option>
                    @foreach ($departmentTypes as $type)
                        <option value="{{ $type }}" {{ request('department_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </section>

    <section class="admin-table-panel">
        <div class="admin-table-head">
            <h2>ລາຍຊື່ພະແນກ</h2>
            <span>{{ number_format($departments->firstItem() ?? 0) }}-{{ number_format($departments->lastItem() ?? 0) }} / {{ number_format($departments->total()) }}</span>
        </div>
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ພະແນກ</th>
                        <th>ປະເພດ</th>
                        <th>ຜູ້ໃຊ້</th>
                        <th class="text-right">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $dept)
                        <tr>
                            <td><span class="admin-code">#{{ $dept->id }}</span></td>
                            <td>
                                <div class="admin-cell-strong">{{ $dept->department_name }}</div>
                                <div class="admin-muted">department id {{ $dept->id }}</div>
                            </td>
                            <td><span class="admin-pill admin-pill-gold">{{ $dept->department_type }}</span></td>
                            <td>
                                <span class="admin-pill {{ $dept->users_count > 0 ? 'admin-pill-green' : 'admin-pill-gray' }}">
                                    {{ number_format($dept->users_count) }} ຄົນ
                                </span>
                            </td>
                            <td>
                                <div class="admin-row-actions">
                                    <button type="button" class="admin-icon-btn" title="ເບິ່ງ" @click="viewModal = 'department-{{ $dept->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button type="button" class="admin-icon-btn" title="ແກ້ໄຂ" @click="editModal = 'department-{{ $dept->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.688-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບພະແນກນີ້?');">
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
                            <td colspan="5">
                                <div class="admin-empty">
                                    <x-icons.building-office />
                                    <span>ບໍ່ພົບຂໍ້ມູນພະແນກ</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departments->hasPages())
            <div class="admin-pagination">
                {{ $departments->links() }}
            </div>
        @endif
    </section>

    <div x-cloak x-show="createModal" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="createModal = false">
        <div class="admin-modal" @click.outside="createModal = false">
            <div class="admin-modal-head">
                <div>
                    <h2>ເພີ່ມພະແນກ</h2>
                    <p>ເພີ່ມໜ່ວຍງານໃໝ່ເພື່ອຜູກກັບຜູ້ໃຊ້</p>
                </div>
                <button type="button" class="admin-modal-close" @click="createModal = false" aria-label="Close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.departments.store') }}" method="POST" class="admin-modal-body">
                @csrf
                <input type="hidden" name="admin_create_modal" value="1">

                <div class="admin-modal-grid">
                    <div class="admin-modal-field">
                        <label for="create-department-name">ຊື່ພະແນກ *</label>
                        <input type="text" name="department_name" id="create-department-name" value="{{ old('admin_create_modal') ? old('department_name') : '' }}" placeholder="ຊື່ພະແນກ">
                        @error('department_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-field">
                        <label for="create-department-type">ປະເພດພະແນກ *</label>
                        <input type="text" name="department_type" id="create-department-type" value="{{ old('admin_create_modal') ? old('department_type') : '' }}" placeholder="ປະເພດພະແນກ">
                        @error('department_type')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="admin-modal-foot">
                    <button type="button" class="fns-btn fns-btn-secondary" @click="createModal = false">ຍົກເລີກ</button>
                    <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($departments as $dept)
        @php $modalKey = 'department-' . $dept->id; @endphp
        <div x-cloak x-show="viewModal === @js($modalKey)" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="viewModal = null">
            <div class="admin-modal" @click.outside="viewModal = null">
                <div class="admin-modal-head">
                    <div>
                        <h2>ລາຍລະອຽດພະແນກ</h2>
                        <p>{{ $dept->department_name }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="viewModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="admin-modal-body">
                    <div class="admin-detail-list">
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ID</div>
                            <div class="admin-detail-value"><span class="admin-code">#{{ $dept->id }}</span></div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ຊື່ພະແນກ</div>
                            <div class="admin-detail-value">{{ $dept->department_name }}</div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ປະເພດ</div>
                            <div class="admin-detail-value"><span class="admin-pill admin-pill-gold">{{ $dept->department_type }}</span></div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ຈຳນວນຜູ້ໃຊ້</div>
                            <div class="admin-detail-value">
                                <span class="admin-pill {{ $dept->users_count > 0 ? 'admin-pill-green' : 'admin-pill-gray' }}">
                                    {{ number_format($dept->users_count) }} ຄົນ
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
                        <h2>ແກ້ໄຂພະແນກ</h2>
                        <p>{{ $dept->department_name }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="editModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.departments.update', $dept) }}" method="POST" class="admin-modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="admin_edit_modal" value="{{ $modalKey }}">

                    <div class="admin-modal-grid">
                        <div class="admin-modal-field">
                            <label for="department-name-{{ $dept->id }}">ຊື່ພະແນກ *</label>
                            <input type="text" name="department_name" id="department-name-{{ $dept->id }}" value="{{ old('admin_edit_modal') === $modalKey ? old('department_name') : $dept->department_name }}" placeholder="ຊື່ພະແນກ">
                            @error('department_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-field">
                            <label for="department-type-{{ $dept->id }}">ປະເພດພະແນກ *</label>
                            <input type="text" name="department_type" id="department-type-{{ $dept->id }}" value="{{ old('admin_edit_modal') === $modalKey ? old('department_type') : $dept->department_type }}" placeholder="ປະເພດພະແນກ">
                            @error('department_type')<span class="admin-modal-error">{{ $message }}</span>@enderror
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
