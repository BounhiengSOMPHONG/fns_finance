@php
    /**
     * $node        SalaryBudgetCode
     * $depth       int  (0=root, 1, 2, 3=leaf)
     * $entryMap    Collection keyed by budget_code_id
     * $nodeAgg     array  keyed by budget_code_id → [persons,atm,cash,total,annual]
     * $editable    bool
     * $planId      int
     * $parentChain string  e.g. ",1,3,"
     */
    $parentChain ??= ',';
    $chain = $parentChain . $node->id . ',';

    $codes = ['', '', '', ''];
    if ($depth < 4) $codes[$depth] = $node->code;

    $cellStyle = 'text-align:center;padding:4px 2px;font-size:0.72rem;font-weight:700;border-right:1px solid #e2e8f0;';

    $rowBg = match($depth) {
        0       => 'background:var(--fns-navy);color:#fff;font-weight:700;',
        1       => 'background:#f1f5f9;color:var(--fns-navy);font-weight:700;',
        2       => 'background:#eff6ff;color:#1e40af;font-weight:600;',
        default => 'background:#fff;',
    };

    $namePad = 8 + ($depth * 14);
    $agg     = $nodeAgg[$node->id] ?? ['persons'=>0,'atm'=>0,'cash'=>0,'total'=>0,'annual'=>0];
@endphp

@if($node->is_leaf)
    @php
        $entry = $entryMap->get($node->id);
        $mode  = $node->annual_mode;
    @endphp
    <tr class="leaf-row"
        data-entry-id="{{ $entry?->id }}"
        data-annual-mode="{{ $mode }}"
        data-parent-chain="{{ $chain }}"
        data-atm="{{ $entry?->atm_amount ?? 0 }}"
        data-cash="{{ $entry?->cash_amount ?? 0 }}"
        data-monthly="{{ $entry?->monthly_total ?? 0 }}"
        data-annual="{{ $entry?->annual_amount ?? 0 }}"
        style="{{ $rowBg }}transition:background 0.3s;">

        @foreach($codes as $c)
        <td style="{{ $cellStyle }}">{{ $c }}</td>
        @endforeach

        <td style="padding:4px {{ $namePad }}px 4px {{ $namePad }}px;border-right:1px solid #e2e8f0;font-size:0.78rem;">
            {{ $node->name }}
        </td>

        <td style="text-align:center;padding:2px;border-right:1px solid #e2e8f0;">
            @if($editable)
            <input class="salary-input si-persons" type="number" min="0"
                value="{{ $entry?->person_count ?? 0 }}"
                style="width:46px;border:none;background:transparent;text-align:center;font-size:0.78rem;padding:2px;outline:none;font-family:inherit;">
            @else
            <span style="font-size:0.78rem;">{{ ($entry?->person_count ?? 0) > 0 ? number_format($entry->person_count, 0) : '' }}</span>
            @endif
        </td>

        <td style="text-align:right;padding:2px 4px;border-right:1px solid #e2e8f0;">
            @if($editable)
            <input class="salary-input si-atm" type="number" min="0" step="1"
                value="{{ $entry?->atm_amount ?? 0 }}"
                style="width:120px;border:none;background:transparent;text-align:right;font-size:0.78rem;padding:2px;outline:none;font-family:inherit;">
            @else
            <span style="font-size:0.78rem;">{{ ($entry?->atm_amount ?? 0) > 0 ? number_format($entry->atm_amount, 0) : '' }}</span>
            @endif
        </td>

        <td style="text-align:right;padding:2px 4px;border-right:1px solid #e2e8f0;">
            @if($editable)
            <input class="salary-input si-cash" type="number" min="0" step="1"
                value="{{ $entry?->cash_amount ?? 0 }}"
                style="width:104px;border:none;background:transparent;text-align:right;font-size:0.78rem;padding:2px;outline:none;font-family:inherit;">
            @else
            <span style="font-size:0.78rem;">{{ ($entry?->cash_amount ?? 0) > 0 ? number_format($entry->cash_amount, 0) : '' }}</span>
            @endif
        </td>

        <td class="cell-total" style="text-align:right;padding:4px 6px;border-right:1px solid #e2e8f0;font-weight:600;font-size:0.78rem;">
            {{ ($entry?->monthly_total ?? 0) > 0 ? number_format($entry->monthly_total, 0) : '' }}
        </td>

        <td style="text-align:right;padding:2px 4px;font-weight:700;font-size:0.78rem;">
            @if($editable && $mode === 'direct')
            <input class="salary-input si-annual" type="number" min="0" step="1"
                value="{{ $entry?->annual_amount ?? 0 }}"
                style="width:130px;border:none;background:transparent;text-align:right;font-size:0.78rem;padding:2px;outline:none;font-family:inherit;font-weight:700;">
            @else
            <span class="cell-annual">{{ ($entry?->annual_amount ?? 0) > 0 ? number_format($entry->annual_amount, 0) : '' }}</span>
            @endif
        </td>
    </tr>

@else
    <tr class="summary-row" data-node-id="{{ $node->id }}" style="{{ $rowBg }}">
        @foreach($codes as $c)
        <td style="{{ $cellStyle }}{{ $depth === 0 ? 'color:#fff;' : '' }}">{{ $c }}</td>
        @endforeach

        <td style="padding:5px 8px 5px {{ $namePad }}px;border-right:1px solid #e2e8f0;font-size:{{ $depth === 0 ? '0.82' : '0.78' }}rem;">
            {{ $node->name }}
        </td>
        <td style="text-align:center;padding:5px 4px;border-right:1px solid #e2e8f0;font-size:0.78rem;" class="sum-persons">
            {{ $agg['persons'] > 0 ? number_format($agg['persons'], 0) : '' }}
        </td>
        <td style="text-align:right;padding:5px 6px;border-right:1px solid #e2e8f0;font-size:0.78rem;" class="sum-atm">
            {{ $agg['atm'] > 0 ? number_format($agg['atm'], 0) : '' }}
        </td>
        <td style="text-align:right;padding:5px 6px;border-right:1px solid #e2e8f0;font-size:0.78rem;" class="sum-cash">
            {{ $agg['cash'] > 0 ? number_format($agg['cash'], 0) : '' }}
        </td>
        <td style="text-align:right;padding:5px 6px;border-right:1px solid #e2e8f0;font-weight:700;font-size:0.78rem;" class="sum-total">
            {{ $agg['total'] > 0 ? number_format($agg['total'], 0) : '' }}
        </td>
        <td style="text-align:right;padding:5px 6px;font-weight:700;font-size:0.78rem;" class="sum-annual">
            {{ $agg['annual'] > 0 ? number_format($agg['annual'], 0) : '' }}
        </td>
    </tr>

    @foreach($node->children as $child)
        @include('dashboards.finance_head.salary._rows', [
            'node'        => $child,
            'depth'       => $depth + 1,
            'entryMap'    => $entryMap,
            'nodeAgg'     => $nodeAgg,
            'editable'    => $editable,
            'planId'      => $planId,
            'parentChain' => $chain,
        ])
    @endforeach
@endif
