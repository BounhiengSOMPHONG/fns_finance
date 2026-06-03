{{-- One editable ref-code row in the modal list. Vars: $rc, $isCat (bool: level-1 = ໝວດຫຼັກ) --}}
<div class="rc-row {{ $isCat ? 'rc-cat' : 'rc-child' }}">
    <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.update', $rc) }}"
          id="rcform-{{ $rc->id }}" class="rc-edit">
        @csrf @method('PATCH')
        <input type="text" name="code" value="{{ $rc->code }}" class="fns-input rc-code" required>
        <input type="text" name="label" value="{{ $rc->label }}" class="fns-input rc-label" placeholder="ຊື່">
    </form>
    <button type="submit" form="rcform-{{ $rc->id }}" class="fns-btn fns-btn-sm fns-btn-primary">ບັນທຶກ</button>
    <form method="POST" action="{{ route('head_of_finance.expense-ref-codes.destroy', $rc) }}"
          style="display:inline;" onsubmit="return confirm('ລຶບ {{ $rc->code }}?{{ $isCat ? ' (ລາຍການຫຼັກໃນໝວດນີ້ຈະຄ້າງ)' : '' }}')">
        @csrf @method('DELETE')
        <button type="submit" class="fns-btn fns-btn-sm fns-btn-danger">ລຶບ</button>
    </form>
</div>
