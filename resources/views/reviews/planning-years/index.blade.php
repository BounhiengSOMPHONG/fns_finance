@extends('layouts.admin')

@section('title', 'Review plans')
@section('page-title', 'Review plans')

@section('content')
<div class="review-inbox">
    @forelse($assignments as $assignment)
        @php
            $round = $assignment->reviewRound;
            $plan = $round?->planningYear;
            $isCurrent = $plan && (int) $plan->current_review_round_id === (int) $round->id;
            $isPending = $plan?->status === 'PENDING_REVIEW' && $isCurrent;
            $isModifying = $plan?->status === 'MODIFYING';
        @endphp
        @if($plan)
            <a href="{{ route('reviews.planning-years.show', $plan) }}" class="review-inbox-card">
                <div>
                    <span class="review-inbox-status {{ $isPending ? 'pending' : ($isModifying ? 'modifying' : 'closed') }}">
                        {{ $isPending ? 'ລໍຖ້າຄວາມເຫັນ' : ($isModifying ? 'ກຳລັງແກ້ໄຂ' : 'ປິດຮອບແລ້ວ') }}
                    </span>
                    <h2>ແຜນປີ {{ $plan->year }}</h2>
                    <p>{{ $plan->name }}</p>
                </div>
                <div class="review-inbox-meta">
                    <strong>Round {{ $round->round_number }}</strong>
                    <span>ສົ່ງໂດຍ {{ $round->requester?->full_name ?? $round->requester?->username ?? '-' }}</span>
                    <span>{{ optional($round->requested_at)->format('d/m/Y H:i') }}</span>
                </div>
            </a>
        @endif
    @empty
        <div class="review-inbox-empty">
            <strong>ຍັງບໍ່ມີແຜນທີ່ຕ້ອງກວດ</strong>
            <span>ເມື່ອ Head of Finance ສົ່ງຂໍຄວາມເຫັນ ລາຍການຈະສະແດງຢູ່ນີ້.</span>
        </div>
    @endforelse

    @if($assignments->hasPages())
        <div class="review-inbox-pages">
            {{ $assignments->links() }}
        </div>
    @endif
</div>

<style>
    .review-inbox {
        display: grid;
        gap: .9rem;
    }

    .review-inbox-card,
    .review-inbox-empty {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(17, 27, 51, .06);
        padding: 1rem;
    }

    .review-inbox-card {
        color: var(--fns-navy);
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        text-decoration: none;
        transition: border-color .16s, box-shadow .16s;
    }

    .review-inbox-card:hover {
        border-color: rgba(201, 153, 26, .45);
        box-shadow: 0 12px 28px rgba(17, 27, 51, .1);
    }

    .review-inbox-card h2 {
        font-size: 1.05rem;
        font-weight: 800;
        margin: .35rem 0 .15rem;
    }

    .review-inbox-card p,
    .review-inbox-meta,
    .review-inbox-empty span {
        color: var(--fns-gray-600);
        font-size: .82rem;
    }

    .review-inbox-status {
        border-radius: 999px;
        display: inline-flex;
        font-size: .72rem;
        font-weight: 800;
        padding: .2rem .55rem;
    }

    .review-inbox-status.pending {
        background: rgba(201, 153, 26, .14);
        color: #8b6a12;
    }

    .review-inbox-status.closed {
        background: #eef2f7;
        color: #475569;
    }

    .review-inbox-status.modifying {
        background: rgba(14, 165, 233, .12);
        color: #0369a1;
    }

    .review-inbox-meta {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        gap: .2rem;
        text-align: right;
    }

    .review-inbox-meta strong {
        color: var(--fns-navy);
    }

    .review-inbox-empty {
        display: grid;
        gap: .25rem;
    }

    .review-inbox-pages {
        margin-top: .4rem;
    }

    @media (max-width: 720px) {
        .review-inbox-card {
            flex-direction: column;
        }

        .review-inbox-meta {
            align-items: flex-start;
            text-align: left;
        }
    }
</style>
@endsection
