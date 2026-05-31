@extends('layouts.admin')

@section('title', 'ເງິນເດືອນ ເດືອນ ' . $salaryPlan->monthLabel())
@section('page-title', 'ຕາຕະລາງສັງລວມລາຍຈ່າຍເງິນເດືອນ')

@section('content')

{{-- Plan header --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:1rem;flex-wrap:wrap;">
    <a href="{{ route('head_of_finance.salary.index') }}" class="fns-btn fns-btn-secondary fns-btn-sm">← ກັບຄືນ</a>
    <span style="font-weight:700;color:var(--fns-navy);font-size:1rem;">
        ເດືອນ {{ $salaryPlan->monthLabel() }}
    </span>
    <span style="margin-left:auto;font-size:0.83rem;color:#64748b;">
        ລວມ 12 ເດືອນ:
        <strong id="grand-annual" style="color:var(--fns-navy);">{{ number_format($salaryPlan->grandTotal(), 0) }}</strong> ກີບ
    </span>
</div>

{{-- ──────────── PAYROLL TABLE ──────────── --}}
<div style="overflow-x:auto;">
<table id="salary-table" style="width:100%;border-collapse:collapse;font-size:0.8rem;min-width:860px;">
    <colgroup>
        <col style="width:36px">  {{-- ພ --}}
        <col style="width:36px">  {{-- ພສ --}}
        <col style="width:36px">  {{-- ຮ່ວງ --}}
        <col style="width:36px">  {{-- ລຮ --}}
        <col>                     {{-- ເນື້ອໃນລາຍຈ່າຍ --}}
        <col style="width:52px">  {{-- ຈຳນວນ ພິນ --}}
        <col style="width:130px"> {{-- ໂອນ ATM --}}
        <col style="width:115px"> {{-- ຖອນສົດ --}}
        <col style="width:130px"> {{-- ລວມ --}}
        <col style="width:140px"> {{-- ລວມ 12 ເດືອນ --}}
    </colgroup>
    <thead>
        <tr style="background:var(--fns-navy);color:#fff;font-size:0.72rem;">
            <th colspan="4" style="text-align:center;padding:6px 4px;border-right:1px solid rgba(255,255,255,0.2);">ສາລະບານງົບປະມານ</th>
            <th rowspan="2" style="padding:6px 8px;border-right:1px solid rgba(255,255,255,0.2);text-align:left;">ເນື້ອໃນລາຍຈ່າຍ</th>
            <th rowspan="2" style="padding:6px 4px;border-right:1px solid rgba(255,255,255,0.2);text-align:center;">ຈຳນວນ<br>ພິນ</th>
            <th colspan="3" style="text-align:center;padding:6px 4px;border-right:1px solid rgba(255,255,255,0.2);">ຈຳນວນເງິນຖອນຕົວຈິງໃນ 1 ເດືອນ</th>
            <th rowspan="2" style="padding:6px 6px;text-align:right;">ລວມ 12 ເດືອນ</th>
        </tr>
        <tr style="background:#1e3a5f;color:#fff;font-size:0.7rem;">
            <th style="text-align:center;padding:4px 2px;border-right:1px solid rgba(255,255,255,0.1);">ພ</th>
            <th style="text-align:center;padding:4px 2px;border-right:1px solid rgba(255,255,255,0.1);">ພສ</th>
            <th style="text-align:center;padding:4px 2px;border-right:1px solid rgba(255,255,255,0.1);">ຮ່ວງ</th>
            <th style="text-align:center;padding:4px 2px;border-right:1px solid rgba(255,255,255,0.2);">ລຮ</th>
            <th style="padding:4px 4px;border-right:1px solid rgba(255,255,255,0.1);text-align:right;">ໂອນເຂົ້າ ATM</th>
            <th style="padding:4px 4px;border-right:1px solid rgba(255,255,255,0.1);text-align:right;">ຖອນເງິນສົດ</th>
            <th style="padding:4px 4px;border-right:1px solid rgba(255,255,255,0.2);text-align:right;">ລວມ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($roots as $root)
            @include('dashboards.finance_head.salary._rows', [
                'node'     => $root,
                'depth'    => 0,
                'entryMap' => $entries,
                'nodeAgg'  => $nodeAgg,
                'editable' => !$salaryPlan->isApproved(),
                'planId'   => $salaryPlan->id,
            ])
        @endforeach

        {{-- Grand total row --}}
        @php
            $grandMonthly = $salaryPlan->entries->sum('monthly_total');
            $grandAtm     = $salaryPlan->entries->sum('atm_amount');
            $grandCash    = $salaryPlan->entries->sum('cash_amount');
            $grandAnnual  = $salaryPlan->entries->sum('annual_amount');
        @endphp
        <tr style="background:#1e3a5f;color:#fff;font-weight:700;font-size:0.8rem;">
            <td colspan="4" style="border-right:1px solid rgba(255,255,255,0.2);"></td>
            <td style="padding:7px 8px;border-right:1px solid rgba(255,255,255,0.2);">ລວມຍອດເງິນໄດ້ຮັບທັງໝົດ:</td>
            <td style="text-align:center;padding:7px 4px;border-right:1px solid rgba(255,255,255,0.1);">{{ $salaryPlan->entries->sum('person_count') > 0 ? number_format($salaryPlan->entries->sum('person_count'), 0) : '' }}</td>
            <td style="text-align:right;padding:7px 6px;border-right:1px solid rgba(255,255,255,0.1);">{{ number_format($grandAtm, 0) }}</td>
            <td style="text-align:right;padding:7px 6px;border-right:1px solid rgba(255,255,255,0.1);">{{ number_format($grandCash, 0) }}</td>
            <td style="text-align:right;padding:7px 6px;border-right:1px solid rgba(255,255,255,0.2);">{{ number_format($grandMonthly, 0) }}</td>
            <td style="text-align:right;padding:7px 6px;" id="footer-annual">{{ number_format($grandAnnual, 0) }}</td>
        </tr>
    </tbody>
</table>
</div>

{{-- Sticky save bar --}}
@if(!$salaryPlan->isApproved())
<div class="sal-save-bar">
    <span class="sal-save-hint">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        ການປ້ອນຂໍ້ມູນຖືກບັນທຶກອັດຕະໂນມັດເມື່ອກົດ Enter ຫຼື Tab — ກົດປຸ່ມລຸ່ມເພື່ອບັນທຶກທັງໝົດ
    </span>
    <span class="sal-save-status" id="sal-save-status"></span>
    <button type="button" id="sal-save-all" class="sal-save-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
        ບັນທຶກທັງໝົດ
    </button>
</div>

<style>
.sal-save-bar {
    position: sticky;
    bottom: 0;
    z-index: 30;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
    padding: 0.85rem 1.1rem;
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(8px);
    border: 1px solid var(--fns-gray-200);
    border-radius: 12px;
    box-shadow: 0 -4px 14px -10px rgba(17,27,51,0.2);
}
.sal-save-hint {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.74rem;
    color: var(--fns-gray-600);
}
.sal-save-hint svg { width: 14px; height: 14px; color: var(--fns-gold); }
.sal-save-status {
    margin-left: auto;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--fns-gray-400);
    min-width: 0;
}
.sal-save-status.is-success { color: #166534; }
.sal-save-status.is-error   { color: #b91c1c; }
.sal-save-status.is-progress { color: var(--fns-navy); }
.sal-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-family: inherit;
    font-size: 0.85rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    background: var(--fns-gold);
    color: var(--fns-navy-deep);
    box-shadow: 0 4px 14px -4px rgba(201,153,26,0.55);
    transition: background .15s, transform .1s, box-shadow .15s;
}
.sal-save-btn:hover {
    background: var(--fns-gold-light, #e7be4f);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px -4px rgba(201,153,26,0.7);
}
.sal-save-btn:disabled {
    background: var(--fns-gray-200);
    color: var(--fns-gray-400);
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}
.sal-save-btn svg { width: 16px; height: 16px; }
</style>
@endif

@push('scripts')
<script>
(function () {
    const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
    const editable = {{ !$salaryPlan->isApproved() ? 'true' : 'false' }};
    if (!editable) return;

    // ── helpers ──────────────────────────────────────────────────
    function fmt(n) {
        return parseFloat(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function recalcRow(row) {
        const atm   = parseFloat(row.querySelector('.si-atm')?.value  || 0) || 0;
        const cash  = parseFloat(row.querySelector('.si-cash')?.value || 0) || 0;
        const mode  = row.dataset.annualMode || 'x12';
        const total = atm + cash;
        let annual  = 0;

        if (mode === 'x12')    annual = total * 12;
        else if (mode === 'x1') annual = total;
        else if (mode === 'direct') {
            annual = parseFloat(row.querySelector('.si-annual')?.value || 0) || 0;
        }

        const cellTotal  = row.querySelector('.cell-total');
        const cellAnnual = row.querySelector('.cell-annual');
        if (cellTotal)  cellTotal.textContent  = fmt(total);
        if (cellAnnual) cellAnnual.textContent  = fmt(annual);

        return { atm, cash, annual };
    }

    function recalcAllParents() {
        // Recalculate all .summary-row totals bottom-up
        document.querySelectorAll('.summary-row').forEach(row => {
            const nodeId = row.dataset.nodeId;
            let atm = 0, cash = 0, total = 0, annual = 0;
            document.querySelectorAll(`.leaf-row[data-parent-chain*=",${nodeId},"]`).forEach(lr => {
                atm    += parseFloat(lr.dataset.atm    || 0);
                cash   += parseFloat(lr.dataset.cash   || 0);
                total  += parseFloat(lr.dataset.monthly || 0);
                annual += parseFloat(lr.dataset.annual  || 0);
            });
            const r = row.querySelector('.sum-atm');
            if (r) r.textContent = atm   > 0 ? fmt(atm)   : '';
            const c = row.querySelector('.sum-cash');
            if (c) c.textContent = cash  > 0 ? fmt(cash)  : '';
            const t = row.querySelector('.sum-total');
            if (t) t.textContent = total > 0 ? fmt(total) : '';
            const a = row.querySelector('.sum-annual');
            if (a) a.textContent = annual > 0 ? fmt(annual) : '';
        });

        // Footer
        let grandAnnual = 0;
        document.querySelectorAll('.leaf-row').forEach(lr => {
            grandAnnual += parseFloat(lr.dataset.annual || 0);
        });
        const fa = document.getElementById('footer-annual');
        if (fa) fa.textContent = fmt(grandAnnual);
    }

    async function saveEntry(row) {
        const entryId = row.dataset.entryId;
        if (!entryId) return;

        const mode    = row.dataset.annualMode || 'x12';
        const atm     = parseFloat(row.querySelector('.si-atm')?.value  || 0) || 0;
        const cash    = parseFloat(row.querySelector('.si-cash')?.value || 0) || 0;
        const persons = parseInt(row.querySelector('.si-persons')?.value || 0) || 0;
        const remark  = row.querySelector('.si-remark')?.value || '';

        const payload = { person_count: persons, atm_amount: atm, cash_amount: cash, remark };

        if (mode === 'direct') {
            payload.annual_amount = parseFloat(row.querySelector('.si-annual')?.value || 0) || 0;
        }

        row.style.opacity = '0.6';
        row.style.pointerEvents = 'none';

        try {
            const res  = await fetch(`/head-of-finance/salary-entries/${entryId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Error');

            // Update cached data attributes for parent recalc
            row.dataset.atm     = atm;
            row.dataset.cash    = cash;
            row.dataset.monthly = data.monthly_total;
            row.dataset.annual  = data.annual_amount;

            // Update display cells with server-confirmed values
            const cellTotal = row.querySelector('.cell-total');
            if (cellTotal) cellTotal.textContent = data.monthly_total > 0 ? fmt(data.monthly_total) : '';
            const cellAnnual = row.querySelector('.cell-annual');
            if (cellAnnual) cellAnnual.textContent = data.annual_amount > 0 ? fmt(data.annual_amount) : '';

            recalcAllParents();
            row.style.background = '#f0fdf4';
            setTimeout(() => { row.style.background = ''; }, 700);
        } catch {
            row.style.background = '#fef2f2';
            setTimeout(() => { row.style.background = ''; }, 700);
        } finally {
            row.style.opacity = '1';
            row.style.pointerEvents = '';
        }
    }

    // ── bind all leaf rows ────────────────────────────────────────
    document.querySelectorAll('.leaf-row').forEach(row => {
        const inputs = row.querySelectorAll('.si-atm,.si-cash,.si-annual');
        inputs.forEach(inp => inp.addEventListener('input', () => recalcRow(row)));

        row.querySelectorAll('.salary-input').forEach((inp, idx, arr) => {
            inp.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); saveEntry(row); inp.blur(); }
                if (e.key === 'Tab' && !e.shiftKey && idx === arr.length - 1) {
                    e.preventDefault();
                    saveEntry(row);
                    // Move to next leaf row
                    const allLeafs = Array.from(document.querySelectorAll('.leaf-row'));
                    const next = allLeafs[allLeafs.indexOf(row) + 1];
                    if (next) next.querySelector('.salary-input')?.focus();
                }
            });

            inp.addEventListener('blur', function () {
                setTimeout(() => {
                    if (!row.contains(document.activeElement)) saveEntry(row);
                }, 180);
            });
        });
    });

    // ── "Save all" button ────────────────────────────────────────
    const saveAllBtn = document.getElementById('sal-save-all');
    const saveStatus = document.getElementById('sal-save-status');
    function setStatus(msg, kind) {
        if (!saveStatus) return;
        saveStatus.textContent = msg || '';
        saveStatus.classList.remove('is-success', 'is-error', 'is-progress');
        if (kind) saveStatus.classList.add('is-' + kind);
    }
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', async () => {
            const rows = Array.from(document.querySelectorAll('.leaf-row[data-entry-id]'));
            if (!rows.length) return;
            saveAllBtn.disabled = true;
            setStatus(`ກຳລັງບັນທຶກ 0 / ${rows.length}...`, 'progress');

            let done = 0, errors = 0;
            // Save sequentially to keep parent totals consistent; cheap on small row counts.
            for (const row of rows) {
                try {
                    await saveEntry(row);
                } catch { errors++; }
                done++;
                setStatus(`ກຳລັງບັນທຶກ ${done} / ${rows.length}...`, 'progress');
            }

            if (errors === 0) {
                setStatus(`ບັນທຶກ ${rows.length} ລາຍການສຳເລັດ`, 'success');
            } else {
                setStatus(`ບັນທຶກສຳເລັດ ${rows.length - errors} / ${rows.length} (ຜິດພາດ ${errors})`, 'error');
            }
            saveAllBtn.disabled = false;
            setTimeout(() => setStatus('', ''), 3500);
        });
    }
})();
</script>
@endpush

@endsection
