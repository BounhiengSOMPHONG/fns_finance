@extends('layouts.admin')

@section('title', 'ແຜນບັນຊີ')
@section('page-title', 'ຈັດການແຜນບັນຊີ')

@section('content')
<div class="table-wrapper">
    <div class="table-header">
        <span class="table-title">ລາຍການບັນຊີ</span>
        <a href="{{ route('admin.chart-of-accounts.create') }}" class="btn btn-primary">
            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            ເພີ່ມບັນຊີ
        </a>
    </div>

    <div class="filter-bar">
        <form id="filter-form" method="GET" action="{{ route('admin.chart-of-accounts.index') }}" style="display:flex; flex-wrap:wrap; gap:12px; width:100%;">
            <div style="flex:1; min-width:200px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ຄົ້ນຫາລະຫັດ ຫຼື ຊື່ບັນຊີ..." class="form-input" onkeydown="if(event.key==='Enter'){this.form.submit();}">
            </div>
            <select name="parent_id" onchange="this.form.submit()" class="form-input" style="width:auto; min-width:220px;">
                <option value="">-- ທຸກບັນຊີຫຼັກ --</option>
                @foreach($parentAccounts as $parent)
                    <option value="{{ $parent->id }}" {{ request('parent_id') != '' && request('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->account_code }} - {{ $parent->account_name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ລະຫັດບັນຊີ</th>
                <th>ຊື່ບັນຊີ</th>
                <th>ບັນຊີຫຼັກ</th>
                <th style="text-align:right">ການດໍາເນີນງານ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($chartOfAccounts as $account)
                <tr>
                    <td>{{ $account->id }}</td>
                    <td style="font-family:monospace; font-weight:500">{{ $account->account_code }}</td>
                    <td>{{ $account->account_name }}</td>
                    <td style="color:var(--color-text-secondary)">
                        @if($account->parent)
                            {{ $account->parent->account_code }} - {{ $account->parent->account_name }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align:right">
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:4px;">
                            <a href="{{ route('admin.chart-of-accounts.show', $account) }}" class="btn-icon" title="ເບິ່ງລາຍລະອຽດ">
                                <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('admin.chart-of-accounts.edit', $account) }}" class="btn-icon" title="ແກ້ໄຂ">
                                <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button" onclick="openDeleteModal('{{ route('admin.chart-of-accounts.destroy', $account) }}', '{{ $account->account_code }} - {{ $account->account_name }}')" class="btn-icon" title="ລົບ" style="color:#DC2626;">
                                <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty-state"><div class="empty-title">ບໍ່ພົບຂໍ້ມູນບັນຊີ</div></div></td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($chartOfAccounts->hasPages())
        <div style="padding:16px 20px; border-top:1px solid var(--color-border);">{{ $chartOfAccounts->links() }}</div>
    @endif
</div>

<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body" style="text-align:center; padding:28px 24px;">
            <div style="width:48px;height:48px;border-radius:50%;background:var(--color-danger-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg style="width:24px;height:24px;color:#DC2626" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 style="font-size:var(--font-size-lg);font-weight:600;color:var(--color-text-primary);margin-bottom:8px;">ຢືນຢັນການລົບ</h3>
            <p style="font-size:var(--font-size-base);color:var(--color-text-secondary);margin-bottom:8px;">ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບບັນຊີນີ້?</p>
            <span id="deleteItemName" style="display:inline-block;padding:4px 12px;background:var(--color-danger-bg);color:var(--color-danger-text);font-size:var(--font-size-sm);font-weight:500;border-radius:var(--radius-md);border:1px solid var(--color-danger-border);"></span>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">ຍົກເລີກ</button>
            <form id="deleteForm" method="POST" style="margin:0;">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">ຢືນຢັນລົບ</button></form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openDeleteModal(u, n) { document.getElementById('deleteForm').action=u; document.getElementById('deleteItemName').textContent=n; document.getElementById('deleteModal').style.display='flex'; }
function closeDeleteModal() { document.getElementById('deleteModal').style.display='none'; }
document.getElementById('deleteModal')?.addEventListener('click', function(e) { if(e.target===this) closeDeleteModal(); });
</script>
@endpush
@endsection
