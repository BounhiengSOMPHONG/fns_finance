@extends('layouts.admin')

@section('title', 'ໜ່ວຍກິດຕາມຫຼັກສູດ')
@section('page-title', 'ການຕັ້ງໜ່ວຍກິດຕາມຫຼັກສູດ')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <form method="GET" style="display:flex; gap:0.5rem;">
        <select name="degree_program_id" class="fns-input" style="width:220px;">
            <option value="">ທຸກສາຂາວິຊາ</option>
            @foreach($programs as $p)
                <option value="{{ $p->id }}" @selected(request('degree_program_id') == $p->id)>
                    [{{ $p->level_label }}] {{ $p->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="fns-btn fns-btn-secondary">ຄົ້ນຫາ</button>
        @if(request('degree_program_id'))
            <a href="{{ route('head_of_finance.settings.course-credits.index') }}" class="fns-btn fns-btn-secondary">ລ້າງ</a>
        @endif
    </form>
    <a href="{{ route('head_of_finance.settings.course-credits.create') }}" class="fns-btn fns-btn-primary">+ ເພີ່ມ</a>
</div>

<div class="fns-card">
    <table class="fns-table">
        <thead>
            <tr>
                <th>#</th>
                <th>ສາຂາວິຊາ</th>
                <th>ຈຳນວນໜ່ວຍກິດ</th>
                <th>ເລກທີເອກະສານ</th>
                <th>ປີທີ່ເລີ່ມໃຊ້</th>
                <th>ຈັດການ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($settings as $s)
            <tr>
                <td>{{ $settings->firstItem() + $loop->index }}</td>
                <td>
                    <span class="fns-badge {{ $s->degreeProgram->level === 'bachelor' ? 'fns-badge-blue' : ($s->degreeProgram->level === 'master' ? 'fns-badge-green' : 'fns-badge-purple') }}">
                        {{ $s->degreeProgram->level_label }}
                    </span>
                    {{ $s->degreeProgram->name }}
                </td>
                <td>
                    ປີ 2+: {{ $s->course_credit_unit }} ໜ່ວຍ
                    @if($s->year1_credit_unit)
                        <br><span style="color:#16a34a; font-size:0.8rem;">ປີ 1: {{ $s->year1_credit_unit }} ໜ່ວຍ ({{ round($s->year1_credit_unit / ($s->year1_credit_unit + $s->course_credit_unit) * 100) }}%)</span>
                    @endif
                </td>
                <td>{{ $s->gov_doc_id ?? '—' }}</td>
                <td>{{ $s->start_year }}</td>
                <td>
                    <a href="{{ route('head_of_finance.settings.course-credits.edit', $s) }}" class="fns-btn fns-btn-sm fns-btn-secondary">ແກ້ໄຂ</a>
                    <form method="POST" action="{{ route('head_of_finance.settings.course-credits.destroy', $s) }}" style="display:inline;"
                        onsubmit="return confirm('ລຶບລາຍການນີ້ບໍ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger">ລຶບ</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; color:#9ca3af;">ບໍ່ມີຂໍ້ມູນ</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:1rem;">{{ $settings->links() }}</div>
</div>
@endsection
