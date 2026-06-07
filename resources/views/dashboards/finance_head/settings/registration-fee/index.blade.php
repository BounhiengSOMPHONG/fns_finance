@extends('layouts.admin')

@section('title', 'ຄ່າລົງທະບຽນ')
@section('page-title', 'ການຕັ້ງຄ່າລົງທະບຽນ')

@section('content')

<style>
/* ── Registration-fee settings — two fixed types, calm & clear ── */
.rf-wrap { width: 100%; }
.rf-intro { font-size: 0.8rem; color: var(--fns-gray-400); margin-bottom: 1.1rem; }

.rf-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 1.15rem; }

.rf-card {
    position: relative; overflow: hidden;
    background: #fff; border: 1px solid var(--fns-gray-200); border-radius: 14px;
    padding: 1.3rem 1.4rem; box-shadow: 0 2px 12px rgba(26,39,68,0.06);
}
.rf-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--rf-accent); }

/* header: type badge + edit */
.rf-hd { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem; }
.rf-edit {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-family: inherit; font-size: 0.78rem; font-weight: 600; color: var(--fns-navy);
    padding: 0.4rem 0.8rem; border: 1px solid var(--fns-gray-200); border-radius: 8px;
    background: #fff; text-decoration: none; transition: all .15s;
}
.rf-edit:hover { background: var(--fns-gray-100); border-color: #b0aead; }
.rf-edit svg { width: 14px; height: 14px; }

/* hero total */
.rf-total-label { font-size: 0.66rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--fns-gray-400); margin-bottom: 0.2rem; }
.rf-total { display: flex; align-items: baseline; gap: 0.4rem; }
.rf-total-num { font-family: 'Cinzel', serif; font-size: 1.85rem; font-weight: 700; color: var(--fns-navy); line-height: 1; }
.rf-total-unit { font-size: 0.85rem; color: var(--fns-gray-400); }
.rf-meta { font-size: 0.74rem; color: var(--fns-gray-400); margin-top: 0.45rem; display: flex; flex-wrap: wrap; gap: 0.2rem 0.55rem; }
.rf-meta b { color: var(--fns-gray-600); font-weight: 600; }

