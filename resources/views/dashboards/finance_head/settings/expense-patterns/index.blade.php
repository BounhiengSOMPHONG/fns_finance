@extends('layouts.admin')

@section('title', 'ສູດຄຳນວນ')
@section('page-title', 'ສູດຄຳນວນ')

@section('content')
<div class="space-y-6">
    @include('dashboards.finance_head.settings.expense-setup-tabs')

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    @php
        $fieldTypeLabels = [
            'number' => 'ຕົວເລກ',
            'text' => 'ຂໍ້ຄວາມ',
            'date' => 'ວັນທີ',
            'boolean' => 'ເລືອກແມ່ນ/ບໍ່',
        ];
    @endphp

    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">ສູດຄຳນວນລາຍຈ່າຍ</h2>
                <p class="mt-1 text-sm text-slate-500">ກຳນົດຊ່ອງກອກ, ຕົວຄູນ ແລະວິທີຄິດຍອດລວມຂອງລາຍຈ່າຍ.</p>
            </div>
            <button type="button" class="fns-btn fns-btn-primary" x-data @click="$dispatch('open-modal', 'add-pattern-modal')">
                + ເພີ່ມແບບຄຳນວນ
            </button>
        </div>
    </section>

    <x-modal name="add-pattern-modal" maxWidth="lg">
        <div class="px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900">ເພີ່ມແບບຄຳນວນໃໝ່</h3>
            <p class="mt-1 text-sm text-slate-500">ກຳນົດລະຫັດ, ຊື່ ແລະລາຍລະອຽດຂອງແບບຄຳນວນ.</p>
        </div>
        <form method="POST" action="{{ route('head_of_finance.settings.expense-patterns.store') }}" class="grid gap-4 px-6 py-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">ລະຫັດແບບ <span class="text-red-500">*</span></label>
                <input name="key" value="{{ old('key') }}" class="fns-input w-full" placeholder="monthly" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">ຊື່ແບບ <span class="text-red-500">*</span></label>
                <input name="name" value="{{ old('name') }}" class="fns-input w-full" placeholder="ລາຍຈ່າຍປະຈຳເດືອນ" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">ລາຍລະອຽດ</label>
                <input name="description" value="{{ old('description') }}" class="fns-input w-full" placeholder="ໃຊ້ກັບລາຍຈ່າຍປະເພດໃດ">
            </div>
            <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 w-fit">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                ໃຊ້ງານ
            </label>
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                <button type="button" class="fns-btn fns-btn-secondary" x-data @click="$dispatch('close-modal', 'add-pattern-modal')">
                    ຍົກເລີກ
                </button>
                <button type="submit" class="fns-btn fns-btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </x-modal>

    @forelse($patterns as $pattern)
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm" x-data="{ open: false }">
            <button type="button"
                    class="flex w-full flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 text-left hover:bg-slate-50/50 transition"
                    @click="open = !open">
                <div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 flex-shrink-0 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <h2 class="text-base font-semibold text-slate-900">{{ $pattern->name }}</h2>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $pattern->key }}</span>
                        <span class="rounded px-2 py-0.5 text-xs font-medium {{ $pattern->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $pattern->is_active ? 'ໃຊ້ງານ' : 'ປິດໄວ້' }}
                        </span>
                        <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                            {{ number_format($pattern->leaf_default_subsections_count) }} ກຸ່ມທີ່ໃຊ້
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ $pattern->description ?: 'ຍັງບໍ່ມີລາຍລະອຽດ.' }}</p>
                </div>
            </button>

            <div x-show="open" x-transition>
            <form method="POST" action="{{ route('head_of_finance.settings.expense-patterns.update', $pattern) }}" class="js-autosave-form grid gap-4 border-b border-slate-200 px-5 py-4 md:grid-cols-[220px_1fr_auto] md:items-end">
                @csrf
                @method('PATCH')
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">ຊື່ແບບ</label>
                    <input name="name" value="{{ old('name', $pattern->name) }}" class="fns-input" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">ລາຍລະອຽດ</label>
                    <input name="description" value="{{ old('description', $pattern->description) }}" class="fns-input">
                </div>
                <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked($pattern->is_active) class="rounded border-slate-300">
                    ໃຊ້ງານ
                </label>
            </form>

            <div class="overflow-x-auto px-5 py-4">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                            <th class="py-2 pr-3">ລະຫັດຊ່ອງ</th>
                            <th class="py-2 pr-3">ຊື່ຊ່ອງ</th>
                            <th class="py-2 pr-3">ປະເພດ</th>
                            <th class="py-2 pr-3">ລຳດັບ</th>
                            <th class="py-2 pr-3">ຄ່າຕັ້ງຕົ້ນ</th>
                            <th class="py-2 pr-3">ຕ້ອງກອກ</th>
                            <th class="py-2 pr-3">ຄຳນວນ</th>
                            <th class="py-2 pr-3">ໃຊ້ລວມ</th>
                            <th class="py-2 pr-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pattern->fields as $field)
                            @php
                                $formulaFields = collect($pattern->formula_schema['fields'] ?? []);
                            @endphp
                            <tr class="js-autosave-row" data-url="{{ route('head_of_finance.settings.expense-patterns.fields.update', [$pattern, $field->field_key]) }}">
                                <form method="POST" action="{{ route('head_of_finance.settings.expense-patterns.fields.update', [$pattern, $field->field_key]) }}" class="js-autosave-source-form">
                                    @csrf
                                    @method('PATCH')
                                    <td class="py-2 pr-3 font-mono text-xs text-slate-600">{{ $field->field_key }}</td>
                                    <td class="py-2 pr-3">
                                        <input name="default_label" value="{{ $field->default_label }}" class="fns-input min-w-44" required>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <select name="data_type" class="fns-input min-w-28" required>
                                            @foreach(['text', 'number', 'date', 'boolean'] as $type)
                                                <option value="{{ $type }}" @selected($field->data_type === $type)>{{ $fieldTypeLabels[$type] }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <input type="number" name="display_order" value="{{ $field->display_order }}" min="0" max="999" class="fns-input w-24" required>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <input name="default_value" value="{{ $field->default_value }}" class="fns-input min-w-32">
                                    </td>
                                    <td class="py-2 pr-3 text-center">
                                        <input type="checkbox" name="is_required" value="1" @checked($field->is_required) class="rounded border-slate-300">
                                    </td>
                                    <td class="py-2 pr-3 text-center">
                                        <input type="checkbox" name="is_calculated" value="1" @checked($field->is_calculated) class="rounded border-slate-300">
                                    </td>
                                    <td class="py-2 pr-3 text-center">
                                        <input type="checkbox" name="include_in_formula" value="1" @checked($formulaFields->contains($field->field_key)) class="rounded border-slate-300">
                                    </td>
                                    <td class="py-2 pr-3 whitespace-nowrap">
                                        <button type="button"
                                                class="fns-btn fns-btn-danger fns-btn-sm js-delete-setting"
                                                data-url="{{ route('head_of_finance.settings.expense-patterns.fields.destroy', [$pattern, $field->field_key]) }}"
                                                data-message="ລຶບຊ່ອງນີ້?">
                                            ລຶບ
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach

                        <tr class="bg-slate-50">
                            <form method="POST" action="{{ route('head_of_finance.settings.expense-patterns.fields.store', $pattern) }}">
                                @csrf
                                <td class="py-3 pr-3">
                                    <input name="field_key" class="fns-input min-w-36" placeholder="unit_price" required>
                                </td>
                                <td class="py-3 pr-3">
                                    <input name="default_label" class="fns-input min-w-44" placeholder="ລາຄາ/ໜ່ວຍ" required>
                                </td>
                                <td class="py-3 pr-3">
                                    <select name="data_type" class="fns-input min-w-28" required>
                                        <option value="number">ຕົວເລກ</option>
                                        <option value="text">ຂໍ້ຄວາມ</option>
                                        <option value="date">ວັນທີ</option>
                                        <option value="boolean">ເລືອກແມ່ນ/ບໍ່</option>
                                    </select>
                                </td>
                                <td class="py-3 pr-3">
                                    <input type="number" name="display_order" value="{{ ($pattern->fields->max('display_order') ?? 0) + 1 }}" min="0" max="999" class="fns-input w-24" required>
                                </td>
                                <td class="py-3 pr-3">
                                    <input name="default_value" class="fns-input min-w-32" placeholder="0">
                                </td>
                                <td class="py-3 pr-3 text-center">
                                    <input type="checkbox" name="is_required" value="1" checked class="rounded border-slate-300">
                                </td>
                                <td class="py-3 pr-3 text-center">
                                    <input type="checkbox" name="is_calculated" value="1" class="rounded border-slate-300">
                                </td>
                                <td class="py-3 pr-3 text-center">
                                    <input type="checkbox" name="include_in_formula" value="1" checked class="rounded border-slate-300">
                                </td>
                                <td class="py-3 pr-3">
                                    <button type="submit" class="fns-btn fns-btn-secondary fns-btn-sm">ເພີ່ມຊ່ອງ</button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
            ຍັງບໍ່ມີແບບຄຳນວນ.
        </div>
    @endforelse
</div>

@push('scripts')
<script>
document.addEventListener('click', async (event) => {
    const button = event.target.closest('.js-delete-setting');
    if (!button) return;
    if (!confirm(button.dataset.message || 'ລຶບລາຍການນີ້?')) return;

    const response = await fetch(button.dataset.url, {
        method: 'DELETE',
        headers: {
            'Accept': 'text/html',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });

    if (response.redirected) {
        window.location.href = response.url;
        return;
    }

    window.location.reload();
});

const autosaveTimers = new WeakMap();

function showAutosaveStatus(message, isOk = true) {
    const status = document.createElement('div');
    status.textContent = message;
    status.className = [
        'fixed bottom-4 right-4 z-50 rounded-md px-3 py-2 text-sm font-medium shadow-lg',
        isOk ? 'bg-slate-900 text-white' : 'bg-red-600 text-white',
    ].join(' ');

    document.body.appendChild(status);
    setTimeout(() => status.remove(), 1400);
}

function getAutosaveControls(scope) {
    return Array.from(scope.querySelectorAll('input, select, textarea'))
        .filter((input) => input.name && input.type !== 'hidden');
}

function snapshotAutosaveForm(form) {
    getAutosaveControls(form).forEach((input) => {
        input.dataset.originalValue = input.type === 'checkbox'
            ? (input.checked ? '1' : '0')
            : input.value;
    });
}

function hasAutosaveChange(input) {
    const value = input.type === 'checkbox'
        ? (input.checked ? '1' : '0')
        : input.value;

    return input.dataset.originalValue !== value;
}

function formHasAutosaveChanges(form) {
    return getAutosaveControls(form).some(hasAutosaveChange);
}

async function autosaveForm(form) {
    if (!form || form.dataset.saving === '1') return;

    form.dataset.saving = '1';

    try {
        const body = form.matches('form') ? new FormData(form) : new FormData();
        const action = form.matches('form') ? form.action : form.dataset.url;

        if (!form.matches('form')) {
            body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            body.append('_method', 'PATCH');

            getAutosaveControls(form).forEach((input) => {
                if (input.type === 'checkbox') {
                    if (input.checked) body.append(input.name, input.value || '1');
                    return;
                }

                body.append(input.name, input.value);
            });
        }

        const response = await fetch(action, {
            method: 'POST',
            body,
            headers: { 'Accept': 'text/html' },
        });

        if (!response.ok) throw new Error('Autosave failed');

        snapshotAutosaveForm(form);
    } catch (error) {
        throw error;
    } finally {
        form.dataset.saving = '0';
    }
}

async function autosaveDirtyForms() {
    const forms = Array.from(document.querySelectorAll('.js-autosave-form, .js-autosave-row'))
        .filter((form) => form.dataset.saving !== '1' && formHasAutosaveChanges(form));

    if (!forms.length) return;

    try {
        await Promise.all(forms.map((form) => autosaveForm(form)));
        showAutosaveStatus(forms.length === 1 ? 'ບັນທຶກແລ້ວ' : `ບັນທຶກ ${forms.length} ຈຸດແລ້ວ`);
    } catch (error) {
        showAutosaveStatus('ບັນທຶກບາງຈຸດບໍ່ສຳເລັດ', false);
    }
}

function queueAutosave(input) {
    const form = input.closest('.js-autosave-form, .js-autosave-row');
    if (!form || !hasAutosaveChange(input)) return;

    clearTimeout(autosaveTimers.get(document));
    autosaveTimers.set(document, setTimeout(autosaveDirtyForms, 450));
}

document.querySelectorAll('.js-autosave-form, .js-autosave-row').forEach(snapshotAutosaveForm);

document.addEventListener('submit', (event) => {
    const form = event.target.closest('.js-autosave-form, .js-autosave-source-form');
    if (!form) return;

    event.preventDefault();
    autosaveDirtyForms();
});

document.addEventListener('keydown', (event) => {
    const input = event.target.closest('.js-autosave-form input, .js-autosave-form select, .js-autosave-form textarea, .js-autosave-row input, .js-autosave-row select, .js-autosave-row textarea');
    if (!input || event.key !== 'Enter') return;

    event.preventDefault();
    autosaveDirtyForms();
});

document.addEventListener('blur', (event) => {
    const input = event.target.closest('.js-autosave-form input, .js-autosave-form textarea, .js-autosave-row input, .js-autosave-row textarea');
    if (input) queueAutosave(input);
}, true);

document.addEventListener('change', (event) => {
    const input = event.target.closest('.js-autosave-form select, .js-autosave-form input[type="checkbox"], .js-autosave-row select, .js-autosave-row input[type="checkbox"]');
    if (input) queueAutosave(input);
});
</script>
@endpush
@endsection
