@extends('layouts.admin')

@section('title', 'ຄ່າທຳນຽມ ແລະ ບໍລິການ (3-6)')
@section('page-title', 'ຄ່າທຳນຽມ ແລະ ບໍລິການ (3-6)')

@section('content')

    @if(session('success'))
        <div class="fns-alert fns-alert-success" style="margin-bottom:1.25rem;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('head_of_finance.settings.income-rates.update') }}">
        @csrf
        @method('PATCH')

        {{-- Info banner --}}
        <div class="fns-card"
            style="margin-bottom:1.25rem; background:linear-gradient(135deg,var(--fns-navy) 0%,var(--fns-navy-mid) 100%); color:#fff; border:none;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    style="width:20px;height:20px;color:var(--fns-gold-light);flex-shrink:0;">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z"
                        clip-rule="evenodd" />
                </svg>
                <div>
                    <div
                        style="font-size:0.7rem; color:rgba(255,255,255,0.55); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.15rem;">
                        ຄຳອະທິບາຍ</div>
                    <div style="font-size:0.88rem; color:rgba(255,255,255,0.85);">
                        ອັດຕາທີ່ຕັ້ງໃນໜ້ານີ້ຈະຖືກໃຊ້ໃນການຄຳນວນລາຍຮັບ ຂໍ້3–6 ໃນໜ້າ <strong>ປ້ອນຂໍ້ມູນ / ປະເມີນ</strong>
                    </div>
                </div>
            </div>
        </div>

        @php
            $items = [
                'item3' => ['icon' => '3', 'color' => '#6366f1', 'desc' => 'ສູດ: ຈຳນວນ ນ/ສ × ອັດຕາ'],
                'item4' => ['icon' => '4', 'color' => '#0ea5e9', 'desc' => 'ສູດ: ຈຳນວນ ນ/ສ ທັງໝົດ (1.2 + 1.4) × ອັດຕາ'],
                'item5' => ['icon' => '5', 'color' => '#10b981', 'desc' => 'ສູດ: ຈຳນວນ ນ/ສ ທັງໝົດ (1.2 + 1.4) × ອັດຕາ'],
                'item6' => ['icon' => '6', 'color' => '#f59e0b', 'desc' => 'ສູດ: ຈຳນວນ ນ/ສ × ອັດຕາ'],
            ];
        @endphp

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
            @foreach($items as $key => $meta)
                @php $row = $rates->get($key . '_rate'); @endphp
                <div class="fns-card" style="border-top:3px solid {{ $meta['color'] }};">
                    <div style="display:flex; align-items:center; gap:0.65rem; margin-bottom:1rem;">
                        <div
                            style="width:2rem; height:2rem; border-radius:7px; background:{{ $meta['color'] }}1a; color:{{ $meta['color'] }}; display:flex; align-items:center; justify-content:center; font-family:'Cinzel',serif; font-size:0.75rem; font-weight:700; flex-shrink:0;">
                            {{ $meta['icon'] }}</div>
                        <div>
                            <div style="font-size:0.78rem; font-weight:700; color:var(--fns-navy);">Item {{ $meta['icon'] }}
                            </div>
                            <div style="font-size:0.7rem; color:var(--fns-gray-400);">{{ $meta['desc'] }}</div>
                        </div>
                    </div>

                    <div class="fns-form-group">
                        <label class="fns-label">ຊື່ລາຍການ</label>
                        <input type="text" value="{{ $row?->label ?? '' }}"
                            class="fns-input"
                            style="background-color:#f1f5f9; color:#64748b; cursor:not-allowed;"
                            readonly>
                    </div>

                    <div class="fns-form-group" style="margin-bottom:0;">
                        <label class="fns-label">ອັດຕາຕໍ່ ນ/ສ (ກີບ)</label>
                        <div style="position:relative;">
                            <input type="number" name="{{ $key }}_rate" min="0" step="1"
                                value="{{ old($key . '_rate', $row ? (float) $row->rate : 0) }}"
                                class="fns-input @error($key . '_rate') fns-input-error @enderror" style="padding-right:3.5rem;"
                                required>
                            <span
                                style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); font-size:0.78rem; color:var(--fns-gray-400); pointer-events:none;">ກີບ</span>
                        </div>
                        @error($key . '_rate')
                            <div class="fns-input-hint fns-input-hint-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>

        <div style="display:flex; gap:0.5rem; align-items:center;">
            <button type="submit" class="fns-btn fns-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    style="width:15px;height:15px;">
                    <path fill-rule="evenodd"
                        d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                        clip-rule="evenodd" />
                </svg>
                ບັນທຶກອັດຕາ
            </button>
        </div>

    </form>
@endsection