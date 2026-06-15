@extends('layouts.admin')

@section('title', 'ສ້າງແຜນລາຍຮັບ')
@section('page-title', 'ສ້າງແຜນລາຍຮັບວິຊາການ')

@section('content')

    <div class="aic-wrap">
        <div class="aic-card">
            {{-- Editorial header --}}
            <div class="aic-head">
                <span class="aic-kicker">ສ້າງແຜນໃໝ່</span>
                <h2 class="aic-title">ປະເມີນລາຍຮັບ</h2>
                <p class="aic-sub">ກຳນົດສົກປີງົບປະມານ ແລະ ໝາຍເຫດ (ຖ້າມີ) —
                    ຫຼັງຈາກນັ້ນທ່ານສາມາດປ້ອນຈຳນວນນັກສຶກສາແຕ່ລະສາຂາໄດ້.</p>
            </div>

            <form method="POST" action="{{ route('head_of_finance.academic-income.store') }}" class="aic-body">
                @csrf

                <div class="aic-group">
                    <label class="aic-label" for="fiscal_year">ສົກປີງົບປະມານ <span class="aic-req">*</span></label>
                    <input id="fiscal_year" type="number" name="fiscal_year" min="2000" max="2100"
                        value="{{ old('fiscal_year', date('Y')) }}"
                        class="aic-input aic-input-year @error('fiscal_year') is-invalid @enderror" required autofocus>
                    @error('fiscal_year')<p class="aic-error">{{ $message }}</p>@enderror
                    <p class="aic-hint">ປ້ອນເລກ 4 ຫຼັກ ເຊັ່ນ {{ date('Y') }}</p>
                </div>

                <div class="aic-foot">
                    <a href="{{ route('head_of_finance.manage-plan.index') }}"
                        class="aic-btn aic-btn-secondary">ຍົກເລີກ</a>
                    <button type="submit" class="aic-btn aic-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                        ສ້າງແຜນ
                    </button>
                </div>
            </form>
        </div>

        {{-- Info call-out --}}
        <aside class="aic-callout">
            <div class="aic-callout-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4M12 8h.01" />
                </svg>
            </div>
            <div>
                <strong class="aic-callout-title">ຂັ້ນຕອນຕໍ່ໄປ</strong>
                <p class="aic-callout-sub">ຫຼັງຈາກສ້າງແຜນແລ້ວ ໃຫ້ກົດ <em>ປ້ອນຂໍ້ມູນ</em> ເພື່ອລະບຸຈຳນວນນັກສຶກສາແຕ່ລະສາຂາ —
                    ລະບົບຈະຄຳນວນລາຍຮັບໂດຍອັດຕະໂນມັດ.</p>
            </div>
        </aside>
    </div>

    <style>
        .aic-wrap {
            max-width: 560px;
        }

        .aic-card {
            background: #fff;
            border: 1px solid var(--fns-gray-200);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(17, 27, 51, 0.05);
        }

        .aic-head {
            padding: 1.4rem 1.6rem 1.1rem;
            background: linear-gradient(135deg, var(--fns-navy-deep), var(--fns-navy-mid));
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .aic-head::after {
            content: "";
            position: absolute;
            right: -30px;
            top: -30px;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(201, 153, 26, 0.18), transparent 65%);
            pointer-events: none;
        }

        .aic-kicker {
            font-family: 'Cinzel', serif;
            font-size: 0.7rem;
            letter-spacing: 0.22em;
            color: var(--fns-gold-light, #e7be4f);
            text-transform: uppercase;
            font-weight: 700;
        }

        .aic-title {
            margin: .35rem 0 .5rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .aic-sub {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.78;
            line-height: 1.55;
            max-width: 42ch;
        }

        .aic-body {
            padding: 1.4rem 1.6rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .aic-group {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .aic-label {
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--fns-gray-600);
            letter-spacing: .02em;
        }

        .aic-req {
            color: #b91c1c;
        }

        .aic-opt {
            font-weight: 500;
            color: var(--fns-gray-400);
            font-size: 0.7rem;
            margin-left: .3rem;
        }

        .aic-input {
            padding: .65rem .85rem;
            border: 1px solid var(--fns-gray-200);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--fns-navy);
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            resize: vertical;
        }

        .aic-input:focus {
            border-color: var(--fns-navy-light);
            box-shadow: 0 0 0 3px rgba(46, 63, 110, 0.12);
        }

        .aic-input.is-invalid {
            border-color: #dc2626;
        }

        .aic-input-year {
            max-width: 180px;
            font-family: 'Cinzel', serif;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-align: center;
        }

        .aic-error {
            color: #b91c1c;
            font-size: 0.75rem;
            margin: 0;
        }

        .aic-hint {
            color: var(--fns-gray-400);
            font-size: 0.72rem;
            margin: 0;
        }

        .aic-foot {
            display: flex;
            gap: .6rem;
            justify-content: flex-end;
            margin-top: .4rem;
            padding-top: 1rem;
            border-top: 1px dashed var(--fns-gray-200);
        }

        .aic-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .6rem 1.1rem;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, border-color .15s, transform .1s;
        }

        .aic-btn svg {
            width: 14px;
            height: 14px;
        }

        .aic-btn-primary {
            background: var(--fns-navy);
            color: #fff;
        }

        .aic-btn-primary:hover {
            background: var(--fns-navy-light);
        }

        .aic-btn-secondary {
            background: #fff;
            color: var(--fns-navy);
            border-color: var(--fns-gray-200);
        }

        .aic-btn-secondary:hover {
            background: var(--fns-gray-100);
        }

        /* Callout */
        .aic-callout {
            display: flex;
            gap: .85rem;
            align-items: flex-start;
            margin-top: 1rem;
            padding: .95rem 1.1rem;
            background: rgba(26, 39, 68, 0.04);
            border: 1px solid rgba(26, 39, 68, 0.08);
            border-left: 3px solid var(--fns-gold);
            border-radius: 10px;
        }

        .aic-callout-icon {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: rgba(201, 153, 26, 0.14);
            color: #8b6a12;
            border-radius: 7px;
        }

        .aic-callout-icon svg {
            width: 16px;
            height: 16px;
        }

        .aic-callout-title {
            font-size: 0.85rem;
            color: var(--fns-navy);
            display: block;
            margin-bottom: .15rem;
        }

        .aic-callout-sub {
            margin: 0;
            font-size: 0.8rem;
            color: var(--fns-gray-600);
            line-height: 1.55;
        }
    </style>

@endsection
