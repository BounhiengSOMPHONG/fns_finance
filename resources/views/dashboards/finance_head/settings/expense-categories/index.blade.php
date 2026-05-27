@extends('layouts.admin')

@section('title', 'ຕັ້ງຄ່າໝວດລາຍຈ່າຍ')
@section('page-title', 'ຕັ້ງຄ່າໝວດລາຍຈ່າຍ (ແມ່ແບບ)')

@section('content')

@if(session('success'))
<div class="fns-alert fns-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="fns-alert fns-alert-danger">{{ session('error') }}</div>
@endif

<p style="font-size:0.85rem;color:#64748b;margin:0 0 1.2rem;max-width:720px;">
    ກຳນົດໝວດຫຼັກ ແລະ ໝວດຍ່ອຍມາດຕະຖານໄວ້ບ່ອນດຽວ. ເມື່ອສ້າງແຜນປະເມີນລາຍຈ່າຍສົກປີໃໝ່ ລະບົບຈະສຳເນົາໝວດເຫຼົ່ານີ້ໃຫ້ອັດຕະໂນມັດ
    (ການແກ້ໄຂແມ່ແບບບໍ່ກະທົບແຜນທີ່ສ້າງໄປແລ້ວ).
</p>

@if($templates->isEmpty())
<div class="fns-card" style="padding:2rem;text-align:center;color:#94a3b8;">
    ຍັງບໍ່ມີໝວດໃນແມ່ແບບ. ກົດ "ເພີ່ມໝວດຫຼັກ" ເພື່ອເລີ່ມຕົ້ນ.
</div>
@else
@include('dashboards.finance_head.settings.expense-categories._tree', [
    'nodes' => $templates,
    'depth' => 0,
])
@endif

{{-- Add main category button --}}
<div style="margin-top:1rem;">
    <button type="button" class="fns-btn fns-btn-primary" onclick="openCatModal(null, null)">
        + ເພີ່ມໝວດຫຼັກ
    </button>
</div>

{{-- ======================= MODALS ======================= --}}

{{-- Add Category modal --}}
<div id="catModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;width:480px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;font-size:1rem;" id="catModalTitle">ເພີ່ມໝວດ</h3>
        <form method="POST" action="{{ route('head_of_finance.settings.expense-categories.store') }}" id="catForm">
            @csrf
            <input type="hidden" name="parent_id" id="catParentId">
            <div class="fns-form-group">
                <label class="fns-label">ລະຫັດ (ref_code) <span style="color:red">*</span></label>
                <input type="text" name="ref_code" id="catRefCode" class="fns-input" placeholder="ເຊັ່ນ 2.1 ຫຼື 2.1.1" required>
            </div>
            <div class="fns-form-group">
                <label class="fns-label">ຊື່ໝວດ <span style="color:red">*</span></label>
                <input type="text" name="name" id="catName" class="fns-input" required>
            </div>
            <div class="fns-form-group">
                <label class="fns-label">ລຳດັບ (sort_order)</label>
                <input type="number" name="sort_order" id="catSortOrder" class="fns-input" value="0" min="0">
            </div>
            <div style="display:flex;gap:8px;margin-top:1rem;">
                <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                <button type="button" class="fns-btn fns-btn-secondary" onclick="closeCatModal()">ຍົກເລີກ</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Category modal --}}
<div id="editCatModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;width:480px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;font-size:1rem;">ແກ້ໄຂໝວດ</h3>
        <form method="POST" id="editCatForm">
            @csrf @method('PATCH')
            <div class="fns-form-group">
                <label class="fns-label">ລະຫັດ (ref_code) <span style="color:red">*</span></label>
                <input type="text" name="ref_code" id="editCatRefCode" class="fns-input" required>
            </div>
            <div class="fns-form-group">
                <label class="fns-label">ຊື່ໝວດ <span style="color:red">*</span></label>
                <input type="text" name="name" id="editCatName" class="fns-input" required>
            </div>
            <div class="fns-form-group">
                <label class="fns-label">ລຳດັບ (sort_order)</label>
                <input type="number" name="sort_order" id="editCatSortOrder" class="fns-input" min="0">
            </div>
            <div style="display:flex;gap:8px;margin-top:1rem;">
                <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                <button type="button" class="fns-btn fns-btn-secondary" onclick="closeEditCatModal()">ຍົກເລີກ</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCatModal(parentId, parentRef) {
    document.getElementById('catParentId').value = parentId ?? '';
    document.getElementById('catRefCode').value = parentRef ? parentRef + '.' : '';
    document.getElementById('catName').value = '';
    document.getElementById('catSortOrder').value = '0';
    document.getElementById('catModalTitle').textContent = parentId ? 'ເພີ່ມໝວດຍ່ອຍ' : 'ເພີ່ມໝວດຫຼັກ';
    document.getElementById('catModal').style.display = 'flex';
}
function closeCatModal() { document.getElementById('catModal').style.display = 'none'; }

function openEditCatModal(id, refCode, name, sortOrder) {
    document.getElementById('editCatForm').action = '{{ url('head-of-finance/settings/expense-categories') }}/' + id;
    document.getElementById('editCatRefCode').value = refCode;
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatSortOrder').value = sortOrder;
    document.getElementById('editCatModal').style.display = 'flex';
}
function closeEditCatModal() { document.getElementById('editCatModal').style.display = 'none'; }

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openCatModal(null, null));
@endif
</script>

@endsection
