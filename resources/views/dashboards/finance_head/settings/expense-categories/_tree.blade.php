{{-- Recursive template-category tree.
     Variables: $nodes (collection of ExpenseCategoryTemplate), $depth (int, 0-based) --}}
@php $depth = $depth ?? 0; @endphp

@foreach($nodes as $cat)
@php
    // Visual styling steps down per nesting level.
    $bg     = ['var(--fns-navy)', '#f1f5f9', '#eff6ff', '#f8fafc'][min($depth, 3)];
    $color  = $depth === 0 ? '#fff' : 'var(--fns-navy)';
    $weight = $depth === 0 ? 700 : 600;
    $border = $depth === 0 ? '' : 'border-left:3px solid ' . (['', 'var(--fns-navy)', '#93c5fd', '#cbd5e1'][min($depth, 3)]) . ';';
    $editBtnBg = $depth === 0 ? 'rgba(255,255,255,0.2)' : '#e2e8f0';
    $editBtnColor = $depth === 0 ? '#fff' : '#334155';
    $addBtnBg = $depth === 0 ? 'rgba(255,255,255,0.15)' : '#dbeafe';
    $addBtnColor = $depth === 0 ? '#fff' : '#1e40af';
@endphp
<div style="margin-bottom:{{ $depth === 0 ? '1rem' : '0' }};margin-left:{{ $depth === 0 ? '0' : '12px' }};{{ $border }}">
    <div style="display:flex;align-items:center;gap:8px;background:{{ $bg }};color:{{ $color }};padding:{{ $depth === 0 ? '8px 12px' : '6px 12px' }};{{ $depth === 0 ? 'border-radius:6px 6px 0 0;' : 'border-bottom:1px solid #e2e8f0;' }}">
        <span style="font-weight:{{ $weight }};flex:1;font-size:{{ $depth >= 2 ? '0.9rem' : '1rem' }};">{{ $cat->ref_code }} {{ $depth === 0 ? '— ' : '' }}{{ $cat->name }}</span>
        <div style="display:flex;gap:6px;margin-left:12px;">
            <button type="button" class="fns-btn fns-btn-sm" style="background:{{ $editBtnBg }};color:{{ $editBtnColor }};font-size:0.7rem;"
                onclick="openEditCatModal({{ $cat->id }}, '{{ addslashes($cat->ref_code) }}', '{{ addslashes($cat->name) }}', {{ $cat->sort_order }})">ແກ້</button>
            <button type="button" class="fns-btn fns-btn-sm" style="background:{{ $addBtnBg }};color:{{ $addBtnColor }};font-size:0.7rem;"
                onclick="openCatModal({{ $cat->id }}, '{{ addslashes($cat->ref_code) }}')">+ ໝວດຍ່ອຍ</button>
            <form method="POST" action="{{ route('head_of_finance.settings.expense-categories.destroy', $cat) }}" style="display:inline;"
                onsubmit="return confirm('ລຶບໝວດ {{ addslashes($cat->name) }} ແລະ ໝວດຍ່ອຍທັງໝົດ?')">
                @csrf @method('DELETE')
                <button type="submit" class="fns-btn fns-btn-sm" style="background:rgba(220,38,38,0.7);color:#fff;font-size:0.7rem;">ລຶບ</button>
            </form>
        </div>
    </div>

    @if($cat->children->isNotEmpty())
        @include('dashboards.finance_head.settings.expense-categories._tree', [
            'nodes' => $cat->children,
            'depth' => $depth + 1,
        ])
    @endif
</div>
@endforeach
