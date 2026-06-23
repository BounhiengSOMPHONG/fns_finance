@extends('layouts.admin')

@section('title', 'ຜັງບັນຊີ')
@section('page-title', 'ຈັດການຜັງບັນຊີ')

@section('page-title-actions')
    <button type="button" class="fns-btn fns-btn-primary" x-data @click="$dispatch('open-create-modal')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        ເພີ່ມບັນຊີ
    </button>
@endsection

@section('content')
@include('dashboards.admin._resource-index-styles')
@php
    $firstCode = $chartOfAccounts->getCollection()->first()?->account_code ?? '-';
    $lastCode = $chartOfAccounts->getCollection()->last()?->account_code ?? '-';
@endphp

<div class="admin-resource" x-data="{ createModal: @js((bool) old('admin_create_modal')), editModal: @js(old('admin_edit_modal')), viewModal: null }" @open-create-modal.window="createModal = true">
    <section class="admin-resource-bar">
        <div>
            <div class="admin-resource-kicker">CHART OF ACCOUNTS</div>
            <h2 class="admin-resource-title">ຄຸ້ມຄອງລະຫັດບັນຊີສຳລັບແຜນງົບ</h2>
            <p class="admin-resource-copy">ກວດລະຫັດ ແລະຊື່ບັນຊີໃຫ້ພ້ອມກ່ອນນຳໄປໃຊ້ໃນລາຍຮັບ, ລາຍຈ່າຍ ແລະລາຍງານ.</p>
        </div>
        <div class="admin-resource-actions">
            <a href="{{ route('admin.users.index') }}" class="fns-btn fns-btn-secondary">ຜູ້ໃຊ້</a>
            <a href="{{ route('admin.home') }}" class="fns-btn fns-btn-secondary">ໜ້າລວມ</a>
        </div>
    </section>

    <section class="admin-stats" aria-label="Chart of accounts summary">
        <div class="admin-stat"><span>ຜົນລັບທັງໝົດ</span><strong>{{ number_format($chartOfAccounts->total()) }}</strong></div>
        <div class="admin-stat"><span>ໜ້ານີ້</span><strong>{{ number_format($chartOfAccounts->count()) }}</strong></div>
        <div class="admin-stat"><span>ລະຫັດທຳອິດ</span><strong>{{ $firstCode }}</strong></div>
        <div class="admin-stat"><span>ລະຫັດສຸດທ້າຍ</span><strong>{{ $lastCode }}</strong></div>
    </section>

    <section class="admin-filter-panel">
        <form method="GET" action="{{ route('admin.chart-of-accounts.index') }}" class="admin-filter-form admin-filter-form-simple">
            <div class="admin-field">
                <label for="search">ຄົ້ນຫາ</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="ລະຫັດ ຫຼື ຊື່ບັນຊີ">
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="fns-btn fns-btn-primary">ຄົ້ນຫາ</button>
                <a href="{{ route('admin.chart-of-accounts.index') }}" class="fns-btn fns-btn-secondary">ລ້າງ</a>
            </div>
        </form>
    </section>

    <section class="admin-table-panel">
        <div class="admin-table-head">
            <h2>ລາຍການບັນຊີ</h2>
            <span>{{ number_format($chartOfAccounts->firstItem() ?? 0) }}-{{ number_format($chartOfAccounts->lastItem() ?? 0) }} / {{ number_format($chartOfAccounts->total()) }}</span>
        </div>
        <div class="admin-table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ລະຫັດ</th>
                        <th>ຊື່ບັນຊີ</th>
                        <th>ບັນຊີແມ່</th>
                        <th class="text-right">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($chartOfAccounts as $account)
                        <tr>
                            <td><span class="admin-code">#{{ $account->id }}</span></td>
                            <td><span class="admin-code">{{ $account->account_code }}</span></td>
                            <td>
                                <div class="admin-cell-strong">{{ $account->account_name }}</div>
                                <div class="admin-muted">account code {{ $account->account_code }}</div>
                            </td>
                            <td>
                                @if($account->parent)
                                    <span class="admin-pill admin-pill-navy">{{ $account->parent->account_code }}</span>
                                    <div class="admin-muted">{{ $account->parent->account_name }}</div>
                                @else
                                    <span class="admin-pill admin-pill-gray">ໝວດໃຫຍ່</span>
                                @endif
                            </td>
                            <td>
                                <div class="admin-row-actions">
                                    <button type="button" class="admin-icon-btn" title="ເບິ່ງ" @click="viewModal = 'account-{{ $account->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button type="button" class="admin-icon-btn" title="ແກ້ໄຂ" @click="editModal = 'account-{{ $account->id }}'">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.688-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.chart-of-accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບບັນຊີນີ້?');">
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
                                    <x-icons.book-open />
                                    <span>ບໍ່ພົບຂໍ້ມູນບັນຊີ</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($chartOfAccounts->hasPages())
            <div class="admin-pagination">
                {{ $chartOfAccounts->links() }}
            </div>
        @endif
    </section>

    <div x-cloak x-show="createModal" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="createModal = false">
        <div class="admin-modal" @click.outside="createModal = false">
            <div class="admin-modal-head">
                <div>
                    <h2>ເພີ່ມບັນຊີ</h2>
                    <p>ເພີ່ມລະຫັດ ແລະຊື່ບັນຊີໃໝ່ໃນຜັງບັນຊີ</p>
                </div>
                <button type="button" class="admin-modal-close" @click="createModal = false" aria-label="Close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.chart-of-accounts.store') }}" method="POST" class="admin-modal-body">
                @csrf
                <input type="hidden" name="admin_create_modal" value="1">

                <div class="admin-modal-grid">
                    <div class="admin-modal-field">
                        <label for="create-account-code">ລະຫັດບັນຊີ *</label>
                        <input type="text" name="account_code" id="create-account-code" value="{{ old('admin_create_modal') ? old('account_code') : '' }}" placeholder="ລະຫັດບັນຊີ">
                        @error('account_code')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-field">
                        <label for="create-account-name">ຊື່ບັນຊີ *</label>
                        <input type="text" name="account_name" id="create-account-name" value="{{ old('admin_create_modal') ? old('account_name') : '' }}" placeholder="ຊື່ບັນຊີ">
                        @error('account_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-modal-field admin-modal-field-full">
                        <label for="create-parent-id">ບັນຊີແມ່ / parent_id</label>
                        <select name="parent_id" id="create-parent-id">
                            <option value="">ໝວດໃຫຍ່ (ບໍ່ມີ parent_id)</option>
                            @foreach($parentAccounts as $parent)
                                <option value="{{ $parent->id }}" {{ old('admin_create_modal') && (string) old('parent_id') === (string) $parent->id ? 'selected' : '' }}>
                                    {{ $parent->account_code }} - {{ $parent->account_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')<span class="admin-modal-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="admin-modal-foot">
                    <button type="button" class="fns-btn fns-btn-secondary" @click="createModal = false">ຍົກເລີກ</button>
                    <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($chartOfAccounts as $account)
        @php $modalKey = 'account-' . $account->id; @endphp
        <div x-cloak x-show="viewModal === @js($modalKey)" x-transition.opacity class="admin-modal-backdrop" @keydown.escape.window="viewModal = null">
            <div class="admin-modal" @click.outside="viewModal = null">
                <div class="admin-modal-head">
                    <div>
                        <h2>ລາຍລະອຽດບັນຊີ</h2>
                        <p>{{ $account->account_code }} · {{ $account->account_name }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="viewModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="admin-modal-body">
                    <div class="admin-detail-list">
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ID</div>
                            <div class="admin-detail-value"><span class="admin-code">#{{ $account->id }}</span></div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ລະຫັດບັນຊີ</div>
                            <div class="admin-detail-value"><span class="admin-code">{{ $account->account_code }}</span></div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ຊື່ບັນຊີ</div>
                            <div class="admin-detail-value">{{ $account->account_name }}</div>
                        </div>
                        <div class="admin-detail-row">
                            <div class="admin-detail-label">ບັນຊີແມ່ / parent_id</div>
                            <div class="admin-detail-value">
                                @if($account->parent)
                                    <span class="admin-code">#{{ $account->parent_id }}</span>
                                    <span class="admin-pill admin-pill-navy">{{ $account->parent->account_code }}</span>
                                    <div class="admin-muted">{{ $account->parent->account_name }}</div>
                                @else
                                    <span class="admin-pill admin-pill-gray">ໝວດໃຫຍ່</span>
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
            <div class="admin-modal" @click.outside="editModal = null">
                <div class="admin-modal-head">
                    <div>
                        <h2>ແກ້ໄຂບັນຊີ</h2>
                        <p>{{ $account->account_code }} · {{ $account->account_name }}</p>
                    </div>
                    <button type="button" class="admin-modal-close" @click="editModal = null" aria-label="Close">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.chart-of-accounts.update', $account) }}" method="POST" class="admin-modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="admin_edit_modal" value="{{ $modalKey }}">

                    <div class="admin-modal-grid">
                        <div class="admin-modal-field">
                            <label for="account-code-{{ $account->id }}">ລະຫັດບັນຊີ *</label>
                            <input type="text" name="account_code" id="account-code-{{ $account->id }}" value="{{ old('admin_edit_modal') === $modalKey ? old('account_code') : $account->account_code }}" placeholder="ລະຫັດບັນຊີ">
                            @error('account_code')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-field">
                            <label for="account-name-{{ $account->id }}">ຊື່ບັນຊີ *</label>
                            <input type="text" name="account_name" id="account-name-{{ $account->id }}" value="{{ old('admin_edit_modal') === $modalKey ? old('account_name') : $account->account_name }}" placeholder="ຊື່ບັນຊີ">
                            @error('account_name')<span class="admin-modal-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="admin-modal-field admin-modal-field-full">
                            <label for="parent-id-{{ $account->id }}">ບັນຊີແມ່ / parent_id</label>
                            <select name="parent_id" id="parent-id-{{ $account->id }}">
                                <option value="">ໝວດໃຫຍ່ (ບໍ່ມີ parent_id)</option>
                                @foreach($parentAccounts as $parent)
                                    @continue($parent->id === $account->id)
                                    <option value="{{ $parent->id }}" {{ (string) (old('admin_edit_modal') === $modalKey ? old('parent_id') : $account->parent_id) === (string) $parent->id ? 'selected' : '' }}>
                                        {{ $parent->account_code }} - {{ $parent->account_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')<span class="admin-modal-error">{{ $message }}</span>@enderror
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
