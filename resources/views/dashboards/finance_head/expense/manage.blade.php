@extends('layouts.admin')

@section('title', 'ຈັດການງົບປະມານ ສົກ ' . $expensePlan->fiscal_year)
@section('page-title', 'ຈັດປະເມີນລາຍຈ່າຍ ສົກ ' . $expensePlan->fiscal_year)

@section('content')

@if(session('success'))
<div class="fns-alert fns-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="fns-alert fns-alert-danger">{{ session('error') }}</div>
@endif

{{-- Plan header bar --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:1.2rem;flex-wrap:wrap;">
    <a href="{{ route('head_of_finance.expense.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">← ກັບຄືນ</a>
    <span style="font-size:1rem;font-weight:700;color:var(--fns-navy);">ສົກ {{ $expensePlan->fiscal_year }}</span>
    <span style="margin-left:auto;font-size:0.85rem;color:#64748b;">
        ງົບລວມ: <strong>{{ number_format($expensePlan->allCategories->flatMap->items->sum('annual_amount'), 0) }} ກີບ</strong>
    </span>
</div>

{{-- Tree --}}
@include('dashboards.finance_head.expense._tree', [
    'topCategories'  => $expensePlan->topCategories,
    'plan'           => $expensePlan,
    'editable'       => true,
    'chartOfAccounts'=> $chartOfAccounts,
])

{{-- Add main category button --}}
<div style="margin-top:1rem;">
    <button class="fns-btn fns-btn-primary" onclick="openCatModal({{ $expensePlan->id }}, null, null)">
        + ເພີ່ມໝວດຫຼັກ
    </button>
</div>

{{-- ======================= MODALS ======================= --}}

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
            <div class="fns-form-group">
                <label class="fns-label">ຮູບແບບ Formula</label>
                <select name="formula_type" id="editCatFormulaType" class="fns-input">
                    <option value="AB">AB — 2 ປັດໄຈ (col_a × col_b)</option>
                    <option value="ABC">ABC — 3 ປັດໄຈ (col_a × col_b × col_c)</option>
                </select>
            </div>
            <div class="fns-form-group">
                <label class="fns-label">ຊື່ Column (ເວັ້ນວ່າງ = ໃຊ້ default)</label>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
                    <input type="text" name="col_a_label" id="editCatColA" class="fns-input" placeholder="ຕໍ່ເດືອນ (ກີບ)">
                    <input type="text" name="col_b_label" id="editCatColB" class="fns-input" placeholder="ຈ/ນ">
                    <input type="text" name="col_c_label" id="editCatColC" class="fns-input" placeholder="ຄັ້ງ">
                </div>
                <p style="font-size:0.72rem;color:#94a3b8;margin:4px 0 0;">Col A &nbsp;|&nbsp; Col B &nbsp;|&nbsp; Col C (ABC ເທົ່ານັ້ນ)</p>
            </div>
            <div style="display:flex;gap:8px;margin-top:1rem;">
                <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                <button type="button" class="fns-btn fns-btn-secondary" onclick="closeEditCatModal()">ຍົກເລີກ</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Category modal --}}
<div id="catModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:1.5rem;width:480px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;font-size:1rem;" id="catModalTitle">ເພີ່ມໝວດ</h3>
        <form method="POST" action="{{ route('head_of_finance.expense-categories.store') }}" id="catForm">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $expensePlan->id }}">
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
            <div class="fns-form-group">
                <label class="fns-label">ຮູບແບບ Formula</label>
                <select name="formula_type" id="catFormulaType" class="fns-input">
                    <option value="AB">AB — 2 ປັດໄຈ (col_a × col_b)</option>
                    <option value="ABC">ABC — 3 ປັດໄຈ (col_a × col_b × col_c)</option>
                </select>
            </div>
            <div class="fns-form-group">
                <label class="fns-label">ຊື່ Column (ເວັ້ນວ່າງ = ໃຊ້ default)</label>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
                    <input type="text" name="col_a_label" class="fns-input" placeholder="ຕໍ່ເດືອນ (ກີບ)">
                    <input type="text" name="col_b_label" class="fns-input" placeholder="ຈ/ນ">
                    <input type="text" name="col_c_label" class="fns-input" placeholder="ຄັ້ງ">
                </div>
                <p style="font-size:0.72rem;color:#94a3b8;margin:4px 0 0;">Col A &nbsp;|&nbsp; Col B &nbsp;|&nbsp; Col C (ABC ເທົ່ານັ້ນ)</p>
            </div>
            <div style="display:flex;gap:8px;margin-top:1rem;">
                <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
                <button type="button" class="fns-btn fns-btn-secondary" onclick="closeCatModal()">ຍົກເລີກ</button>
            </div>
        </form>
    </div>
</div>

<script>
// ===== Category modals =====
function openCatModal(planId, parentId, parentRef) {
    document.getElementById('catParentId').value = parentId ?? '';
    document.getElementById('catRefCode').value = parentRef ? parentRef + '.' : '';
    document.getElementById('catName').value = '';
    document.getElementById('catModalTitle').textContent = parentId ? 'ເພີ່ມໝວດຍ່ອຍ' : 'ເພີ່ມໝວດຫຼັກ';
    document.getElementById('catModal').style.display = 'flex';
}
function closeCatModal() { document.getElementById('catModal').style.display = 'none'; }

function openEditCatModal(id, refCode, name, sortOrder, formulaType, colA, colB, colC) {
    document.getElementById('editCatForm').action = '/head-of-finance/expense-categories/' + id;
    document.getElementById('editCatRefCode').value = refCode;
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatSortOrder').value = sortOrder;
    document.getElementById('editCatFormulaType').value = formulaType || 'AB';
    document.getElementById('editCatColA').value = colA || '';
    document.getElementById('editCatColB').value = colB || '';
    document.getElementById('editCatColC').value = colC || '';
    document.getElementById('editCatModal').style.display = 'flex';
}
function closeEditCatModal() { document.getElementById('editCatModal').style.display = 'none'; }

</script>

@endsection
