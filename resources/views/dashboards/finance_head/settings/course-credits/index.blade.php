@extends('layouts.admin')

@section('title', 'ລາຄາ & ໜ່ວຍກິດ & ມຊ%')
@section('page-title', 'ການຕັ້ງລາຄາ & ໜ່ວຍກິດ & ມຊ%')

@section('content')

@php
    $levelMeta = [
        'bachelor' => [
            'label' => 'ປ.ຕີ', 
            'full' => 'ປະລິນຍາຕີ (ປ.ຕີ)', 
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot' => 'bg-blue-400',
            'hover' => 'hover:border-blue-300 focus-within:border-blue-500 focus-within:ring-blue-500',
            'btn' => 'bg-blue-600 hover:bg-blue-700',
            'input_focus' => 'focus:border-blue-500 focus:ring-blue-500/20'
        ],
        'master'   => [
            'label' => 'ປ.ໂທ', 
            'full' => 'ປະລິນຍາໂທ (ປ.ໂທ)', 
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-400',
            'hover' => 'hover:border-emerald-300 focus-within:border-emerald-500 focus-within:ring-emerald-500',
            'btn' => 'bg-emerald-600 hover:bg-emerald-700',
            'input_focus' => 'focus:border-emerald-500 focus:ring-emerald-500/20'
        ],
        'phd'      => [
            'label' => 'ປ.ເອກ', 
            'full' => 'ປະລິນຍາເອກ (ປ.ເອກ)', 
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
            'dot' => 'bg-purple-400',
            'hover' => 'hover:border-purple-300 focus-within:border-purple-500 focus-within:ring-purple-500',
            'btn' => 'bg-purple-600 hover:bg-purple-700',
            'input_focus' => 'focus:border-purple-500 focus:ring-purple-500/20'
        ],
    ];
    $byLevel = $courseCredits->groupBy(fn($s) => $s->degreeProgram?->level);
@endphp

