@extends('layouts.admin')

@section('title', 'Manage plan')
@section('page-title', 'Manage plan')

@section('content')
@php
    $currentYear = (int) date('Y');
@endphp

<section class="mp-shell">
    <div class="mp-head">
        <div>
            <span class="mp-kicker">Planning workspace</span>
            <h2>Manage plan</h2>
            <p>ສ້າງແຜນປີດຽວ ແລ້ວຈັດການ ປະເມີນລາຍຮັບ, ເງິນເດືອນ, ແລະ ລາຍຈ່າຍ ໃນໜ້າດຽວ.</p>
        </div>
        <button type="button" class="mp-primary" id="openManagePlanModal">
            <span>+</span>
            ສ້າງແຜນປີໃໝ່
        </button>
    </div>

    <div class="mp-grid">
        @forelse($plans as $plan)
            @php
                $incomePlan = $plan->academicIncomePlans->first();
                $salaryPlan = $plan->salaryPlans->sortBy('month')->first();
                $expenseRows = $plan->expense_plans_count;
                $incomeTotal = $plan->academicIncomePlans->sum(fn ($income) => $income->items->sum('total_income'));
                $salaryTotal = $plan->salaryPlans->sum(fn ($salary) => $salary->entries->sum('annual_amount'));
                $expenseTotal = $plan->expensePlans->sum(fn ($expense) => $expense->values->sum('value_number'));
                $isCurrent = (int) $plan->year === $currentYear;
            @endphp
            <article class="mp-card  {{ $isCurrent ? 'is-current' : '' }}">
                <div class="mp-card-top">
                    <div class="mp-year">
                        <span>FY</span>
                        <strong>{{ $plan->year }}</strong>
                    </div>
                    <div class="mp-card-title">
                        <h3>{{ $plan->name ?: 'Planning ' . $plan->year }}</h3>
                        <p>{{ $plan->description ?: 'ຈັດການແຜນປະຈຳປີຮ່ວມກັນ' }}</p>
                    </div>
                    @if($isCurrent)
                        <span class="mp-pill">ປະຈຸບັນ</span>
                    @endif
                </div>

                <div class="mp-status">
                    <div>
                        <span>ລາຍຮັບ</span>
                        <strong>{{ $incomePlan ? number_format((float) $incomeTotal, 0) . ' ກີບ' : 'ຍັງບໍ່ມີ' }}</strong>
                    </div>
                    <div>
                        <span>ເງິນເດືອນ</span>
                        <strong>{{ $salaryPlan ? number_format((float) $salaryTotal, 0) . ' ກີບ' : 'ຍັງບໍ່ມີ' }}</strong>
                    </div>
                    <div>
                        <span>ລາຍຈ່າຍ</span>
                        <strong>{{ $expenseRows > 0 ? number_format((float) $expenseTotal, 0) . ' ກີບ' : 'ຍັງບໍ່ມີ' }}</strong>
                    </div>
                </div>

                <div class="mp-actions">
                    @if($incomePlan)
                        <a href="{{ route('head_of_finance.academic-income.evaluate', $incomePlan) }}" class="mp-action">
                            <x-icons.book-open /> ປະເມີນລາຍຮັບ
                        </a>
                    @endif
                    @if($salaryPlan)
                        <a href="{{ route('head_of_finance.salary.manage', $salaryPlan) }}" class="mp-action">
                            <x-icons.users /> ເງິນເດືອນ
                        </a>
                    @endif
                    <a href="{{ route('head_of_finance.expense.manage', $plan) }}" class="mp-action mp-action-strong">
                        <x-icons.book-open /> ລາຍຈ່າຍ
                    </a>
                    @if(!$incomePlan || !$salaryPlan)
                        <form method="POST" action="{{ route('head_of_finance.manage-plan.sync', $plan) }}">
                            @csrf
                            <button type="submit" class="mp-action mp-action-light">
                                <x-icons.settings /> ສ້າງສ່ວນທີ່ຂາດ
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('head_of_finance.manage-plan.destroy', $plan) }}" onsubmit="return confirm('ລຶບແຜນປະຈຳປີ {{ $plan->year }} ແລະຂໍ້ມູນລາຍຮັບ, ເງິນເດືອນ, ລາຍຈ່າຍຂອງປີນີ້ທັງໝົດ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mp-action mp-action-danger">
                            ລຶບແຜນ
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="mp-empty">
                <strong>ຍັງບໍ່ມີແຜນ</strong>
                <p>ກົດສ້າງແຜນປີໃໝ່ເພື່ອໄດ້ທັງ 3 ສ່ວນ: ລາຍຮັບ, ເງິນເດືອນ, ແລະ ລາຍຈ່າຍ.</p>
            </div>
        @endforelse
    </div>

    <div class="mp-pagination">{{ $plans->links() }}</div>
</section>

<div id="managePlanModal" class="mp-modal-backdrop" aria-hidden="true">
    <div class="mp-modal" role="dialog" aria-modal="true" aria-labelledby="managePlanModalTitle">
        <div class="mp-modal-head">
            <div>
                <span class="mp-kicker">Create once</span>
                <h3 id="managePlanModalTitle">ສ້າງແຜນປີໃໝ່</h3>
            </div>
            <button type="button" class="mp-close" data-close-manage-plan>&times;</button>
        </div>
        <form method="POST" action="{{ route('head_of_finance.manage-plan.store') }}" class="mp-modal-body">
            @csrf
            <label>
                <span>ປີງົບປະມານ</span>
                <input type="number" name="year" min="2000" max="2100" value="{{ old('year', $currentYear + 1) }}" required>
                @error('year')<small>{{ $message }}</small>@enderror
            </label>
            <label>
                <span>ຊື່ແຜນ</span>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Planning {{ $currentYear + 1 }}">
            </label>
            <label>
                <span>ລາຍລະອຽດ</span>
                <textarea name="description" rows="3">{{ old('description') }}</textarea>
            </label>
            <div class="mp-modal-note">
                ເມື່ອສ້າງແລ້ວ ລະບົບຈະສ້າງແຜນລາຍຮັບ, ແຜນເງິນເດືອນ, ແລະ ໂຄງສ້າງແຜນລາຍຈ່າຍໃຫ້ອັດຕະໂນມັດ.
            </div>
            <div class="mp-modal-actions">
                <button type="button" class="mp-secondary" data-close-manage-plan>ຍົກເລີກ</button>
                <button type="submit" class="mp-primary">ສ້າງແຜນ</button>
            </div>
        </form>
    </div>
</div>

<style>
    .mp-shell { display:flex; flex-direction:column; gap:1rem; }
    .mp-head {
        display:flex; align-items:flex-end; justify-content:space-between; gap:1rem;
        background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px; padding:1.1rem 1.2rem;
        box-shadow:0 2px 12px rgba(26,39,68,.05);
    }
    .mp-kicker { color:var(--fns-gold); font-size:.72rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .mp-head h2 { margin:.2rem 0; color:var(--fns-navy); font-size:1.35rem; }
    .mp-head p { margin:0; color:var(--fns-gray-500); font-size:.86rem; }
    .mp-primary, .mp-secondary, .mp-action {
        border-radius:8px; font-family:inherit; font-weight:900; font-size:.82rem; cursor:pointer;
    }
    .mp-primary {
        display:inline-flex; align-items:center; justify-content:center; gap:.45rem;
        border:1px solid var(--fns-gold); background:var(--fns-gold); color:#111b33;
        padding:.65rem .9rem; box-shadow:0 8px 18px rgba(201,153,26,.22);
    }
    .mp-secondary { border:1px solid var(--fns-gray-200); background:#fff; color:var(--fns-navy); padding:.65rem .9rem; }
    .mp-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:.85rem; }
    .mp-card {
        background:#fff; border:1px solid var(--fns-gray-200); border-radius:8px; padding:1rem;
        box-shadow:0 2px 12px rgba(26,39,68,.05);
    }
    .mp-card.is-current { border-color:rgba(201,153,26,.7); }
    .mp-card-top { display:grid; grid-template-columns:auto 1fr auto; gap:.8rem; align-items:start; }
    .mp-year { min-width:72px; border-radius:8px; background:#eef2f7; padding:.55rem; text-align:center; }
    .mp-year span { display:block; color:var(--fns-gray-400); font-size:.68rem; font-weight:900; }
    .mp-year strong { display:block; color:var(--fns-navy); font-size:1.35rem; line-height:1.1; }
    .mp-card-title h3 { margin:0; color:var(--fns-navy); font-size:1rem; }
    .mp-card-title p { margin:.25rem 0 0; color:var(--fns-gray-500); font-size:.8rem; }
    .mp-pill { align-self:start; border-radius:999px; background:rgba(201,153,26,.16); color:#8a6410; padding:.25rem .5rem; font-size:.7rem; font-weight:900; }
    .mp-status { display:grid; grid-template-columns:repeat(3, 1fr); gap:.55rem; margin:1rem 0; }
    .mp-status div { border:1px solid var(--fns-gray-200); border-radius:8px; padding:.55rem; }
    .mp-status span { display:block; color:var(--fns-gray-400); font-size:.7rem; font-weight:900; }
    .mp-status strong {
        display:block; margin-top:.15rem;
        color:var(--fns-navy);
        font-size:clamp(.72rem, 1.35vw, .86rem);
        font-variant-numeric:tabular-nums;
        white-space:normal;
        overflow-wrap:anywhere;
        line-height:1.25;
    }
    .mp-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
    .mp-actions form { margin:0; }
    .mp-action {
        display:inline-flex; align-items:center; gap:.45rem; border:1px solid var(--fns-gray-200);
        background:#fff; color:var(--fns-navy); padding:.55rem .7rem; text-decoration:none;
    }
    .mp-action svg { width:15px; height:15px; }
    .mp-action-strong { background:var(--fns-navy); border-color:var(--fns-navy); color:#fff; }
    .mp-action-light { background:#fbfbfc; }
    .mp-action-danger { border-color:#fecaca; background:#fff5f5; color:#b91c1c; }
    .mp-action-danger:hover { border-color:#f87171; background:#fee2e2; color:#991b1b; }
    .mp-empty { grid-column:1 / -1; text-align:center; background:#fff; border:1px dashed var(--fns-gray-200); border-radius:8px; padding:2rem; color:var(--fns-gray-500); }
    .mp-empty strong { display:block; color:var(--fns-navy); font-size:1rem; }
    .mp-pagination { margin-top:.25rem; }
    .mp-modal-backdrop {
        position:fixed; inset:0; z-index:60; display:none; align-items:center; justify-content:center;
        padding:1rem; background:rgba(6,18,38,.55); backdrop-filter:blur(4px);
    }
    .mp-modal-backdrop.is-open { display:flex; }
    .mp-modal { width:min(560px, 100%); background:#fff; border-radius:8px; box-shadow:0 24px 70px rgba(6,18,38,.24); overflow:hidden; }
    .mp-modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:1rem 1.1rem; border-bottom:1px solid var(--fns-gray-200); }
    .mp-modal-head h3 { margin:.2rem 0 0; color:var(--fns-navy); font-size:1.1rem; }
    .mp-close { border:0; background:transparent; color:var(--fns-gray-400); font-size:1.5rem; cursor:pointer; }
    .mp-modal-body { display:grid; gap:.85rem; padding:1rem 1.1rem; }
    .mp-modal-body label span { display:block; margin-bottom:.35rem; color:var(--fns-gray-500); font-size:.76rem; font-weight:900; }
    .mp-modal-body input, .mp-modal-body textarea {
        width:100%; border:1px solid var(--fns-gray-200); border-radius:8px; padding:.65rem .75rem;
        color:var(--fns-navy); font-family:inherit; outline:none;
    }
    .mp-modal-body input:focus, .mp-modal-body textarea:focus { border-color:var(--fns-gold); box-shadow:0 0 0 3px rgba(201,153,26,.16); }
    .mp-modal-body small { display:block; margin-top:.3rem; color:#b91c1c; font-size:.75rem; }
    .mp-modal-note { border-radius:8px; background:#f7f8fa; color:var(--fns-gray-500); padding:.75rem; font-size:.8rem; }
    .mp-modal-actions { display:flex; justify-content:flex-end; gap:.55rem; padding-top:.4rem; }
    @media (max-width:760px) {
        .mp-head { align-items:stretch; flex-direction:column; }
        .mp-grid { grid-template-columns:1fr; }
        .mp-card-top { grid-template-columns:1fr; }
        .mp-status { grid-template-columns:1fr; }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('managePlanModal');
    const openButton = document.getElementById('openManagePlanModal');
    const closeButtons = modal.querySelectorAll('[data-close-manage-plan]');

    const openModal = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => modal.querySelector('input[name="year"]')?.focus(), 50);
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    openButton?.addEventListener('click', openModal);
    closeButtons.forEach(button => button.addEventListener('click', closeModal));
    modal.addEventListener('click', event => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    @if($errors->has('year') || $errors->has('name') || $errors->has('description'))
        openModal();
    @endif
});
</script>
@endpush
@endsection
