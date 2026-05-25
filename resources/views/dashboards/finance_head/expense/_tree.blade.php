{{-- Variables: $topCategories (collection), $plan (ExpensePlan), $editable (bool), $chartOfAccounts (collection, only when editable) --}}
@foreach($topCategories as $mainCat)
<div class="exp-main-cat-block" style="margin-bottom:1rem;">

    {{-- Main category header row (Level 1) --}}
    <div class="exp-row exp-main-row" style="display:flex;align-items:center;gap:8px;background:var(--fns-navy);color:#fff;padding:8px 12px;border-radius:6px 6px 0 0;">
        <span style="font-weight:700;flex:1;">{{ $mainCat->ref_code }} — {{ $mainCat->name }}</span>
        <span style="font-weight:700;white-space:nowrap;">{{ number_format($mainCat->subtotal(), 0) }} ກີບ</span>
        @if($editable)
        <div style="display:flex;gap:6px;margin-left:12px;">
            <button class="fns-btn fns-btn-sm" style="background:rgba(255,255,255,0.2);color:#fff;font-size:0.7rem;"
                onclick="openEditCatModal({{ $mainCat->id }}, '{{ addslashes($mainCat->ref_code) }}', '{{ addslashes($mainCat->name) }}', {{ $mainCat->sort_order }}, '{{ $mainCat->formula_type }}', '{{ $mainCat->col_a_label }}', '{{ $mainCat->col_b_label }}', '{{ $mainCat->col_c_label }}')">ແກ້</button>
            <button class="fns-btn fns-btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;font-size:0.7rem;"
                onclick="openCatModal({{ $plan->id }}, {{ $mainCat->id }}, '{{ addslashes($mainCat->ref_code) }}')">+ ໝວດຍ່ອຍ</button>
            <form method="POST" action="{{ route('head_of_finance.expense-categories.destroy', $mainCat) }}" style="display:inline;"
                onsubmit="return confirm('ລຶບໝວດ {{ addslashes($mainCat->name) }} ທັງໝົດ?')">
                @csrf @method('DELETE')
                <button type="submit" class="fns-btn fns-btn-sm" style="background:rgba(220,38,38,0.7);color:#fff;font-size:0.7rem;">ລຶບ</button>
            </form>
        </div>
        @endif
    </div>

    {{-- Level-2 subcategories --}}
    @foreach($mainCat->children as $sub)
    <div class="exp-sub-block" style="border-left:3px solid var(--fns-navy);margin-left:12px;">
        {{-- Level-2 header --}}
        <div style="display:flex;align-items:center;gap:8px;background:#f1f5f9;padding:6px 12px;border-bottom:1px solid #e2e8f0;">
            <span style="font-weight:600;flex:1;color:var(--fns-navy);">{{ $sub->ref_code }} {{ $sub->name }}</span>
            <span style="font-weight:600;color:var(--fns-navy);white-space:nowrap;">{{ number_format($sub->subtotal(), 0) }} ກີບ</span>
            @if($editable)
            <div style="display:flex;gap:5px;margin-left:10px;">
                <button class="fns-btn fns-btn-sm" style="background:#e2e8f0;color:#334155;font-size:0.7rem;"
                    onclick="openEditCatModal({{ $sub->id }}, '{{ addslashes($sub->ref_code) }}', '{{ addslashes($sub->name) }}', {{ $sub->sort_order }}, '{{ $sub->formula_type }}', '{{ $sub->col_a_label }}', '{{ $sub->col_b_label }}', '{{ $sub->col_c_label }}')">ແກ້</button>
                <button class="fns-btn fns-btn-sm" style="background:#dbeafe;color:#1e40af;font-size:0.7rem;"
                    onclick="openCatModal({{ $plan->id }}, {{ $sub->id }}, '{{ addslashes($sub->ref_code) }}')">+ ໝວດຍ່ອຍ</button>
                <form method="POST" action="{{ route('head_of_finance.expense-categories.destroy', $sub) }}" style="display:inline;"
                    onsubmit="return confirm('ລຶບໝວດຍ່ອຍ {{ addslashes($sub->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger" style="font-size:0.7rem;">ລຶບ</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Level-3 sub-subcategories --}}
        @foreach($sub->children as $subsub)
        <div style="border-left:3px solid #93c5fd;margin-left:16px;">
            {{-- Level-3 header --}}
            <div style="display:flex;align-items:center;gap:8px;background:#eff6ff;padding:5px 12px;border-bottom:1px solid #dbeafe;">
                <span style="font-weight:600;flex:1;color:#1e40af;font-size:0.9rem;">{{ $subsub->ref_code }} {{ $subsub->name }}</span>
                <span style="font-weight:600;color:#1e40af;white-space:nowrap;font-size:0.9rem;">{{ number_format($subsub->subtotal(), 0) }} ກີບ</span>
                @if($editable)
                <div style="display:flex;gap:5px;margin-left:10px;">
                    <button class="fns-btn fns-btn-sm" style="background:#dbeafe;color:#1e40af;font-size:0.7rem;"
                        onclick="openEditCatModal({{ $subsub->id }}, '{{ addslashes($subsub->ref_code) }}', '{{ addslashes($subsub->name) }}', {{ $subsub->sort_order }}, '{{ $subsub->formula_type }}', '{{ $subsub->col_a_label }}', '{{ $subsub->col_b_label }}', '{{ $subsub->col_c_label }}')">ແກ້</button>
                    <form method="POST" action="{{ route('head_of_finance.expense-categories.destroy', $subsub) }}" style="display:inline;"
                        onsubmit="return confirm('ລຶບໝວດຍ່ອຍ {{ addslashes($subsub->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger" style="font-size:0.7rem;">ລຶບ</button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Level-3 items --}}
            @if($editable)
                @include('dashboards.finance_head.expense._items_grid', ['items' => $subsub->items, 'category' => $subsub])
            @else
                @if($subsub->items->isNotEmpty())
                    @include('dashboards.finance_head.expense._items_table', ['items' => $subsub->items, 'editable' => false, 'category' => $subsub])
                @else
                    <p style="padding:8px 12px;font-size:0.78rem;color:#94a3b8;margin:0;">ຍັງບໍ່ມີລາຍການ</p>
                @endif
            @endif
        </div>
        @endforeach

        {{-- Level-2 direct items (only when no Level-3 children) --}}
        @if($sub->children->isEmpty())
            @if($editable)
                @include('dashboards.finance_head.expense._items_grid', ['items' => $sub->items, 'category' => $sub])
            @else
                @if($sub->items->isNotEmpty())
                    @include('dashboards.finance_head.expense._items_table', ['items' => $sub->items, 'editable' => false, 'category' => $sub])
                @else
                    <p style="padding:8px 12px;font-size:0.78rem;color:#94a3b8;margin:0;">ຍັງບໍ່ມີລາຍການ</p>
                @endif
            @endif
        @endif
    </div>
    @endforeach

    {{-- Level-1 direct items (main cat has no children) --}}
    @if($mainCat->children->isEmpty())
        @if($editable)
            @include('dashboards.finance_head.expense._items_grid', ['items' => $mainCat->items, 'category' => $mainCat])
        @else
            @if($mainCat->items->isNotEmpty())
                @include('dashboards.finance_head.expense._items_table', ['items' => $mainCat->items, 'editable' => false, 'category' => $mainCat])
            @else
                <p style="padding:8px 12px;font-size:0.78rem;color:#94a3b8;margin:0;border-left:3px solid #e2e8f0;margin-left:12px;">ຍັງບໍ່ມີຂໍ້ມູນ</p>
            @endif
        @endif
    @endif

</div>
@endforeach