/* items breakdown */
.rf-items-label { font-size: 0.66rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--fns-gray-400); margin: 1.1rem 0 0.25rem; }
.rf-item {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.5rem 0.1rem; border-bottom: 1px solid var(--fns-gray-200);
}
.rf-item:last-child { border-bottom: none; }
.rf-item-name { flex: 1; min-width: 0; font-size: 0.85rem; color: #374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rf-pct { flex-shrink: 0; font-size: 0.66rem; font-weight: 600; color: var(--fns-gold); background: rgba(201,153,26,0.1); border-radius: 6px; padding: 0.1rem 0.45rem; }
.rf-pct.zero { color: var(--fns-gray-400); background: var(--fns-gray-100); }
.rf-amt { flex-shrink: 0; font-family: 'Cinzel', serif; font-size: 0.92rem; font-weight: 700; color: var(--fns-navy); font-variant-numeric: tabular-nums; }
.rf-amt small { font-family: inherit; font-weight: 400; font-size: 0.66rem; color: var(--fns-gray-400); margin-left: 0.15rem; }

.rf-empty { text-align: center; padding: 2.5rem; color: var(--fns-gray-400); }
.rf-empty div { font-size: 2rem; opacity: 0.2; margin-bottom: 0.6rem; }
.rf-modal {
    position: fixed; inset: 0; z-index: 1000; display: none; align-items: center; justify-content: center;
    padding: 1rem; background: rgba(15, 23, 42, 0.48);
}
.rf-modal.is-open { display: flex; }
.rf-modal-panel {
    width: min(760px, 100%); max-height: calc(100vh - 2rem); overflow: auto;
    border-radius: 16px; border: 1px solid var(--fns-gray-200); background: #fff;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
}
.rf-modal-head {
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    padding: 1rem 1.15rem; border-bottom: 1px solid var(--fns-gray-200); background: #fbfbfc;
}
.rf-modal-head h2 { margin: 0; color: var(--fns-navy); font-size: 1rem; font-weight: 900; }
.rf-modal-close { border: 0; background: transparent; color: var(--fns-gray-500); font-size: 1.45rem; line-height: 1; cursor: pointer; }
.rf-modal-body { padding: 1.15rem; }
.rf-modal-actions { display: flex; justify-content: flex-end; gap: .55rem; margin-top: 1.25rem; }
.rf-items-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.rf-items-table th { text-align: left; color: var(--fns-gray-500); font-size: .72rem; padding: .45rem; border-bottom: 1px solid var(--fns-gray-200); }
.rf-items-table td { padding: .45rem; border-bottom: 1px solid var(--fns-gray-200); }
</style>

<div class="rf-wrap">
    <p class="rf-intro">ຕັ້ງຄ່າຄ່າລົງທະບຽນ ສຳລັບ ນ/ສ ປີ 1 (ໃໝ່) ແລະ ປີ 2–4 — ມີ 2 ປະເພດຄົງທີ່, ແກ້ໄຂໄດ້ຢ່າງດຽວ</p>

    <div class="rf-grid">
        @forelse($settings as $s)
        @php
            $isY1    = $s->section_type === 'year1';
            $accent  = $isY1 ? 'var(--fns-green-mid)' : 'var(--fns-navy-light)';
            $badge   = $isY1 ? 'fns-badge-green' : 'fns-badge-blue';
        @endphp
        <div class="rf-card" style="--rf-accent:{{ $accent }};">
            <div class="rf-hd">
                <span class="fns-badge {{ $badge }}" style="font-size:0.8rem; padding:0.3rem 0.85rem;">
                    {{ \App\Models\RegistrationFeeSetting::sectionLabel($s->section_type) }}
                </span>
                <button type="button"
                        class="rf-edit js-rf-edit"
                        data-id="{{ $s->id }}"
                        data-url="{{ route('head_of_finance.settings.registration-fee.update', $s) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/></svg>
                    ແກ້ໄຂ
                </button>
            </div>

            <div class="rf-total-label">ລວມຄ່າລົງທະບຽນ</div>
            <div class="rf-total">
                <span class="rf-total-num">{{ number_format($s->total_rate, 0) }}</span>
                <span class="rf-total-unit">ກີບ</span>
            </div>
            <div class="rf-meta">
                <span>ປີທີ່ເລີ່ມໃຊ້ <b>{{ $s->start_year }}</b></span>
                @if($s->gov_doc_id)<span>· ເອກະສານ <b>{{ $s->gov_doc_id }}</b></span>@endif
                <span>· {{ $s->items->count() }} ລາຍການ</span>
            </div>

            <div class="rf-items-label">ລາຍລະອຽດ</div>
            @foreach($s->items as $item)
            <div class="rf-item">
                <span class="rf-item-name" title="{{ $item->name }}">{{ $item->name }}</span>
                <span class="rf-pct {{ $item->nuol_pct > 0 ? '' : 'zero' }}">ມຊ {{ rtrim(rtrim(number_format($item->nuol_pct * 100, 2), '0'), '.') }}%</span>
                <span class="rf-amt">{{ number_format($item->amount, 0) }}<small>ກີບ</small></span>
            </div>
            @endforeach
        </div>
        @empty
        <div class="rf-card" style="--rf-accent:var(--fns-gray-200); grid-column:1/-1;">
            <div class="rf-empty">
                <div>💰</div>
                <p>ຍັງບໍ່ມີຂໍ້ມູນຄ່າລົງທະບຽນ</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<div id="rf-modal" class="rf-modal" aria-hidden="true">
    <div class="rf-modal-panel" role="dialog" aria-modal="true" aria-labelledby="rf-modal-title">
        <div class="rf-modal-head">
            <h2 id="rf-modal-title">ແກ້ໄຂຄ່າລົງທະບຽນ</h2>
            <button type="button" class="rf-modal-close" data-rf-close>&times;</button>
        </div>
        <form method="POST" id="rf-modal-form" class="rf-modal-body">
            @csrf
            @method('PUT')

            <div class="fns-form-group">
                <label class="fns-label">ປະເພດ <span style="color:red;">*</span></label>
                <select name="section_type" id="rf-section-type" class="fns-input" required>
                    <option value="year2_4">ນ/ສ ປີ 2-4</option>
                    <option value="year1">ນ/ສ ປີ 1 (ໃໝ່)</option>
                </select>
            </div>

            <div class="fns-form-group">
                <label class="fns-label">ເລກທີເອກະສານອ້າງອີງ</label>
                <input type="text" name="gov_doc_id" id="rf-gov-doc" class="fns-input">
            </div>

            <div class="fns-form-group">
                <label class="fns-label">ປີທີ່ເລີ່ມໃຊ້ <span style="color:red;">*</span></label>
                <input type="number" name="start_year" id="rf-start-year" min="2000" max="2100" class="fns-input" required>
            </div>

            <div class="fns-form-group" style="margin-top:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <label class="fns-label" style="margin-bottom:0;">ລາຍການຄ່າໃຊ້ຈ່າຍ <span style="color:red;">*</span></label>
                    <button type="button" id="rf-add-item" class="fns-btn fns-btn-sm fns-btn-secondary">+ ເພີ່ມລາຍການ</button>
                </div>
                <table class="rf-items-table">
                    <thead>
                        <tr>
                            <th>ລາຍການ</th>
                            <th style="width:150px;">ຈຳນວນ (ກີບ)</th>
                            <th style="width:130px;">% ມຊ</th>
                            <th style="width:70px;"></th>
                        </tr>
                    </thead>
                    <tbody id="rf-items-body"></tbody>
                </table>
            </div>

            <div class="rf-modal-actions">
                <button type="button" class="fns-btn fns-btn-secondary" data-rf-close>ຍົກເລີກ</button>
                <button type="submit" class="fns-btn fns-btn-primary">ອັບເດດ</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const settings = @json($settings->mapWithKeys(fn ($s) => [$s->id => [
        'section_type' => $s->section_type,
        'gov_doc_id' => $s->gov_doc_id,
        'start_year' => $s->start_year,
        'items' => $s->items->map(fn ($item) => [
            'name' => $item->name,
            'amount' => (float) $item->amount,
            'nuol_pct' => rtrim(rtrim(number_format($item->nuol_pct * 100, 2, '.', ''), '0'), '.'),
        ])->values(),
    ]]));

    const modal = document.getElementById('rf-modal');
    const form = document.getElementById('rf-modal-form');
    const sectionType = document.getElementById('rf-section-type');
    const govDoc = document.getElementById('rf-gov-doc');
    const startYear = document.getElementById('rf-start-year');
    const tbody = document.getElementById('rf-items-body');
    let rowIndex = 0;

    function escHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    }

    function addRow(data = {}) {
        const index = rowIndex++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="items[${index}][name]" value="${escHtml(data.name ?? '')}" class="fns-input" required></td>
            <td><input type="number" name="items[${index}][amount]" value="${escHtml(data.amount ?? '')}" class="fns-input" min="0" step="0.01" required></td>
            <td><input type="number" name="items[${index}][nuol_pct]" value="${escHtml(data.nuol_pct ?? '0')}" class="fns-input" min="0" max="100" step="0.01" required></td>
            <td><button type="button" class="fns-btn fns-btn-sm fns-btn-danger">ລຶບ</button></td>
        `;
        tr.querySelector('button').addEventListener('click', () => tr.remove());
        tbody.appendChild(tr);
    }

    function openModal(button) {
        const data = settings[button.dataset.id];
        if (!data) return;

        form.action = button.dataset.url;
        sectionType.value = data.section_type || 'year2_4';
        govDoc.value = data.gov_doc_id || '';
        startYear.value = data.start_year || @json(date('Y'));
        tbody.innerHTML = '';
        rowIndex = 0;
        (data.items.length ? data.items : [{ name: '', amount: '', nuol_pct: '0' }]).forEach(addRow);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        setTimeout(() => sectionType.focus(), 50);
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('click', event => {
        const edit = event.target.closest('.js-rf-edit');
        if (edit) {
            openModal(edit);
            return;
        }

        if (event.target.matches('[data-rf-close]') || event.target === modal) closeModal();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
    document.getElementById('rf-add-item').addEventListener('click', () => addRow());
});
</script>
@endpush

@endsection
