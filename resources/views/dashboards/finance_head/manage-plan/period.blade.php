@extends('layouts.admin')

@section('title', $periodTitle . ' - ' . $planningYear->year)
@section('page-title', $periodTitle)

@section('content')
<div class="period-page">
    <div class="period-toolbar">
        <div>
            <span class="period-eyebrow">ແຜນປີ {{ $planningYear->year }}</span>
            <h2>{{ $periodTitle }}</h2>
            <p>{{ $planningYear->name }}</p>
        </div>
        <div class="period-actions">
            <a href="{{ route('head_of_finance.manage-plan.index') }}" class="period-btn period-btn-secondary">ກັບຄືນ</a>
        </div>
    </div>

    <section class="period-placeholder" id="{{ $periodKey }}">
        <h3>{{ $periodTitle }}</h3>
        <p>ໜ້ານີ້ຖືກແຍກອອກຈາກໜ້າ Preview ແລ້ວ. ລໍຖ້າກຳນົດວ່າຈະໃຫ້ໜ້ານີ້ເຮັດຫຍັງ.</p>
    </section>
</div>

<style>
    .period-page {
        display: grid;
        gap: 1rem;
    }

    .period-toolbar {
        align-items: center;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        padding: 1rem;
    }

    .period-eyebrow {
        color: #64748b;
        display: block;
        font-size: .82rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .period-toolbar h2 {
        color: #0f172a;
        font-size: 1.35rem;
        margin: 0;
    }

    .period-toolbar p {
        color: #64748b;
        margin: .15rem 0 0;
    }

    .period-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: flex-end;
    }

    .period-btn {
        border-radius: 7px;
        font-weight: 700;
        padding: .55rem .85rem;
        text-decoration: none;
    }

    .period-btn-secondary {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .period-btn-primary {
        background: #0f5132;
        border: 1px solid #0f5132;
        color: #fff;
    }

    .period-placeholder {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
    }

    .period-placeholder h3 {
        color: #0f172a;
        font-size: 1.4rem;
        margin: 0 0 .5rem;
    }

    .period-placeholder p {
        color: #64748b;
        margin: 0 auto;
        max-width: 620px;
    }

    @media (max-width: 720px) {
        .period-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .period-actions {
            justify-content: flex-start;
        }
    }
</style>
@endsection
