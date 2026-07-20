@if(empty($table['rows']))
    <div class="ai-empty">ບໍ່ມີຫຼັກສູດ — ກະລຸນາຕັ້ງຄ່າກ່ອນ</div>
@else
    <section class="ai-degree-section" data-degree-section="{{ $table['level'] }}" data-table-key="{{ $table['key'] }}">
        <div class="ai-degree-head">
            <div>
                <h3>{{ $table['label'] }}</h3>
                <p>{{ count($table['rows']) }} ຫຼັກສູດ</p>
            </div>
            <div class="ai-degree-total">
                <span>ລວມສ່ວນນີ້</span>
                <b data-degree-total="{{ $table['key'] }}">0</b>
                <em data-degree-amount="{{ $table['key'] }}">0 ກີບ</em>
            </div>
        </div>

        <div class="ai-table-scroll">
            <table class="ai-program-table" data-program-table="{{ $table['key'] }}" data-level="{{ $table['level'] }}">
                <thead>
                    <tr>
                        <th class="ai-program-name-col">ຫຼັກສູດ</th>
                        @foreach($table['columns'] as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th class="ai-col-total">ລວມ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($table['rows'] as $row)
                        <tr class="ai-table-row" data-name="{{ $row['search'] }}" data-filter-state="zero">
                            <th class="ai-program-name-col" scope="row">
                                <span class="ai-canonical-name">{{ $row['label'] }}</span>
                                @if(!empty($row['names']))
                                    <span class="ai-display-names">{{ $row['names'] }}</span>
                                @endif
                            </th>
                            @foreach($table['columns'] as $column)
                                @php $cell = $row['cells'][$column['key']] ?? null; @endphp
                                <td data-cell-col="{{ $column['key'] }}">
                                    @if($cell)
                                        <label class="ai-row ai-cell-input @if($cell['warn']) is-warn @endif @if($cell['value'] <= 0) is-zero @endif"
                                               title="{{ $cell['name'] }}"
                                               data-name="{{ $cell['search'] }}"
                                               data-save-kind="count"
                                               data-input-prefix="{{ $cell['inputPrefix'] }}"
                                               data-program-id="{{ $cell['programId'] }}">
                                            @if($cell['warn'])
                                                <span class="ai-warn-dot" title="ຍັງບໍ່ໄດ້ຕັ້ງຄ່າໜ່ວຍກິດ / ລາຄາ"></span>
                                            @endif
                                            <input type="number"
                                                   name="{{ $cell['inputPrefix'] }}[{{ $cell['programId'] }}]"
                                                   min="0"
                                                   inputmode="numeric"
                                                   value="{{ $cell['value'] }}"
                                                   class="ai-num"
                                                   data-sec="{{ $cell['section'] }}"
                                                   data-col="{{ $column['key'] }}"
                                                   data-table-key="{{ $table['key'] }}"
                                                   data-level="{{ $table['level'] }}"
                                                   data-amount-rate="{{ $cell['amountRate'] }}"
                                                   data-income-amount="{{ $cell['amount'] }}"
                                                   data-initial="{{ $cell['value'] }}">
                                            <span class="ai-cell-money" data-cell-amount>{{ number_format((float) $cell['amount'], 0) }} ກີບ</span>
                                        </label>
                                    @else
                                        <span class="ai-empty-cell">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="ai-row-total">
                                <span data-row-total>0</span>
                                <em data-row-amount>0 ກີບ</em>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ລວມ</th>
                        @foreach($table['columns'] as $column)
                            <td data-col-total="{{ $column['key'] }}">0</td>
                        @endforeach
                        <td>
                            <span data-table-total>0</span>
                            <em data-table-amount>0 ກີບ</em>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
@endif
