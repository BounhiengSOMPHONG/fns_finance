@extends('layouts.admin')

@section('title', 'Planning ' . $planningYear->year)
@section('page-title', 'Planning')

@section('content')
@php
    $sections = $report['sections'];
    $totals = $report['totals'];
    $summaryRows = $report['summaryRows'];
    $detail_1_1 = $report['detail_1_1'];
    $detail_1_3 = $report['detail_1_3'];
    $feeYear2_4 = $report['feeYear2_4'];
    $feeYear1 = $report['feeYear1'];
    $s1_2 = $report['s1_2'];
    $s1_1 = $report['s1_1'];
    $salaryRows = collect($salaryReport['rows']);
    $salaryTotals = $salaryReport['totals'];
    $salaryMonth = $salaryReport['month'] ? str_pad((string) $salaryReport['month'], 2, '0', STR_PAD_LEFT) : '01';
    $salaryFiscalYear = $salaryReport['fiscal_year'] ?? $planningYear->year;
    $planYearRows = collect($planYearReport['rows'] ?? []);
    $planYearTotals = $planYearReport['totals'] ?? ['total_amount' => 0, 'state_amount' => 0, 'faculty_amount' => 0];
    $planYearWarnings = $planYearReport['warnings'] ?? ['unlinked_expenses' => [], 'reference_fallbacks' => []];
    $planYearSectionFormula = $planYearRows
        ->filter(fn (array $row): bool => (int) ($row['level'] ?? 0) === 0)
        ->map(fn (array $row): string => substr(str_pad((string) ($row['code'] ?? ''), 8, '0', STR_PAD_LEFT), 0, 2))
        ->filter(fn (string $code): bool => $code !== '00')
        ->unique()
        ->values()
        ->implode('+');

    $money = fn ($amount) => number_format((float) $amount, 0);
    $blankMoney = fn ($amount) => (float) $amount === 0.0 ? '0' : number_format((float) $amount, 0);
    $reportNumber = function ($value): string {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 0);
    };
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    $grossIncome = function ($item): float {
        if (! $item) {
            return 0.0;
        }

        if ($item->snap_course_credit_unit !== null) {
            return (float) $item->student_count * (float) $item->snap_course_credit_unit * (float) $item->snap_credit_unit_price;
        }

        if ($item->snap_registration_fee_rate !== null) {
            return (float) $item->student_count * (float) $item->snap_registration_fee_rate;
        }

        return (float) $item->student_count * (float) $item->snap_credit_unit_price;
    };
    $programLabel = function ($item, bool $yearFirst = true): string {
        $program = $item->degreeProgram;
        $name = $program?->name ?? '-';
        $level = $program?->level;
        $year = $program?->study_year;

        if ($level === 'bachelor') {
            return $yearFirst ? 'ປີ ' . ($year ?: '-') . ' ' . $name : $name . ' ປີທີ ' . ($year ?: '-');
        }

        $prefix = $level === 'phd' ? 'ປະລິນຍາເອກ' : 'ປະລິນຍາໂທ';

        return $prefix . ' ' . $name;
    };

    $incomeRows = collect($summaryRows)->values();
    $expenseRows = collect($expenseReport['sections'])->values();
    $balanceRowCount = max($incomeRows->count(), $expenseRows->count());
    $balanceRows = ($balanceRowCount > 0 ? collect(range(0, $balanceRowCount - 1)) : collect())
        ->map(function (int $index) use ($incomeRows, $expenseRows): array {
            $income = $incomeRows->get($index);
            $expense = $expenseRows->get($index);
            $incomeYearly = $income ? (float) $income['planned'] : null;
            $expenseYearly = $expense ? (float) $expense['total'] : null;

            return [
                'number' => $index + 1,
                'income_title' => $income['title'] ?? null,
                'income_yearly' => $incomeYearly,
                'income_monthly' => $incomeYearly !== null ? $incomeYearly / 12 : null,
                'expense_title' => $expense['title'] ?? null,
                'expense_yearly' => $expenseYearly,
                'expense_monthly' => $expense ? (float) $expense['period_total'] : null,
            ];
        });
    $balanceIncomeYearly = (float) $report['summaryPlanTotal'];
    $balanceIncomeMonthly = $balanceIncomeYearly / 12;
    $balanceExpenseYearly = (float) $expenseReport['total'];
    $balanceExpenseMonthly = (float) $expenseReport['periodTotal'];
    $balanceYearly = $balanceIncomeYearly - $balanceExpenseYearly;
    $balanceMonthly = $balanceIncomeMonthly - $balanceExpenseMonthly;
    $balanceCanFinalize = $balanceYearly >= -0.01;
    $balanceBlockingMessage = 'ລາຍຮັບວິຊາການຕ້ອງບໍ່ນ້ອຍກວ່າລາຍຈ່າຍວິຊາການ ຈຶ່ງຈະບັນທຶກ ຫຼື ສົ່ງຂໍຄວາມເຫັນໄດ້';
    $reviewContext = $reviewContext ?? [
        'mode' => 'finance',
        'can_manage_review' => true,
        'can_comment' => false,
        'can_agree' => false,
        'show_review_panel' => true,
        'current_user_id' => auth()->id(),
    ];
    $reviewerUsers = $reviewerUsers ?? collect();
    $currentRound = $planningYear->currentReviewRound;
    $previousReviewerIds = $currentRound?->reviewers?->pluck('user_id')->map(fn ($id) => (int) $id)->all() ?? [];
    $selectedReviewerIds = collect(old('reviewer_ids', $previousReviewerIds))->map(fn ($id) => (int) $id)->all();
    $reviewRounds = $planningYear->reviewRounds
        ->sortByDesc('round_number')
        ->values();
    $statusLabels = [
        'DRAFT' => 'Draft',
        'PENDING_REVIEW' => 'Pending review',
        'MODIFYING' => 'Modifying',
        'SAVED' => 'Saved',
    ];
@endphp

<div class="review-toolbar">
    <div>
        <span class="review-status review-status-{{ strtolower($planningYear->status ?? 'draft') }}">
            {{ $statusLabels[$planningYear->status ?? 'DRAFT'] ?? ($planningYear->status ?? 'DRAFT') }}
        </span>
        <h2>ແຜນປີ {{ $planningYear->year }}</h2>
        <p>{{ $planningYear->name }}</p>
    </div>

    <div class="review-toolbar-actions">
        @if(($reviewContext['mode'] ?? 'finance') === 'reviewer')
            <a href="{{ route('reviews.planning-years.index') }}" class="review-secondary-btn">ກັບໄປ Review</a>
        @else
            <a href="{{ route('head_of_finance.manage-plan.index') }}" class="review-secondary-btn">ກັບຄືນ</a>
        @endif

        @if($reviewContext['show_review_panel'] && $reviewRounds->isNotEmpty())
            <button type="button" class="review-secondary-btn review-drawer-toggle" data-open-review-drawer>
                Review
                <span>{{ $reviewRounds->sum(fn ($round) => ($round->comments ?? collect())->count()) }}</span>
            </button>
        @endif

        @if($reviewContext['can_manage_review'])
            <button type="button" class="review-primary-btn" data-print-plan>
                ພິມ
            </button>
        @endif

        @if($reviewContext['can_manage_review'] && $planningYear->canBeEdited())
            @if(! empty($planYearWarnings['unlinked_expenses']))
                <a href="{{ route('head_of_finance.settings.expense-default-rows.accounts.index') }}"
                   class="review-plan-warning"
                   title="ກວດແກ້ກ່ອນບັນທຶກແຜນປີ">
                    <strong>{{ count($planYearWarnings['unlinked_expenses']) }} ລາຍການຍັງບໍ່ໄດ້ຜູກບັນຊີ</strong>
                    <span>ກວດ Account Links ກ່ອນບັນທຶກແຜນປີ</span>
                </a>
            @endif

            @if($balanceCanFinalize)
                <form method="POST" action="{{ route('head_of_finance.manage-plan.save', $planningYear) }}">
                    @csrf
                    <button type="submit" class="review-save-btn" onclick="return confirm('ບັນທຶກແຜນ ແລະ ປິດການແກ້ໄຂລາຍຮັບ, ລາຍຈ່າຍ, ເງິນເດືອນ?')">
                        ບັນທຶກແຜນ
                    </button>
                </form>
            @else
                <span class="review-balance-lock" title="{{ $balanceBlockingMessage }}">ດຸນດ່ຽງຕິດລົບ</span>
            @endif
        @endif

        @if($reviewContext['can_manage_review'] && $planningYear->canRequestReview() && $balanceCanFinalize)
            <button type="button" class="review-primary-btn" data-open-review-modal>
                ສົ່ງຂໍຄວາມເຫັນ
            </button>
        @endif

        @if($reviewContext['can_manage_review'] && $planningYear->isPendingReview())
            <form method="POST" action="{{ route('head_of_finance.manage-plan.close-review', $planningYear) }}">
                @csrf
                <button type="submit" class="review-warning-btn" onclick="return confirm('ປິດຮອບຂໍຄວາມເຫັນ ແລະ ເຂົ້າສະຖານະກຳລັງແກ້ໄຂ?')">
                    ປິດຮອບ ແລະ ແກ້ໄຂ
                </button>
            </form>
        @endif
    </div>
</div>

@if($reviewContext['can_manage_review'] && ! $balanceCanFinalize)
    <div class="review-balance-alert">
        <strong>ຍັງບັນທຶກແຜນ ຫຼື ສົ່ງຂໍຄວາມເຫັນບໍ່ໄດ້</strong>
        <span>{{ $balanceBlockingMessage }}. ດຸນດ່ຽງປີນີ້: {{ $money($balanceYearly) }}</span>
    </div>