<div class="max-w-7xl mx-auto space-y-8 pb-12">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">ການຕັ້ງຄ່າທາງການເງິນ</h2>
            <p class="text-sm text-slate-500 mt-1">ຈັດການລາຄາໜ່ວຍກິດ, ເປີເຊັນ ມຊ ແລະ ຈຳນວນໜ່ວຍກິດຂອງແຕ່ລະຫຼັກສູດ.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Credit Price Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm border border-blue-100/50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">ລາຄາຕໍ່ໜ່ວຍກິດ</h3>
                        <p class="text-xs text-slate-500">ກຳນົດລາຄາ (ກີບ) ຕາມລະດັບການສຶກສາ</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 flex-1 flex flex-col gap-5">
                @foreach($levelMeta as $key => $meta)
                    @php $price = $prices->get($key); @endphp
                    @if($price)
                        <form method="POST" action="{{ route('head_of_finance.settings.credit-unit-price.update', $price) }}" class="relative group bg-white border border-slate-200 rounded-xl p-4 transition-all focus-within:ring-1 dirty-form {{ $meta['hover'] }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="level" value="{{ $key }}">
                            
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $meta['badge'] }}">
                                    {{ $meta['full'] }}
                                </span>
                                <button type="submit" class="opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity inline-flex items-center justify-center px-4 py-1.5 text-xs font-semibold rounded-lg text-white shadow-sm disabled:opacity-50 btn-save {{ $meta['btn'] }}">
                                    ບັນທຶກ
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">ລາຄາ / ໜ່ວຍກິດ (ກີບ)</label>
                                    <div class="relative">
                                        <input type="number" name="credit_unit_price" step="0.01" min="0" required
                                            value="{{ (float) $price->credit_unit_price }}" class="w-full pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 font-medium focus:bg-white focus:ring-2 transition-all text-right font-mono data-dirty {{ $meta['input_focus'] }}">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">₭</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">ເລກທີເອກະສານ</label>
                                    <input type="text" name="gov_doc_id" value="{{ $price->gov_doc_id }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:bg-white focus:ring-2 transition-all data-dirty {{ $meta['input_focus'] }}">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">ປີທີ່ໃຊ້</label>
                                    <input type="number" name="start_year" min="2000" max="2100" required
                                        value="{{ $price->start_year }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:bg-white focus:ring-2 transition-all data-dirty {{ $meta['input_focus'] }}">
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="flex items-center justify-between p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/50">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $meta['badge'] }}">
                                {{ $meta['full'] }}
                            </span>
                            <span class="text-sm text-slate-400 italic">ຍັງບໍ່ໄດ້ຕັ້ງຄ່າ</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- NUOL % Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-sm border border-purple-100/50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">ເປີເຊັນ ມຊ (%)</h3>
                        <p class="text-xs text-slate-500">ອັດຕາສ່ວນແບ່ງໃຫ້ ມຊ ຕາມລະດັບການສຶກສາ</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 flex-1 flex flex-col gap-5">
                @foreach($levelMeta as $key => $meta)
                    @php $n = $nuolPcts->get($key); @endphp
                    @if($n)
                        <form method="POST" action="{{ route('head_of_finance.settings.nuol-pct.update', $n) }}" class="relative group bg-white border border-slate-200 rounded-xl p-4 transition-all focus-within:ring-1 dirty-form {{ $meta['hover'] }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="level" value="{{ $key }}">
                            
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $meta['badge'] }}">
                                    {{ $meta['full'] }}
                                </span>
                                <button type="submit" class="opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity inline-flex items-center justify-center px-4 py-1.5 text-xs font-semibold rounded-lg text-white shadow-sm disabled:opacity-50 btn-save {{ $meta['btn'] }}">
                                    ບັນທຶກ
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">ເປີເຊັນ ມຊ (%)</label>
                                    <div class="relative">
                                        <input type="number" name="percentage" step="0.01" min="0" max="100" required
                                            value="{{ rtrim(rtrim(number_format($n->percentage * 100, 4, '.', ''), '0'), '.') }}" class="w-full pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 font-medium focus:bg-white focus:ring-2 transition-all text-right font-mono data-dirty {{ $meta['input_focus'] }}">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">%</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">ເລກທີເອກະສານ</label>
                                    <input type="text" name="gov_doc_id" value="{{ $n->gov_doc_id }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:bg-white focus:ring-2 transition-all data-dirty {{ $meta['input_focus'] }}">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">ປີທີ່ໃຊ້</label>
                                    <input type="number" name="start_year" min="2000" max="2100" required
                                        value="{{ $n->start_year }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:bg-white focus:ring-2 transition-all data-dirty {{ $meta['input_focus'] }}">
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="flex items-center justify-between p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/50">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $meta['badge'] }}">
                                {{ $meta['full'] }}
                            </span>
                            <span class="text-sm text-slate-400 italic">ຍັງບໍ່ໄດ້ຕັ້ງຄ່າ</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    </div>

    <!-- Course Credits List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden mt-8">
        <!-- Header & Toolbar -->
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm border border-emerald-100/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-semibold text-slate-800">ໜ່ວຍກິດຕາມຫຼັກສູດ</h3>
                        <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-xs font-medium">{{ $courseCredits->count() }} ລາຍການ</span>
                    </div>
                    <p class="text-xs text-slate-500">ຈຳນວນໜ່ວຍກິດຂອງແຕ່ລະສາຂາວິຊາ</p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="cc-filter" placeholder="ຄົ້ນຫາຫຼັກສູດ / ສາຂາວິຊາ..." autocomplete="off"
                        class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <a href="{{ route('head_of_finance.settings.course-credits.create') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 focus:ring-4 focus:ring-slate-200 transition-all shadow-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    ເພີ່ມໃໝ່
                </a>
            </div>
        </div>

        <!-- List Content -->
        <div class="p-6">
            @forelse($levelMeta as $key => $meta)
                @php $items = $byLevel->get($key); @endphp
                @if($items && $items->count())
                <div class="mb-8 last:mb-0" data-group>
                    
                    <div class="flex items-center gap-3 mb-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-semibold border shadow-sm {{ $meta['badge'] }}">
                            {{ $meta['full'] }}
                        </span>
                        <div class="h-px bg-slate-200 flex-1"></div>
                        <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $items->count() }} ສາຂາ</span>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4" data-group-rows>
                        @foreach($items as $s)
                            <div class="group flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-white hover:border-slate-300 hover:shadow-md transition-all cc-row" data-name="{{ \Illuminate\Support\Str::lower($s->degreeProgram?->name) }}">
                                
                                <div class="flex items-start gap-3 overflow-hidden pr-4">
                                    <div class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $meta['dot'] }}"></div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-semibold text-slate-800 truncate" title="{{ $s->degreeProgram?->name }}">
                                            {{ $s->degreeProgram?->name ?? '—' }}
                                        </h4>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-slate-500">
                                            @if($s->degreeProgram?->study_year)
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    {{ $s->degreeProgram->study_year }} ປີ
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                ເລີ່ມປີ {{ $s->start_year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-6 shrink-0">
                                    <div class="text-right">
                                        <div class="text-base font-bold text-slate-800 font-mono tracking-tight">{{ (float) $s->course_credit_unit }} <span class="text-xs font-normal text-slate-500 font-sans">ໜ່ວຍ</span></div>
                                        @if($s->year1_credit_unit)
                                            <div class="text-[11px] font-medium text-emerald-600">ປີ 1: {{ (float) $s->year1_credit_unit }}</div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                                        <a href="{{ route('head_of_finance.settings.course-credits.edit', $s) }}" 
                                           class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-blue-500" title="ແກ້ໄຂ">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </a>
                                        <form method="POST" action="{{ route('head_of_finance.settings.course-credits.destroy', $s) }}" onsubmit="return confirm('ທ່ານຕ້ອງການລຶບຂໍ້ມູນນີ້ແທ້ບໍ?')" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-red-500" title="ລຶບ">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                </div>
                @endif
            @empty
            @endforelse

            @if($courseCredits->isEmpty())
                <div class="text-center py-16 px-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800 mb-1">ຍັງບໍ່ມີຂໍ້ມູນໜ່ວຍກິດ</h3>
                    <p class="text-xs text-slate-500 mb-4">ກົດປຸ່ມ "ເພີ່ມໃໝ່" ເພື່ອເລີ່ມຕົ້ນເພີ່ມຂໍ້ມູນໜ່ວຍກິດຕາມຫຼັກສູດ.</p>
                </div>
            @endif
            
            <div id="cc-nores" class="hidden text-center py-12">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-sm text-slate-500">ບໍ່ພົບສາຂາວິຊາທີ່ກົງກັບການຄົ້ນຫາ</p>
            </div>
        </div>
    </div>
</div>

<style>
.dirty-form.is-dirty {
    border-color: #3b82f6 !important;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1), 0 2px 4px -1px rgba(59, 130, 246, 0.06) !important;
    background-color: #f8fafc !important;
}
.dirty-form.is-dirty .btn-save {
    opacity: 1 !important;
    pointer-events: auto !important;
    animation: pulse-soft 2s infinite;
}
.btn-save {
    pointer-events: none;
}
.group:hover .btn-save, .group:focus-within .btn-save {
    pointer-events: auto;
}

@keyframes pulse-soft {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dirty form handling
    document.querySelectorAll('.dirty-form').forEach(form => {
        const mark = () => form.classList.add('is-dirty');
        form.querySelectorAll('.data-dirty').forEach(el => {
            el.addEventListener('input', mark);
            el.addEventListener('change', mark);
        });
    });

    // Filtering
    const filter = document.getElementById('cc-filter');
    const nores  = document.getElementById('cc-nores');
    
    if (filter) {
        filter.addEventListener('input', () => {
            const q = filter.value.trim().toLowerCase();
            let any = false;
            
            document.querySelectorAll('.cc-row').forEach(r => {
                const name = (r.dataset.name || '').toLowerCase();
                const hit = !q || name.includes(q);
                r.style.display = hit ? '' : 'none';
                if (hit) any = true;
            });
            
            // Hide empty groups
            document.querySelectorAll('[data-group-rows]').forEach(g => {
                const visible = g.querySelector('.cc-row:not([style*="display: none"])');
                g.style.display = visible ? '' : 'none';
                
                const groupContainer = g.closest('[data-group]');
                if (groupContainer) {
                    groupContainer.style.display = visible ? '' : 'none';
                }
            });
            
            if (nores) {
                nores.classList.toggle('hidden', any || q === '');
            }
        });
    }
});
</script>
@endpush
@endsection
