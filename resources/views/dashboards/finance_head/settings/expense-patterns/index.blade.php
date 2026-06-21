@extends('layouts.admin')

@section('title', 'Expense Setup')
@section('page-title', 'Expense Setup')

@section('content')
<div class="space-y-6">
    @include('dashboards.finance_head.settings.expense-setup-tabs')

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">Pattern Builder</h2>
            <p class="mt-1 text-sm text-slate-500">Create a form pattern by adding fields and ticking the number fields that should be multiplied.</p>
        </div>
        <form method="POST" action="{{ route('head_of_finance.settings.expense-patterns.store') }}" class="grid gap-4 px-5 py-4 md:grid-cols-[150px_220px_1fr_auto] md:items-end">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Key</label>
                <input name="key" value="{{ old('key') }}" class="fns-input" placeholder="monthly" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                <input name="name" value="{{ old('name') }}" class="fns-input" placeholder="Monthly expense" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                <input name="description" value="{{ old('description') }}" class="fns-input" placeholder="What this form pattern is used for">
            </div>
            <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                Active
            </label>
            <div class="md:col-span-4">
                <button type="submit" class="fns-btn fns-btn-primary">Add pattern</button>
            </div>
        </form>
    </section>

    @forelse($patterns as $pattern)
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-semibold text-slate-900">{{ $pattern->name }}</h2>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $pattern->key }}</span>
                        <span class="rounded px-2 py-0.5 text-xs font-medium {{ $pattern->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $pattern->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                            {{ number_format($pattern->leaf_default_subsections_count) }} final subsections
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ $pattern->description ?: 'No description yet.' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse($pattern->leafDefaultSubsections as $subsection)
                            <span class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-700">
                                <span class="font-semibold text-slate-900">{{ $subsection->code }}</span>
                                {{ $subsection->name }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400">No final subsection uses this pattern yet.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('head_of_finance.settings.expense-patterns.update', $pattern) }}" class="js-autosave-form grid gap-4 border-b border-slate-200 px-5 py-4 md:grid-cols-[220px_1fr_auto] md:items-end">
                @csrf
                @method('PATCH')
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Pattern name</label>
                    <input name="name" value="{{ old('name', $pattern->name) }}" class="fns-input" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                    <input name="description" value="{{ old('description', $pattern->description) }}" class="fns-input">
                </div>
                <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked($pattern->is_active) class="rounded border-slate-300">
                    Active
                </label>
            </form>

            <div class="overflow-x-auto px-5 py-4">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                            <th class="py-2 pr-3">Key</th>
                            <th class="py-2 pr-3">Label</th>
                            <th class="py-2 pr-3">Type</th>
                            <th class="py-2 pr-3">Order</th>
                            <th class="py-2 pr-3">Default</th>
                            <th class="py-2 pr-3">Required</th>
                            <th class="py-2 pr-3">Calculated</th>
                            <th class="py-2 pr-3">Use in total</th>
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
                                                <option value="{{ $type }}" @selected($field->data_type === $type)>{{ $type }}</option>
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
                                                data-message="Delete this field?">
                                            Delete
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
                                        <option value="number">number</option>
                                        <option value="text">text</option>
                                        <option value="date">date</option>
                                        <option value="boolean">boolean</option>
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
                                    <button type="submit" class="fns-btn fns-btn-secondary fns-btn-sm">Add field</button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
            No expense patterns yet.
        </div>
    @endforelse
</div>

@push('scripts')
<script>
document.addEventListener('click', async (event) => {
    const button = event.target.closest('.js-delete-setting');
    if (!button) return;
    if (!confirm(button.dataset.message || 'Delete this row?')) return;

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
        showAutosaveStatus(forms.length === 1 ? 'Saved' : `Saved ${forms.length} changes`);
    } catch (error) {
        showAutosaveStatus('Could not autosave all changes', false);
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