@endif

<nav class="preview-topic-nav" aria-label="ເລືອກຫົວຂໍ້ລາຍງານ">
    <span>ເລືອກຫົວຂໍ້</span>
    <div>
        <a href="#period-1-2" class="preview-topic-link is-active" data-preview-topic-link="plan-year" title="ລາຍຈ່າຍງົບປະມານປີ">ແຜນປີ</a>
        <a href="#preview-income" class="preview-topic-link" data-preview-topic-link="income">ລາຍຮັບ</a>
        <a href="#preview-expense" class="preview-topic-link" data-preview-topic-link="expense">ລາຍຈ່າຍ</a>
        <a href="#preview-salary" class="preview-topic-link" data-preview-topic-link="salary">ເງິນເດືອນ</a>
    </div>
</nav>

@if($reviewContext['can_manage_review'] && $planningYear->canRequestReview())
    <div class="review-modal-backdrop" data-review-modal hidden>
        <div class="review-modal" role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
            <div class="review-modal-head">
                <h3 id="reviewModalTitle">ສົ່ງຂໍຄວາມເຫັນ</h3>
                <button type="button" data-close-review-modal>&times;</button>
            </div>
            <form method="POST" action="{{ route('head_of_finance.manage-plan.request-review', $planningYear) }}" class="review-modal-body">
                @csrf
                <label class="review-field">
                    <span>ຜູ້ກວດສອບ</span>
                    <div class="reviewer-picker">
                        @forelse($reviewerUsers as $reviewerUser)
                            <label>
                                <input type="checkbox" name="reviewer_ids[]" value="{{ $reviewerUser->id }}" @checked(in_array((int) $reviewerUser->id, $selectedReviewerIds, true))>
                                <span>
                                    <strong>{{ $reviewerUser->full_name ?? $reviewerUser->username }}</strong>
                                    <small>{{ $reviewerUser->role?->role_name ?? '-' }}</small>
                                </span>
                            </label>
                        @empty
                            <p>ບໍ່ມີຜູ້ໃຊ້ active ສຳລັບເລືອກ</p>
                        @endforelse
                    </div>
                </label>
                <label class="review-field">
                    <span>ໝາຍເຫດ</span>
                    <textarea name="note" rows="3" maxlength="1000" placeholder="ຂໍ້ຄວາມສັ້ນໆ ສຳລັບຜູ້ກວດ"></textarea>
                </label>
                <div class="review-modal-actions">
                    <button type="button" class="review-secondary-btn" data-close-review-modal>ຍົກເລີກ</button>
                    <button type="submit" class="review-primary-btn">ສົ່ງຂໍຄວາມເຫັນ</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($reviewContext['show_review_panel'] && $reviewRounds->isNotEmpty())
    <div class="review-drawer-backdrop" data-review-drawer hidden>
        <aside class="review-drawer" role="dialog" aria-modal="true" aria-labelledby="reviewDrawerTitle">
            <div class="review-drawer-head">
                <div>
                    <span>Review</span>
                    <h3 id="reviewDrawerTitle">ຄວາມເຫັນຈາກຜູ້ກວດສອບ</h3>
                </div>
                <button type="button" class="review-drawer-close" data-close-review-drawer>&times;</button>
            </div>

            <section class="review-panel">
                @foreach($reviewRounds as $round)
                    @php
                        $isCurrentRound = $currentRound && (int) $round->id === (int) $currentRound->id;
                        $roundComments = $round->comments ?? collect();
                    @endphp
                    <div class="review-round {{ $isCurrentRound ? 'is-current' : '' }}">
                        <div class="review-panel-head">
                            <span class="review-round-line">Review round {{ $round->round_number }}{{ $isCurrentRound ? ' · Current' : '' }}</span>
                            <div class="review-panel-meta">
                                <span>ຜູ້ກວດ {{ $round->reviewers->count() }} ຄົນ</span>
                                @if($round->closed_at)
                                    <span>ປິດ {{ optional($round->closed_at)->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($round->note)
                            <p class="review-note">{{ $round->note }}</p>
                        @endif

                        <div class="reviewer-list">
                            @foreach($round->reviewers as $reviewer)
                                <span>{{ $reviewer->user?->full_name ?? $reviewer->user?->username ?? '-' }}</span>
                            @endforeach
                        </div>

                        @if($isCurrentRound && $reviewContext['can_comment'])
                            <form method="POST" action="{{ route('reviews.planning-years.comments.store', $planningYear) }}" class="review-comment-form">
                                @csrf
                                <textarea name="comment" rows="3" maxlength="3000" required placeholder="ຂຽນຄວາມເຫັນຂອງທ່ານ"></textarea>
                                <button type="submit" class="review-primary-btn">ສົ່ງຄວາມເຫັນ</button>
                            </form>
                        @endif

                        <div class="review-comments">
                            @forelse($roundComments as $comment)
                                @php
                                    $agreementCount = $comment->agreements->count();
                                    $agreedByMe = $comment->agreements->contains('user_id', $reviewContext['current_user_id']);
                                @endphp
                                <article class="review-comment">
                                    <div class="review-comment-top">
                                        <div>
                                            <strong>{{ $comment->user?->full_name ?? $comment->user?->username ?? '-' }}</strong>
                                            <span>{{ $comment->user?->role?->role_name ?? '' }}</span>
                                        </div>
                                        <time>{{ $comment->created_at?->format('d/m/Y H:i') }}</time>
                                    </div>
                                    <p>{{ $comment->comment }}</p>
                                    <div class="review-comment-actions">
                                        <span>{{ $agreementCount }} ເຫັນດີ</span>
                                        @if($isCurrentRound && $reviewContext['can_agree'] && (int) $comment->user_id !== (int) $reviewContext['current_user_id'])
                                            <form method="POST" action="{{ route('reviews.planning-years.comments.agreement', [$planningYear, $comment]) }}">
                                                @csrf
                                                <button type="submit" class="{{ $agreedByMe ? 'is-agreed' : '' }}">
                                                    {{ $agreedByMe ? 'ຍົກເລີກເຫັນດີ' : 'ເຫັນດີ' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="review-empty">ຍັງບໍ່ມີຄວາມເຫັນ</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </section>
        </aside>
    </div>
@endif

<div class="income-preview">
    <section class="paper plan-year-paper" id="period-1-2" data-preview-topic="plan-year">
        <div class="official-header">
            <div class="org-left">
                <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
            </div>
            <div class="nation-right">
                <strong>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</strong>
                <span>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</span>
            </div>
        </div>

        <div class="plan-year-title-block">
            <p>ແຜນລາຍຈ່າຍງົບປະມານປີ {{ $planningYear->year }}</p>
        </div>

        <table class="report-table plan-year-table">
            <colgroup>
                <col class="plan-year-code-col">
                <col class="plan-year-code-col">
                <col class="plan-year-code-col">
                <col class="plan-year-code-col">
                <col class="plan-year-name-col">
                <col class="plan-year-money-col">
                <col class="plan-year-money-col">
                <col class="plan-year-money-col">
            </colgroup>
            <thead>
                <tr class="plan-year-head-row">
                    <th rowspan="2" class="plan-year-code-head"><span>ພາກ</span></th>
                    <th rowspan="2" class="plan-year-code-head"><span>ພາກ</span><span>ສ່ວນ</span></th>
                    <th rowspan="2" class="plan-year-code-head"><span>ຮ່ວງ</span></th>
                    <th rowspan="2" class="plan-year-code-head"><span>ລູກ</span><span>ຮ່ວງ</span></th>
                    <th rowspan="2">ເນື້ອໃນລາຍຈ່າຍ</th>
                    <th colspan="3" class="plan-year-budget-head">ແຜນງົບປະມານ</th>
                </tr>
                <tr class="plan-year-budget-row">
                    <th>ລວມ</th>
                    <th>ປົກກະຕິ</th>
                    <th>ວິຊາການ</th>
                </tr>
            </thead>
            <tbody>
                <tr class="plan-year-overall-row">
                    <td colspan="4"></td>
                    <td class="plan-year-overall-label">ລວມຍອດ ເງິນ ພາກ ສ່ວນ ({{ $planYearSectionFormula ?: '...' }}) =</td>
                    <td class="num">{{ $money($planYearTotals['total_amount']) }}</td>
                    <td class="num">{{ $money($planYearTotals['state_amount']) }}</td>
                    <td class="num">{{ $money($planYearTotals['faculty_amount']) }}</td>
                </tr>
                @forelse($planYearRows as $row)
                    @php
                        $code = str_pad((string) $row['code'], 8, '0', STR_PAD_LEFT);
                        $codeParts = [
                            substr($code, 0, 2),
                            substr($code, 2, 2),
                            substr($code, 4, 2),
                            substr($code, 6, 2),
                        ];
                        $level = min((int) $row['level'], 3);
                    @endphp
                    <tr class="{{ $row['level'] === 0 ? 'plan-year-root-row' : '' }} {{ ! empty($row['is_group']) ? 'plan-year-group-row' : '' }}">
                        @foreach($codeParts as $partIndex => $part)
                            <td class="center plan-year-code-cell {{ $partIndex === $level ? 'plan-year-code-main' : '' }}">
                                {{ $partIndex < $level ? '' : $part }}
                            </td>
                        @endforeach
                        <td class="plan-year-name">{{ $row['title'] }}</td>
                        <td class="num">{{ $money($row['total_amount']) }}</td>
                        <td class="num">{{ $money($row['state_amount']) }}</td>
                        <td class="num">{{ $money($row['faculty_amount']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center">60</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="4" class="center">ຍັງບໍ່ມີຂໍ້ມູນລາຍຈ່າຍຕາມຜັງບັນຊີ</td>
                    </tr>
                @endforelse
                <tr class="total-row plan-year-grand-total">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="center">ລວມຍອດ</td>
                    <td class="num">{{ $money($planYearTotals['total_amount']) }}</td>
                    <td class="num">{{ $money($planYearTotals['state_amount']) }}</td>
                    <td class="num">{{ $money($planYearTotals['faculty_amount']) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-grid balance-signatures">
            @foreach(['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $signature)
                <div class="signature">
                    <span>ວັນທີ ......./......./.......</span>
                    <div></div>
                    <strong>{{ $signature }}</strong>
                </div>
            @endforeach
        </div>
        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper balance-paper">
        <h2 class="balance-title">ແຜນງົບປະມານດຸນດ່ຽງລາຍຮັບ ແລະ ລາຍຈ່າຍວິຊາການ ຂອງ ຄວທ ປະຈຳ ສົກຮຽນ {{ $planningYear->year }}</h2>

        <div class="balance-org">
            <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
            <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
        </div>

        <table class="report-table balance-table">
            <thead>
                <tr>
                    <th rowspan="2" class="balance-num-col">ລ/ດ</th>
                    <th colspan="3">ລາຍຮັບ</th>
                    <th colspan="3">ລາຍຈ່າຍ</th>
                    <th colspan="2">ດຸນດ່ຽງ</th>
                </tr>
                <tr>
                    <th>ລາຍການລາຍຮັບຈາກພາກສ່ວນຕ່າງໆ</th>
                    <th class="balance-money-col">ງົບປະມານ/ປີ</th>
                    <th class="balance-money-col">ງົບປະມານ/ເດືອນ</th>
                    <th>ເນື້ອໃນລາຍຈ່າຍ</th>
                    <th class="balance-money-col">ລາຍຈ່າຍ/ປີ</th>
                    <th class="balance-money-col">ລາຍຈ່າຍ/ເດືອນ</th>
                    <th class="balance-money-col">ດຸນດ່ຽງຕໍ່ປີ</th>
                    <th class="balance-money-col">ດຸນດ່ຽງ/ເດືອນ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balanceRows as $row)
                    <tr>
                        <td class="center">{{ $row['number'] }}</td>
                        <td>{{ $row['income_title'] ?? '' }}</td>
                        <td class="num">{{ $row['income_yearly'] !== null ? $money($row['income_yearly']) : '' }}</td>
                        <td class="num">{{ $row['income_monthly'] !== null ? $money($row['income_monthly']) : '' }}</td>
                        <td>{{ $row['expense_title'] ?? '' }}</td>
                        <td class="num">{{ $row['expense_yearly'] !== null ? $money($row['expense_yearly']) : '' }}</td>
                        <td class="num">{{ $row['expense_monthly'] !== null ? $money($row['expense_monthly']) : '' }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td class="center">1</td>
                        <td colspan="8" class="center">ຍັງບໍ່ມີຂໍ້ມູນ</td>
                    </tr>
                @endforelse
                <tr class="total-row balance-total-row">
                    <td></td>
                    <td class="center">ລວມ</td>
                    <td class="num">{{ $money($balanceIncomeYearly) }}</td>
                    <td class="num">{{ $money($balanceIncomeMonthly) }}</td>
                    <td></td>
                    <td class="num">{{ $money($balanceExpenseYearly) }}</td>
                    <td class="num">{{ $money($balanceExpenseMonthly) }}</td>
                    <td class="num">{{ $money($balanceYearly) }}</td>
                    <td class="num">{{ $money($balanceMonthly) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-grid balance-signatures">
            @foreach(['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $signature)
                <div class="signature">
                    <span>ວັນທີ ......./......./.......</span>
                    <div></div>
                    <strong>{{ $signature }}</strong>
                </div>
            @endforeach
        </div>

        <div class="balance-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper paper-summary income-summary-paper" id="preview-income" data-preview-topic="income">
        <h2 class="plan-year-report-title">1. ແຜນງົບປະມານລາຍຮັບວິຊາການຂອງ ຄວທ ສົກ {{ $planningYear->year }}</h2>

        <div class="income-summary-org">
            <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
            <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
        </div>

        <table class="report-table plan-table income-summary-table">
            <thead>
                <tr>
                    <th class="income-summary-num-col">ລ/ດ</th>
                    <th>ລາຍການ</th>
                    <th class="income-summary-money-col">ຈຳນວນເງິນຕາມແຜນ</th>
                    <th class="income-summary-money-col">ຈຳນວນເງິນຮັບຕົວຈິງ</th>
                    <th class="income-summary-bal-col">ດຸນດ່ຽງ</th>
                    <th class="income-summary-note-col">ໝາຍເຫດ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summaryRows as $row)
                    <tr>
                        <td class="center">{{ $row['number'] }}</td>
                        <td>{{ $row['title'] }}</td>
                        <td class="num">{{ $blankMoney($row['planned']) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td class="center">ລວມ</td>
                    <td class="num">{{ $money($report['summaryPlanTotal']) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper income-overview-paper">
        <div class="official-header">
            <div class="org-left">
                <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
            </div>
            <div class="nation-right">
                <strong>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</strong>
                <span>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</span>
            </div>
        </div>
        <h1 class="report-title">ຮ່າງສັງລວມລາຍຮັບວິຊາການ ສົກ {{ $planningYear->year }}</h1>

        <table class="report-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:42px">ລ/ດ</th>
                    <th rowspan="2">ເນື້ອໃນເອກະສານ</th>
                    <th rowspan="2" style="width:76px">ຈຳນວນພົນ</th>
                    <th rowspan="2" style="width:96px">ອັດຕາຕໍ່ໜ່ວຍ</th>
                    <th rowspan="2" style="width:118px">ຈຳນວນເງິນລວມ</th>
                    <th rowspan="2" style="width:110px">ມອບພັນທະ ມຊ</th>
                    <th colspan="3">ລາຍຮັບຂອງ ຄວທ</th>
                </tr>
                <tr>
                    <th style="width:112px">ລວມລາຍຮັບ ຄວທ</th>
                    <th style="width:108px">ຄ່າສິດສອນ</th>
                    <th style="width:112px">ເຫຼືອຈາກຄ່າສອນ</th>
                </tr>
            </thead>
            <tbody>
                <tr class="grand-row">
                    <td></td>
                    <td class="center">ລວມລາຍຮັບທັງໝົດ</td>
                    <td></td>
                    <td></td>
                    <td class="num">{{ $money($totals['gross']) }}</td>
                    <td class="num">{{ $money($totals['nuol']) }}</td>
                    <td class="num">{{ $money($totals['fns_income']) }}</td>
                    <td class="num">{{ $money($totals['teaching_fee']) }}</td>
                    <td class="num">{{ $money($totals['remaining']) }}</td>
                </tr>

                @php $s = $sections['s1']; @endphp
                <tr class="section-row">
                    <td class="center">1</td>
                    <td>{{ $s['title'] }}</td>
                    <td></td>
                    <td></td>
                    <td class="num">{{ $money($s['totals']['gross']) }}</td>
                    <td class="num">{{ $money($s['totals']['nuol']) }}</td>
                    <td class="num">{{ $money($s['totals']['fns_income']) }}</td>
                    <td class="num">{{ $money($s['totals']['teaching_fee']) }}</td>
                    <td class="num">{{ $money($s['totals']['remaining']) }}</td>
                </tr>
                @foreach($s['rows'] as $key => $row)
                    <tr>
                        <td class="center">{{ $key }}</td>
                        <td class="indent">{{ $row['title'] }}</td>
                        <td class="num">{{ $row['count'] }}</td>
                        <td class="num">{{ $row['rate'] ? $money($row['rate']) : '' }}</td>
                        <td class="num">{{ $money($row['gross']) }}</td>
                        <td class="num">{{ $money($row['nuol']) }}</td>
                        <td class="num">{{ $money($row['fns_income']) }}</td>
                        <td class="num">{{ $money($row['teaching_fee']) }}</td>
                        <td class="num">{{ $money($row['remaining']) }}</td>
                    </tr>
                @endforeach

                @php $s = $sections['s2']; @endphp
                <tr class="section-row">
                    <td class="center">2</td>
                    <td>{{ $s['title'] }}</td>
                    <td></td>
                    <td></td>
                    <td class="num">{{ $money($s['totals']['gross']) }}</td>
                    <td class="num">{{ $money($s['totals']['nuol']) }}</td>
                    <td class="num">{{ $money($s['totals']['fns_income']) }}</td>
                    <td class="num">{{ $money($s['totals']['teaching_fee']) }}</td>
                    <td class="num">{{ $money($s['totals']['remaining']) }}</td>
                </tr>
                @foreach($s['rows'] as $key => $row)
                    <tr>
                        <td class="center">{{ $key }}</td>
                        <td class="indent">{{ $row['title'] }}</td>
                        <td class="num">{{ $row['count'] }}</td>
                        <td class="num">{{ $row['rate'] ? $money($row['rate']) : '' }}</td>
                        <td class="num">{{ $money($row['gross']) }}</td>
                        <td class="num">{{ $money($row['nuol']) }}</td>
                        <td class="num">{{ $money($row['fns_income']) }}</td>
                        <td class="num">{{ $money($row['teaching_fee']) }}</td>
                        <td class="num">{{ $money($row['remaining']) }}</td>
                    </tr>
                @endforeach

                @foreach(['s3' => '3', 's4' => '4', 's5' => '5', 's6' => '6'] as $key => $number)
                    @php $row = $sections[$key]; @endphp
                    <tr class="section-row">
                        <td class="center">{{ $number }}</td>
                        <td>{{ $row['title'] }}</td>
                        <td class="num">{{ $row['count'] }}</td>
                        <td class="num">{{ $row['rate'] ? $money($row['rate']) : '' }}</td>
                        <td class="num">{{ $money($row['gross']) }}</td>
                        <td class="num">{{ $money($row['nuol']) }}</td>
                        <td class="num">{{ $money($row['fns_income']) }}</td>
                        <td class="num">{{ $money($row['teaching_fee']) }}</td>
                        <td class="num">{{ $money($row['remaining']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-grid">
            @foreach(['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $signature)
                <div class="signature">
                    <span>ວັນທີ ......./......./.......</span>
                    <div></div>
                    <strong>{{ $signature }}</strong>
                </div>
            @endforeach
        </div>

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.1. ລາຍຮັບຄ່າໜ່ວຍກິດນັກຮຽນແຕ່ປີ 2-4 ລະບົບຈ່າຍເງິນ ແລະ ປະລິນຍາໂທ</h2>
        <table class="report-table">
            <thead>
                <tr>
                    <th>ລ/ດ</th>
                    <th>ລາຍການ / ຫຼັກສູດ</th>
                    <th>ອັດຕາຄ່າຮຽນຕໍ່ຄົນ</th>
                    <th>ຈຳນວນຄົນ</th>
                    <th>ລາຍຮັບລວມ</th>
                    <th>ຈຳນວນເປີເຊັນ ມຊ</th>
                    <th>ພັນທະມຊ</th>
                    <th>ຈຳນວນເປີເຊັນ ຄວທ</th>
                    <th>ລາຍຮັບຄວທ</th>
                </tr>
            </thead>
            <tbody>
                @php $dTotal = ['count' => 0, 'gross' => 0.0, 'nuol' => 0.0, 'fns' => 0.0]; @endphp
                @foreach($detail_1_1 as $item)
                    @php
                        $rate = (float) $item->snap_course_credit_unit * (float) $item->snap_credit_unit_price;
                        $gross = $grossIncome($item);
                        $nuolPct = (float) $item->snap_nuol_pct;
                        $nuol = $gross * $nuolPct;
                        $fns = $gross - $nuol;
                        $dTotal['count'] += (int) $item->student_count;
                        $dTotal['gross'] += $gross;
                        $dTotal['nuol'] += $nuol;
                        $dTotal['fns'] += $fns;
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $programLabel($item) }}</td>
                        <td class="num">{{ $money($rate) }}</td>
                        <td class="num">{{ (int) $item->student_count }}</td>
                        <td class="num">{{ $money($gross) }}</td>
                        <td class="num">{{ $pct($nuolPct) }}</td>
                        <td class="num">{{ $money($nuol) }}</td>
                        <td class="num">{{ $pct(1 - $nuolPct) }}</td>
                        <td class="num">{{ $money($fns) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td>ລວມ</td>
                    <td></td>
                    <td class="num">{{ $dTotal['count'] }}</td>
                    <td class="num">{{ $money($dTotal['gross']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['nuol']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['fns']) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.2. ລາຍຮັບຄ່າລົງທະບຽນນັກສຶກສາປີທີ 2-4 ຂອງ ຄວທ</h2>
        @include('dashboards.finance_head.manage-plan._registration-fee-table', [
            'feeSetting' => $feeYear2_4,
            'studentCount' => (int) ($s1_2?->student_count ?? 0),
            'money' => $money,
            'pct' => $pct,
        ])

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.3. ລາຍຮັບຄ່າໜ່ວຍກິດປີ 1 ລະບົບຈ່າຍເງິນ</h2>
        <table class="report-table">
            <thead>
                <tr>
                    <th>ລ/ດ</th>
                    <th>ລາຍການ / ຫຼັກສູດ</th>
                    <th>ອັດຕາຄ່າຮຽນຕໍ່ຄົນ</th>
                    <th>ຈຳນວນຄົນ</th>
                    <th>ລາຍຮັບລວມ</th>
                    <th>ຈຳນວນເປີເຊັນ ມຊ</th>
                    <th>ພັນທະມຊ</th>
                    <th>ຈຳນວນເປີເຊັນ ຄວທ</th>
                    <th>ລາຍຮັບຄວທ</th>
                </tr>
            </thead>
            <tbody>
                @php $dTotal = ['count' => 0, 'gross' => 0.0, 'nuol' => 0.0, 'fns' => 0.0]; @endphp
                @foreach($detail_1_3 as $item)
                    @php
                        $rate = (float) $item->snap_course_credit_unit * (float) $item->snap_credit_unit_price;
                        $gross = $grossIncome($item);
                        $nuolPct = (float) $item->snap_nuol_pct;
                        $nuol = $gross * $nuolPct;
                        $fns = $gross - $nuol;
                        $dTotal['count'] += (int) $item->student_count;
                        $dTotal['gross'] += $gross;
                        $dTotal['nuol'] += $nuol;
                        $dTotal['fns'] += $fns;
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $programLabel($item, false) }}</td>
                        <td class="num">{{ $money($rate) }}</td>
                        <td class="num">{{ (int) $item->student_count }}</td>
                        <td class="num">{{ $money($gross) }}</td>
                        <td class="num">{{ $pct($nuolPct) }}</td>
                        <td class="num">{{ $money($nuol) }}</td>
                        <td class="num">{{ $pct(1 - $nuolPct) }}</td>
                        <td class="num">{{ $money($fns) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td>ລວມ</td>
                    <td></td>
                    <td class="num">{{ $dTotal['count'] }}</td>
                    <td class="num">{{ $money($dTotal['gross']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['nuol']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($dTotal['fns']) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper detail-paper">
        <h2 class="detail-title">1.4. ຄ່າລົງທະບຽນນັກສຶກສາປີທີ 1 ລະບົບຈ່າຍເງິນຂອງ ຄວທ</h2>
        @include('dashboards.finance_head.manage-plan._registration-fee-table', [
            'feeSetting' => $feeYear1,
            'studentCount' => (int) ($s1_1?->student_count ?? 0),
            'money' => $money,
            'pct' => $pct,
        ])

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    <section class="paper paper-summary expense-paper expense-summary-paper" id="preview-expense" data-preview-topic="expense">
        <h2 class="expense-summary-title">2. ງົບປະມານລາຍຈ່າຍບໍລິຫານ ແລະ ວິຊາການຂອງ ຄວທ ປະຈຳສົກປີ {{ $planningYear->year }}</h2>

        <div class="expense-org">
            <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
            <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
        </div>

        <table class="report-table plan-table expense-summary-table">
            <thead>
                <tr>
                    <th style="width:44px">ລ/ດ</th>
                    <th>ລາຍການ</th>
                    <th style="width:88px">ອ້າງອີງ</th>
                    <th style="width:132px">ຕໍ່ເດືອນ</th>
                    <th style="width:90px">ຈ/ນເດືອນ</th>
                    <th style="width:132px">ຕໍ່ປີ</th>
                    <th style="width:150px">ໝາຍເຫດ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenseReport['sections'] as $expenseSection)
                    <tr>
                        <td class="center">{{ $expenseSection['number'] }}</td>
                        <td>{{ $expenseSection['title'] }}</td>
                        <td class="center">{{ $expenseSection['code'] }}</td>
                        <td class="num">{{ $money($expenseSection['period_total']) }}</td>
                        <td class="num">{{ $reportNumber($expenseSection['period_count']) }}</td>
                        <td class="num">{{ $money($expenseSection['total']) }}</td>
                        <td>{{ $expenseSection['note'] }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td class="center" colspan="2">ລວມ</td>
                    <td class="num">{{ $money($expenseReport['periodTotal']) }}</td>
                    <td></td>
                    <td class="num">{{ $money($expenseReport['total']) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="signature-grid expense-signatures">
            @foreach(['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $signature)
                <div class="signature">
                    <span>ວັນທີ ......./......./.......</span>
                    <div></div>
                    <strong>{{ $signature }}</strong>
                </div>
            @endforeach
        </div>

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>

    @foreach($expenseReport['sections'] as $expenseSection)
        <section class="paper detail-paper expense-paper expense-detail-paper">
            <h2 class="expense-section-title">{{ $expenseSection['code'] }} ແຜນງົບປະມານ{{ $expenseSection['title'] }} ຂອງ ຄວທ ປະຈຳສົກປີ {{ $planningYear->year }}</h2>

            <div class="expense-detail-header">
                <div class="expense-org">
                    <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                    <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
                </div>
                <span class="unit-label">ໜ່ວຍ: ກີບ</span>
            </div>

            <table class="report-table expense-summary-table">
                <thead>
                    <tr>
                        <th style="width:44px">ລ/ດ</th>
                        <th>ລາຍການ</th>
                        <th style="width:92px">ອ້າງອີງ</th>
                        <th style="width:132px">ຕໍ່ເດືອນ</th>
                        <th style="width:94px">ຈ/ນເດືອນ</th>
                        <th style="width:132px">ໝົດປີ</th>
                        <th style="width:150px">ໝາຍເຫດ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenseSection['details'] as $detail)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $detail['title'] }}</td>
                            <td class="center">{{ $detail['code'] }}</td>
                            <td class="num">{{ $money($detail['total'] / max((float) $expenseSection['period_count'], 1)) }}</td>
                            <td class="num">{{ $reportNumber($expenseSection['period_count']) }}</td>
                            <td class="num">{{ $money($detail['total']) }}</td>
                            <td></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="center">1</td>
                            <td>ຍັງບໍ່ມີລາຍການ</td>
                            <td class="center">{{ $expenseSection['code'] }}</td>
                            <td class="num">0</td>
                            <td class="num">{{ $reportNumber($expenseSection['period_count']) }}</td>
                            <td class="num">0</td>
                            <td></td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td></td>
                        <td class="center" colspan="2">ລວມ</td>
                        <td class="num">{{ $money($expenseSection['period_total']) }}</td>
                        <td></td>
                        <td class="num">{{ $money($expenseSection['total']) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            @foreach($expenseSection['details'] as $detail)
                <h3 class="expense-subtitle">{{ $detail['code'] }} {{ $detail['title'] }}</h3>
                <table class="report-table expense-detail-table">
                    <thead>
                        <tr>
                            <th style="width:44px">ລ/ດ</th>
                            <th>ລາຍການ</th>
                            @foreach($detail['columns'] as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                            <th>ຈຳນວນເງິນ</th>
                            <th>ໝາຍເຫດ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detail['rows'] as $row)
                            <tr>
                                <td class="center">{{ $row['number'] }}</td>
                                <td>{{ $row['item_name'] }}</td>
                                @foreach($detail['columns'] as $column)
                                    <td class="num">{{ $reportNumber($row['values'][$column['key']] ?? null) }}</td>
                                @endforeach
                                <td class="num">{{ $money($row['total']) }}</td>
                                <td>{{ $row['note'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="center">1</td>
                                <td>ຍັງບໍ່ມີລາຍການ</td>
                                @foreach($detail['columns'] as $column)
                                    <td></td>
                                @endforeach
                                <td class="num">0</td>
                                <td></td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td></td>
                            <td class="center" colspan="{{ count($detail['columns']) + 1 }}">ລວມ</td>
                            <td class="num">{{ $money($detail['total']) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            @endforeach

            <div class="plan-year-page-number" aria-hidden="true"></div>
        </section>
    @endforeach

    <span id="preview-salary" class="preview-topic-anchor" aria-hidden="true"></span>
    <section class="paper salary-paper" id="period-3-4" data-preview-topic="salary">
        <div class="official-header salary-header">
            <div class="org-left">
                <strong>ມະຫາວິທະຍາໄລແຫ່ງຊາດ</strong>
                <strong>ຄະນະວິທະຍາສາດທຳມະຊາດ</strong>
            </div>
            <div class="nation-right">
                <strong>ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</strong>
                <span>ສັນຕິພາບ ເອກະລາດ ປະຊາທິປະໄຕ ເອກະພາບ ວັດທະນາຖາວອນ</span>
            </div>
        </div>

        <h2 class="report-title salary-title">ຕາຕະລາງສັງລວມລາຍຈ່າຍເງິນເດືອນ ຕາມສາລະບານງົບປະມານ</h2>
        <div class="salary-meta">
            <span>ເດືອນ {{ $salaryMonth }}/{{ $salaryFiscalYear }}</span>
            <span>ງວດທີ 1 ສົກປີ {{ $salaryFiscalYear }}</span>
            <span>ໜ່ວຍ: ກີບ</span>
        </div>

        <table class="report-table salary-table">
            <thead>
                <tr>
                    <th colspan="4">ສາລະບານງົບປະມານ</th>
                    <th rowspan="2">ເນື້ອໃນລາຍຈ່າຍ</th>
                    <th rowspan="2" style="width:78px">ຈຳນວນພົນ</th>
                    <th colspan="3">ຈຳນວນເງິນຖອນຕົວຈິງໃນ 1 ເດືອນ</th>
                    <th rowspan="2" style="width:128px">ລວມ 12 ເດືອນ</th>
                </tr>
                <tr>
                    <th style="width:38px">ພ</th>
                    <th style="width:38px">ພສ</th>
                    <th style="width:38px">ຮ່ວງ</th>
                    <th style="width:38px">ລຮ</th>
                    <th style="width:128px">ໂອນເຂົ້າ ATM</th>
                    <th style="width:128px">ຖອນເງິນສົດ</th>
                    <th style="width:128px">ລວມ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaryRows as $row)
                    @php
                        $code = str_pad((string) $row['code'], 8, '0', STR_PAD_LEFT);
                        $codeParts = [
                            substr($code, 0, 2),
                            substr($code, 2, 2),
                            substr($code, 4, 2),
                            substr($code, 6, 2),
                        ];
                        $level = min((int) $row['level'], 3);
                    @endphp
                    <tr class="{{ $row['level'] === 0 ? 'salary-root-row' : '' }} {{ $row['is_group'] ? 'salary-group-row' : '' }}">
                        @foreach($codeParts as $partIndex => $part)
                            <td class="center salary-code-cell {{ $partIndex === $level ? 'salary-code-main' : '' }}">
                                {{ $partIndex < $level ? '' : $part }}
                            </td>
                        @endforeach
                        <td class="salary-name">{{ $row['title'] }}</td>
                        <td class="num">{{ $row['person_count'] }}</td>
                        <td class="num">{{ $money($row['transfer_amount']) }}</td>
                        <td class="num">{{ $money($row['cash_amount']) }}</td>
                        <td class="num">{{ $money($row['monthly_total']) }}</td>
                        <td class="num">{{ $money($row['annual_total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center">60</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="6" class="center">ຍັງບໍ່ມີລະຫັດບັນຊີເງິນເດືອນ</td>
                    </tr>
                @endforelse
                <tr class="total-row salary-grand-total">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="center">ລວມຍອດເງິນໄດ້ຮັບທັງໝົດ</td>
                    <td class="num">{{ $salaryTotals['person_count'] }}</td>
                    <td class="num">{{ $money($salaryTotals['transfer_amount']) }}</td>
                    <td class="num">{{ $money($salaryTotals['cash_amount']) }}</td>
                    <td class="num">{{ $money($salaryTotals['monthly_total']) }}</td>
                    <td class="num">{{ $money($salaryTotals['annual_total']) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-grid salary-signatures">
            @foreach(['ຄະນະບໍດີ', 'ຫົວໜ້າພະແນກຈັດຕັ້ງ-ສັງລວມ', 'ຫົວໜ້າພະແນກວິຊາການ', 'ຫົວໜ້າພະແນກການເງິນ-ຊັບສິນ'] as $signature)
                <div class="signature salary-signature">
                    <span>ວັນທີ ......./......./.......</span>
                    <div></div>
                    <strong>{{ $signature }}</strong>
                </div>
            @endforeach
        </div>

        <div class="plan-year-page-number" aria-hidden="true"></div>
    </section>
</div>

<style>
    .review-toolbar,
    .review-panel {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(17, 27, 51, .06);
        margin-bottom: 1rem;
        padding: 1rem;
    }

    .review-toolbar {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
    }

    .review-toolbar h2 {
        color: var(--fns-navy);
        font-size: 1.1rem;
        font-weight: 800;
        margin: .3rem 0 .1rem;
    }

    .review-toolbar p,
    .review-panel-meta,
    .review-comment-top span,
    .review-comment-top time {
        color: var(--fns-gray-600);
        font-size: .8rem;
    }

    .review-toolbar-actions,
    .review-modal-actions,
    .review-comment-actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        justify-content: flex-end;
    }

    .review-status {
        border-radius: 999px;
        display: inline-flex;
        font-size: .72rem;
        font-weight: 800;
        padding: .22rem .6rem;
    }

    .review-status-draft {
        background: #eef2f7;
        color: #475569;
    }

    .review-status-pending_review {
        background: rgba(201, 153, 26, .15);
        color: #8b6a12;
    }

    .review-status-modifying {
        background: rgba(26, 74, 46, .12);
        color: var(--fns-green);
    }

    .review-status-saved {
        background: rgba(22, 101, 52, .12);
        color: #166534;
    }

    .review-primary-btn,
    .review-secondary-btn,
    .review-save-btn,
    .review-warning-btn {
        align-items: center;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        font-family: inherit;
        font-size: .82rem;
        font-weight: 800;
        justify-content: center;
        min-height: 38px;
        padding: .55rem .9rem;
        text-decoration: none;
    }

    .review-primary-btn {
        background: var(--fns-navy);
        border: 1px solid var(--fns-navy);
        color: #fff;
    }

    .review-secondary-btn {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        color: var(--fns-navy);
    }

    .review-warning-btn {
        background: rgba(201, 153, 26, .16);
        border: 1px solid rgba(201, 153, 26, .35);
        color: #7a5b0b;
    }

    .review-plan-warning {
        align-items: flex-start;
        background: #fff7df;
        border: 1px solid #e5bc4f;
        border-radius: 8px;
        color: #734b04;
        display: inline-flex;
        flex-direction: column;
        gap: .1rem;
        max-width: 255px;
        min-height: 38px;
        padding: .45rem .65rem;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(115, 75, 4, .12);
    }

    .review-plan-warning strong {
        font-size: .78rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .review-plan-warning span {
        color: #8a6413;
        font-size: .68rem;
        font-weight: 800;
        line-height: 1.15;
    }

    .review-plan-warning:hover {
        background: #fff1c2;
        border-color: #d69a18;
        color: #5f3d03;
    }

    .review-save-btn {
        background: var(--fns-green);
        border: 1px solid var(--fns-green);
        color: #fff;
    }

    .review-balance-lock {
        align-items: center;
        background: #fff7df;
        border: 1px solid #e2b846;
        border-radius: 8px;
        color: #815500;
        display: inline-flex;
        font-size: .82rem;
        font-weight: 900;
        min-height: 2.55rem;
        padding: .48rem .8rem;
        white-space: nowrap;
    }

    .review-balance-alert {
        align-items: flex-start;
        background: #fff8e7;
        border: 1px solid #e7c35b;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(17, 27, 51, .06);
        color: #6f4b05;
        display: flex;
        flex-direction: column;
        gap: .18rem;
        margin: -0.35rem 0 1rem;
        padding: .8rem 1rem;
    }

    .review-balance-alert strong {
        color: #4b3400;
        font-size: .9rem;
        font-weight: 900;
    }

    .review-balance-alert span {
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .review-drawer-toggle {
        gap: .45rem;
        bottom: 1.15rem;
        box-shadow: 0 14px 34px rgba(17, 27, 51, .2);
        position: fixed;
        right: 1.15rem;
        z-index: 90;
    }

    .review-drawer-toggle span {
        align-items: center;
        background: var(--fns-gold);
        border-radius: 999px;
        color: var(--fns-navy-deep);
        display: inline-flex;
        font-size: .72rem;
        font-weight: 900;
        justify-content: center;
        min-width: 1.35rem;
        padding: .05rem .4rem;
    }

    .review-drawer-backdrop {
        background: rgba(17, 27, 51, .42);
        bottom: 0;
        display: flex;
        justify-content: flex-end;
        left: 0;
        position: fixed;
        right: 0;
        top: 0;
        z-index: 95;
    }

    .review-drawer-backdrop[hidden] {
        display: none;
    }

    .review-drawer {
        background: #f8fafc;
        border-left: 1px solid rgba(17, 27, 51, .12);
        box-shadow: -22px 0 48px rgba(17, 27, 51, .18);
        display: flex;
        flex-direction: column;
        height: 100vh;
        max-width: 620px;
        overflow: hidden;
        width: min(620px, 100%);
    }

    .review-drawer-head {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid var(--fns-gray-200);
        display: flex;
        justify-content: space-between;
        padding: 1rem;
    }

    .review-drawer-head span {
        color: #8b6a12;
        font-size: .72rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .review-drawer-head h3 {
        color: var(--fns-navy);
        font-size: 1rem;
        font-weight: 900;
        margin: .15rem 0 0;
    }

    .review-drawer-close {
        align-items: center;
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        color: var(--fns-gray-600);
        cursor: pointer;
        display: inline-flex;
        font-size: 1.4rem;
        height: 38px;
        justify-content: center;
        line-height: 1;
        width: 38px;
    }

    .review-drawer .review-panel {
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        margin: 0;
        overflow: auto;
        padding: 1rem;
    }

    .review-modal-backdrop {
        align-items: center;
        background: rgba(17, 27, 51, .52);
        bottom: 0;
        display: flex;
        justify-content: center;
        left: 0;
        padding: 1rem;
        position: fixed;
        right: 0;
        top: 0;
        z-index: 100;
    }

    .review-modal-backdrop[hidden] {
        display: none;
    }

    .review-modal {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(17, 27, 51, .28);
        max-height: 90vh;
        max-width: 720px;
        overflow: auto;
        width: min(720px, 100%);
    }

    .review-modal-head {
        align-items: center;
        border-bottom: 1px solid var(--fns-gray-200);
        display: flex;
        justify-content: space-between;
        padding: .9rem 1rem;
    }

    .review-modal-head h3,
    .review-panel h3 {
        color: var(--fns-navy);
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }

    .review-modal-head button {
        background: transparent;
        border: 0;
        color: var(--fns-gray-600);
        cursor: pointer;
        font-size: 1.5rem;
        line-height: 1;
    }

    .review-modal-body,
    .review-field,
    .review-comment-form {
        display: grid;
        gap: .8rem;
    }

    .review-modal-body {
        padding: 1rem;
    }

    .review-field > span {
        color: var(--fns-navy);
        font-size: .82rem;
        font-weight: 800;
    }

    .review-field textarea,
    .review-comment-form textarea {
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        font-family: inherit;
        font-size: .88rem;
        padding: .7rem;
        resize: vertical;
        width: 100%;
    }

    .reviewer-picker {
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        display: grid;
        max-height: 310px;
        overflow: auto;
        padding: .35rem;
    }

    .reviewer-picker label {
        align-items: center;
        border-radius: 7px;
        display: flex;
        gap: .65rem;
        padding: .55rem;
    }

    .reviewer-picker label:hover {
        background: var(--fns-gray-100);
    }

    .reviewer-picker strong,
    .reviewer-picker small {
        display: block;
    }

    .reviewer-picker small {
        color: var(--fns-gray-600);
        font-size: .72rem;
    }

    .review-panel {
        display: grid;
        gap: .8rem;
    }

    .review-round {
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        display: grid;
        gap: .8rem;
        padding: .85rem;
    }

    .review-round.is-current {
        border-color: rgba(201, 153, 26, .42);
        box-shadow: inset 3px 0 0 var(--fns-gold);
    }

    .review-panel-head {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .review-panel-head span:first-child {
        color: #8b6a12;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .review-panel-meta {
        align-items: center;
        color: var(--fns-gray-600);
        display: flex;
        flex-wrap: wrap;
        font-size: .8rem;
        gap: .65rem;
        justify-content: flex-end;
        text-align: right;
    }

    .review-round-line {
        color: #8b6a12;
        font-weight: 800;
        white-space: nowrap;
    }

    .review-note {
        background: #fff8e5;
        border: 1px solid #f1dc9a;
        border-radius: 8px;
        color: #6f520d;
        font-size: .84rem;
        margin: 0;
        padding: .65rem .75rem;
    }

    .reviewer-list {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }

    .reviewer-list span {
        background: #eef2f7;
        border-radius: 999px;
        color: #475569;
        font-size: .76rem;
        font-weight: 700;
        padding: .25rem .6rem;
    }

    .review-comment-form {
        align-items: end;
        grid-template-columns: 1fr auto;
    }

    .review-comments {
        display: grid;
        gap: .65rem;
    }

    .review-comment {
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        padding: .75rem;
    }

    .review-comment-top {
        align-items: start;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .review-comment-top strong {
        color: var(--fns-navy);
        display: block;
        font-size: .86rem;
    }

    .review-comment p {
        color: #111827;
        font-size: .9rem;
        margin: .55rem 0;
        white-space: pre-wrap;
    }

    .review-comment-actions {
        justify-content: flex-start;
    }

    .review-comment-actions span,
    .review-comment-actions button {
        color: var(--fns-gray-600);
        font-size: .78rem;
    }

    .review-comment-actions button {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 999px;
        cursor: pointer;
        font-family: inherit;
        font-weight: 800;
        padding: .25rem .6rem;
    }

    .review-comment-actions button.is-agreed {
        background: rgba(26, 74, 46, .1);
        border-color: rgba(26, 74, 46, .22);
        color: var(--fns-green);
    }

    .preview-topic-nav {
        position: fixed;
        left: 1.25rem;
        bottom: 1.25rem;
        z-index: 45;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        margin: 0;
        border: 1px solid #d9e1ec;
        border-radius: 999px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        padding: .42rem;
        backdrop-filter: blur(10px);
    }

    .preview-topic-nav > span {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
    }

    .preview-topic-nav > div {
        display: flex;
        flex-wrap: nowrap;
        justify-content: center;
        gap: .35rem;
    }

    .preview-topic-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.35rem;
        border: 1px solid #d8e0ea;
        border-radius: 999px;
        background: #fff;
        color: #13213b;
        padding: .42rem 1rem;
        font-size: .86rem;
        font-weight: 900;
        text-decoration: none;
        transition: background .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease;
    }

    .preview-topic-link:hover {
        border-color: #c29014;
        background: #fff8e7;
        color: #111827;
    }

    .preview-topic-link.is-active {
        border-color: #13213b;
        background: #13213b;
        color: #fff;
        box-shadow: 0 7px 18px rgba(19, 33, 59, .18);
    }

    [data-preview-topic],
    .preview-topic-anchor {
        scroll-margin-top: 5.75rem;
    }

    .preview-topic-anchor {
        display: block;
        height: 0;
    }

    .review-empty {
        border: 1px dashed var(--fns-gray-200);
        border-radius: 8px;
        color: var(--fns-gray-600);
        font-size: .85rem;
        padding: .8rem;
        text-align: center;
    }

    @media (max-width: 640px) {
        .review-drawer-toggle {
            bottom: .85rem;
            left: 1rem;
            right: 1rem;
            width: auto;
        }

        .preview-topic-nav {
            left: .75rem;
            right: .75rem;
            bottom: 4.7rem;
            border-radius: 14px;
        }

        .preview-topic-nav > div {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            width: 100%;
        }

        .preview-topic-link {
            width: 100%;
            padding-left: .35rem;
            padding-right: .35rem;
        }
    }

    .income-preview {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        color: #111827;
        font-family: 'Noto Sans Lao', ui-sans-serif, system-ui, sans-serif;
        padding-bottom: 5.25rem;
    }

    .paper {
        background: #fff;
        border: 1px solid #d8dce3;
        border-radius: 8px;
        max-width: 100%;
        box-shadow: 0 3px 14px rgba(17, 24, 39, .06);
        overflow-x: auto;
        padding: 1.2rem;
    }

    .plan-year-paper {
        border-color: #cfd8e5;
        box-sizing: border-box;
        min-width: 0;
        padding: clamp(2.5rem, 8vw, 150px) 1.2rem 34px;
        position: relative;
        width: 100%;
    }

    .plan-year-table {
        color: #111;
        font-size: .78rem;
        min-width: 1120px;
        table-layout: fixed;
        width: 1120px;
    }

    .plan-year-code-col {
        width: 56px;
    }

    .plan-year-name-col {
        width: auto;
    }

    .plan-year-money-col {
        width: 150px;
    }

    .plan-year-table th,
    .plan-year-table td {
        border-color: #000;
        line-height: 1.2;
        padding: 4px 5px;
    }

    .plan-year-table th {
        background: #fff;
        font-weight: 800;
        white-space: normal;
    }

    .plan-year-head-row th {
        height: 48px;
        vertical-align: middle;
    }

    .plan-year-head-row th span {
        display: block;
    }

    .plan-year-head-row .plan-year-code-head {
        line-height: 1.45;
        padding-top: 8px;
        vertical-align: top;
    }

    .plan-year-budget-head {
        font-size: .78rem;
        height: 28px;
        text-align: center;
    }

    .plan-year-budget-row th {
        height: 28px;
        text-align: center;
    }

    .plan-year-overall-row td {
        background: #ccffff;
        font-weight: 900;
    }

    .plan-year-overall-label {
        color: #000;
    }

    .plan-year-overall-row .num {
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .plan-year-overall-row td:first-child {
        border-right-color: #000;
    }

    .plan-year-overall-row td:nth-child(2) {
        text-align: center;
    }

    .plan-year-root-row td {
        background: #ccffcc;
        font-weight: 900;
    }

    .report-table.plan-year-table .plan-year-root-row .num,
    .report-table.plan-year-table .plan-year-group-row .num,
    .report-table.plan-year-table .plan-year-grand-total .num {
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .plan-year-code-cell {
        color: #111;
        font-family: inherit;
        font-weight: 700;
        white-space: nowrap;
    }

    .plan-year-code-main {
        color: #111827;
        font-style: italic;
        font-weight: 900;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .plan-year-name {
        min-width: 0;
        padding-left: 4px !important;
        text-align: left;
    }

    .plan-year-grand-total td {
        border-top-width: 2px;
    }

    .plan-year-title-block {
        margin: 14px 0 28px;
        text-align: center;
    }

    .plan-year-title-block p {
        color: #111;
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.3;
        margin: .05rem 0 0;
    }

    .plan-year-report-title {
        margin: 0;
    }

    .income-summary-paper {
        break-inside: avoid;
    }

    .income-summary-paper .plan-year-report-title {
        color: #000;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.45;
        margin: 0 0 .35rem;
        text-align: center;
    }

    .income-summary-org {
        margin-bottom: .55rem;
    }

    .income-summary-org strong {
        color: #111827;
        display: block;
        font-size: .82rem;
        font-weight: 800;
        line-height: 1.55;
    }

    .income-summary-table {
        font-size: .78rem;
        min-width: 1180px;
    }

    .income-summary-num-col {
        width: 40px;
    }

    .income-summary-money-col {
        width: 150px;
    }

    .income-summary-bal-col {
        width: 100px;
    }

    .income-summary-note-col {
        width: 120px;
    }

    .income-overview-paper {
        break-inside: avoid;
    }

    .income-overview-paper .official-header {
        align-items: flex-start;
        display: grid;
        grid-template-columns: minmax(180px, 370px) minmax(360px, 1fr) minmax(180px, 370px);
        margin: 0 0 16px;
        min-height: 96px;
    }

    .income-overview-paper .org-left {
        grid-column: 1;
        padding-top: 3rem;
    }

    .income-overview-paper .nation-right {
        grid-column: 2;
        justify-self: center;
        min-width: 0;
        padding-top: 0;
    }

    .income-overview-paper .official-header strong {
        color: #000;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.72;
    }

    .income-overview-paper .nation-right span {
        color: #000;
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.55;
    }

    .income-overview-paper .signature-grid {
        margin-top: 1.65rem;
    }

    .income-overview-paper .signature {
        display: flex;
        flex-direction: column;
        font-size: .9rem;
        line-height: 1.55;
        min-width: 0;
        padding: 0 .5rem;
    }

    .income-overview-paper .signature span {
        order: 1;
    }

    .income-overview-paper .signature strong {
        font-weight: 700;
        order: 2;
        white-space: normal;
    }

    .income-overview-paper .signature div {
        border-bottom: 0;
        height: 7.5rem;
        margin: 0;
        order: 3;
    }

    .plan-year-page-number {
        display: none;
        font-size: .76rem;
        font-weight: 700;
        margin-top: .35rem;
        text-align: center;
    }

    .report-top,
    .official-header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .8rem;
    }

    .plan-year-paper .official-header {
        align-items: flex-start;
        display: grid;
        grid-template-columns: minmax(180px, 370px) minmax(360px, 1fr) minmax(180px, 370px);
        margin: 0 0 20px;
        min-height: clamp(96px, 11vw, 172px);
    }

    .plan-year-paper .org-left {
        grid-column: 1;
        padding-top: clamp(3rem, 6.6vw, 104px);
    }

    .plan-year-paper .nation-right {
        grid-column: 2;
        justify-self: center;
        min-width: 0;
        padding-top: 0;
    }

    .report-top strong,
    .official-header strong,
    .official-header span {
        display: block;
        line-height: 1.55;
    }

    .nation-right {
        text-align: center;
        min-width: 360px;
    }

    .nation-right span {
        font-size: .72rem;
    }

    .plan-year-paper .official-header strong {
        color: #000;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.72;
    }

    .plan-year-paper .nation-right span {
        color: #000;
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.55;
    }

    @media (max-width: 1100px) {
        .plan-year-paper .official-header {
            grid-template-columns: minmax(0, 1fr);
            min-height: 0;
            row-gap: .75rem;
        }

        .plan-year-paper .org-left,
        .plan-year-paper .nation-right {
            grid-column: 1;
            justify-self: stretch;
            padding-top: 0;
        }

        .plan-year-paper .nation-right {
            order: -1;
        }
    }

    .report-title,
    .summary-caption,
    .detail-title {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.45;
        margin: .9rem 0 .7rem;
        text-align: center;
    }

    .summary-caption {
        margin-top: 1rem;
        text-align: right;
    }

    .detail-title {
        text-align: left;
    }

    .detail-paper {
        break-inside: avoid;
    }

    .detail-paper .detail-title {
        color: #000;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.45;
        margin: 0 0 .55rem;
        text-align: center;
    }

    .expense-paper {
        break-inside: avoid;
    }

    .expense-summary-title {
        color: #000;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.45;
        margin: 0 0 .35rem;
        text-align: center;
    }

    .expense-section-title {
        color: #000;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.45;
        margin: 0 0 .35rem;
        text-align: center;
    }

    .expense-org {
        margin-bottom: .55rem;
    }

    .expense-org strong {
        color: #111827;
        display: block;
        font-size: .82rem;
        font-weight: 800;
        line-height: 1.55;
    }

    .expense-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: .55rem;
    }

    .expense-detail-header .expense-org {
        margin-bottom: 0;
    }

    .expense-signatures {
        margin-top: 1.65rem;
    }

    .expense-signatures .signature {
        display: flex;
        flex-direction: column;
        font-size: .9rem;
        line-height: 1.55;
        min-width: 0;
        padding: 0 .5rem;
    }

    .expense-signatures .signature span {
        order: 1;
    }

    .expense-signatures .signature strong {
        font-weight: 700;
        order: 2;
        white-space: normal;
    }

    .expense-signatures .signature div {
        border-bottom: 0;
        height: 7.5rem;
        margin: 0;
        order: 3;
    }

    .unit-label {
        align-self: flex-start;
        color: #374151;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .expense-subtitle {
        color: #111827;
        font-size: .86rem;
        font-weight: 800;
        line-height: 1.45;
        margin: 1rem 0 .45rem;
    }

    .report-table {
        border-collapse: collapse;
        font-size: .78rem;
        min-width: 1080px;
        width: 100%;
    }

    .plan-table {
        min-width: 920px;
    }

    .expense-summary-table {
        min-width: 980px;
    }

    .expense-detail-table {
        margin-bottom: .95rem;
        min-width: 920px;
    }

    .salary-paper {
        break-inside: auto;
    }

    .salary-paper .official-header {
        align-items: flex-start;
        display: grid;
        grid-template-columns: minmax(180px, 370px) minmax(360px, 1fr) minmax(180px, 370px);
        margin: 0 0 16px;
        min-height: 96px;
    }

    .salary-paper .org-left {
        grid-column: 1;
        padding-top: 3rem;
    }

    .salary-paper .nation-right {
        grid-column: 2;
        justify-self: center;
        min-width: 0;
        padding-top: 0;
    }

    .salary-paper .official-header strong {
        color: #000;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.72;
    }

    .salary-paper .nation-right span {
        color: #000;
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.55;
    }

    .salary-title {
        margin-bottom: .35rem;
    }

    .salary-meta {
        display: flex;
        font-size: .78rem;
        font-weight: 700;
        gap: 1rem;
        justify-content: center;
        margin: 0 0 .75rem;
    }

    .salary-table {
        font-size: .72rem;
        min-width: 1240px;
    }

    .salary-table th,
    .salary-table td {
        padding: .32rem .4rem;
    }

    .salary-code-cell {
        font-variant-numeric: tabular-nums;
        min-width: 38px;
    }

    .salary-code-main {
        font-style: italic;
        font-weight: 900;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .salary-name {
        min-width: 260px;
        padding-left: 4px !important;
        text-align: left;
    }

    .salary-root-row td {
        background: #d9ffc7;
        font-weight: 800;
    }

    .salary-grand-total td {
        border-top-width: 2px;
    }

    .report-table.salary-table .salary-group-row .num,
    .report-table.salary-table .salary-grand-total .num {
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .salary-signatures {
        column-gap: 4rem;
        margin-top: 2.65rem;
    }

    .salary-signature {
        display: flex;
        flex-direction: column;
        font-size: .9rem;
        line-height: 1.55;
        min-width: 0;
        padding: 0 .5rem;
    }

    .salary-signature span {
        order: 1;
    }

    .salary-signature strong {
        font-weight: 700;
        order: 2;
        white-space: normal;
    }

    .salary-signature div {
        order: 3;
    }

    .salary-caption {
        text-align: right;
    }

    .balance-paper {
        break-inside: avoid;
    }

    .balance-title {
        color: #000;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.45;
        margin: 0 0 .35rem;
        text-align: center;
    }

    .balance-org {
        margin-bottom: .55rem;
    }

    .balance-org strong {
        color: #111827;
        display: block;
        font-size: .82rem;
        font-weight: 800;
        line-height: 1.55;
    }

    .balance-num-col {
        width: 30px;
    }

    .balance-money-col {
        width: 96px;
    }

    .report-table.balance-table {
        font-size: .72rem;
        min-width: 1180px;
    }

    .report-table.balance-table th,
    .report-table.balance-table td {
        padding: .3rem .36rem;
    }

    .report-table.balance-table th {
        white-space: normal;
    }

    .report-table.balance-table td {
        min-height: 2.1rem;
    }

    .balance-total-row td {
        border-top-width: 2px;
    }

    .balance-signatures {
        margin-top: 1.65rem;
    }

    .balance-paper .balance-signatures {
        column-gap: 4rem;
        margin-top: 2.65rem;
    }

    .balance-paper .signature {
        display: flex;
        flex-direction: column;
        font-size: .9rem;
        line-height: 1.55;
        min-width: 0;
        padding: 0 .5rem;
    }

    .balance-paper .signature span {
        order: 1;
    }

    .balance-paper .signature strong {
        font-weight: 700;
        order: 2;
        white-space: normal;
    }

    .balance-paper .signature div {
        border-bottom: 0;
        height: 7.5rem;
        margin: 0;
        order: 3;
    }

    .plan-year-paper .balance-signatures {
        column-gap: 4rem;
        margin-top: 2.65rem;
        min-width: 1120px;
        width: 1120px;
    }

    .plan-year-paper .signature {
        display: flex;
        flex-direction: column;
        font-size: .9rem;
        line-height: 1.55;
        min-width: 0;
        padding: 0 .5rem;
    }

    .plan-year-paper .signature span {
        order: 1;
    }

    .plan-year-paper .signature strong {
        font-weight: 700;
        order: 2;
        white-space: normal;
    }

    .plan-year-paper .signature div {
        border-bottom: 0;
        height: 7.5rem;
        margin: 0;
        order: 3;
    }

    .balance-page-number {
        display: none;
        font-size: .76rem;
        font-weight: 700;
        margin-top: .35rem;
        text-align: center;
    }

    .report-table th,
    .report-table td {
        border: 1px solid #9ca3af;
        line-height: 1.35;
        padding: .42rem .5rem;
        vertical-align: middle;
    }

    .report-table th {
        background: #f3f4f6;
        color: #111827;
        font-weight: 800;
        text-align: center;
        white-space: nowrap;
    }

    .num {
        font-variant-numeric: tabular-nums;
        text-align: right;
        white-space: nowrap;
    }

    .center {
        text-align: center;
    }

    .indent {
        padding-left: 1.35rem !important;
    }

    .grand-row td,
    .total-row td {
        background: #eef2f7;
        font-weight: 800;
    }

    .section-row td {
        background: #f8fafc;
        font-weight: 700;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 2.1rem;
        text-align: center;
    }

    .signature {
        font-size: .76rem;
    }

    .signature div {
        border-bottom: 1px dotted #6b7280;
        height: 3rem;
        margin-bottom: .35rem;
    }

    .salary-signature div {
        border-bottom: 0;
        height: 7.5rem;
        margin: 0;
    }

    @media print {
        @page {
            margin: 8mm;
            size: A4 landscape;

            @bottom-center {
                color: #111;
                content: counter(page);
                font-family: 'Noto Sans Lao', ui-sans-serif, system-ui, sans-serif;
                font-size: 8pt;
            }
        }

        html,
        body {
            background: #fff !important;
            font-family: 'Noto Sans Lao', ui-sans-serif, system-ui, sans-serif !important;
            margin: 0 !important;
        }

        .fns-topnav,
        .fns-sidebar,
        .fns-alert,
        .fns-page-title,
        .review-toolbar,
        .preview-topic-nav,
        .review-panel,
        .review-drawer-backdrop,
        .review-modal-backdrop,
        [data-print-plan] {
            display: none !important;
        }

        .fns-main,
        .fns-content {
            margin: 0 !important;
            padding: 0 !important;
        }

        .fns-main > div:has(.fns-page-title) {
            display: none !important;
        }

        .income-preview {
            gap: 1.25rem;
            padding-bottom: 0;
            width: 100%;
            zoom: 0.74;
        }

        .paper {
            border: 0;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
            padding: 0;
            page-break-after: always;
        }

        .paper:last-child {
            page-break-after: auto;
        }

        thead {
            display: table-row-group;
        }

        .report-table {
            min-width: 0;
        }

        .report-table th,
        .report-table td {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .report-table th,
        .grand-row td,
        .plan-year-root-row td,
        .total-row td,
        .section-row td,
        .salary-root-row td {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .plan-year-paper {
            min-height: 0;
            padding: clamp(1.5rem, 5vw, 80px) 0 20px;
        }

        .plan-year-paper .official-header {
            min-height: 72px;
        }

        .plan-year-paper .official-header strong {
            white-space: nowrap;
        }

        .plan-year-page-number,
        .balance-page-number {
            display: none !important;
        }

        .plan-year-paper .balance-signatures {
            min-width: 0;
            width: 100%;
        }

        .plan-year-name {
            min-width: 0;
        }

        .detail-paper,
        .income-overview-paper,
        .income-summary-paper,
        .expense-paper,
        .balance-paper,
        .salary-paper {
            min-height: 0;
        }

        .salary-signatures,
        .expense-signatures,
        .balance-signatures,
        .plan-year-paper .balance-signatures,
        .balance-paper .balance-signatures {
            column-gap: 1.5rem;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const printButton = document.querySelector('[data-print-plan]');

        if (printButton) {
            printButton.addEventListener('click', function () {
                window.print();
            });
        }

        const topicLinks = Array.from(document.querySelectorAll('[data-preview-topic-link]'));
        const topicSections = Array.from(document.querySelectorAll('[data-preview-topic]'));
        const setActiveTopic = (topic) => {
            topicLinks.forEach((link) => {
                link.classList.toggle('is-active', link.dataset.previewTopicLink === topic);
            });
        };

        topicLinks.forEach((link) => {
            link.addEventListener('click', function (event) {
                const target = document.querySelector(link.getAttribute('href'));
                if (!target) return;

                event.preventDefault();
                target.scrollIntoView({behavior: 'smooth', block: 'start'});
                setActiveTopic(link.dataset.previewTopicLink);
                history.replaceState(null, '', link.getAttribute('href'));
            });
        });

        if (topicSections.length) {
            const observer = new IntersectionObserver((entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

                if (visible?.target?.dataset.previewTopic) {
                    setActiveTopic(visible.target.dataset.previewTopic);
                }
            }, {
                rootMargin: '-18% 0px -60% 0px',
                threshold: [0.02, 0.1, 0.25, 0.5],
            });

            topicSections.forEach((section) => observer.observe(section));
        }

        const modal = document.querySelector('[data-review-modal]');
        const openButton = document.querySelector('[data-open-review-modal]');

        if (modal && openButton) {
            const closeButtons = modal.querySelectorAll('[data-close-review-modal]');
            const openModal = () => {
                modal.hidden = false;
            };
            const closeModal = () => {
                modal.hidden = true;
            };

            openButton.addEventListener('click', openModal);
            closeButtons.forEach((button) => button.addEventListener('click', closeModal));
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        const drawer = document.querySelector('[data-review-drawer]');
        const openDrawerButton = document.querySelector('[data-open-review-drawer]');

        if (drawer && openDrawerButton) {
            const closeDrawerButtons = drawer.querySelectorAll('[data-close-review-drawer]');
            const openDrawer = () => {
                drawer.hidden = false;
                document.body.style.overflow = 'hidden';
            };
            const closeDrawer = () => {
                drawer.hidden = true;
                document.body.style.overflow = '';
            };

            openDrawerButton.addEventListener('click', openDrawer);
            closeDrawerButtons.forEach((button) => button.addEventListener('click', closeDrawer));
            drawer.addEventListener('click', function (event) {
                if (event.target === drawer) {
                    closeDrawer();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !drawer.hidden) {
                    closeDrawer();
                }
            });
        }
    });
</script>
@endsection
