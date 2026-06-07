@extends('layouts.admin')

@section('title', 'Expense structure')
@section('page-title', 'Expense structure')

@section('content')
<div class="space-y-6">
    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Sections and subsections</h2>
            <p class="mt-1 text-sm text-slate-500">Manage what appears in the expense planning workbook.</p>
        </div>

        <form method="GET" action="{{ route('head_of_finance.settings.expense-structure.index') }}">
            <select name="planning_year_id" class="fns-input" onchange="this.form.submit()">
                @foreach($years as $year)
                    <option value="{{ $year->id }}" @selected($planningYear?->id === $year->id)>
                        {{ $year->year }} - {{ $year->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($planningYear)
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-900">Add section</h3>
            </div>
            <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.sections.store') }}" class="grid gap-3 px-5 py-4 md:grid-cols-[110px_1fr_120px_auto] md:items-end">
                @csrf
                <input type="hidden" name="planning_year_id" value="{{ $planningYear->id }}">

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Code</label>
                    <input name="code" class="fns-input" placeholder="2.7" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                    <input name="name" class="fns-input" placeholder="Section name" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Order</label>
                    <input type="number" name="display_order" class="fns-input" min="0" max="999" value="{{ ($sections->max('display_order') ?? 0) + 1 }}" required>
                </div>
                <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                    Active
                </label>
                <div class="md:col-span-4">
                    <textarea name="description" class="fns-input" rows="2" placeholder="Description"></textarea>
                    <button type="submit" class="fns-btn fns-btn-primary mt-3">Add section</button>
                </div>
            </form>
        </section>

        @forelse($sections as $section)
            @php
                $parentOptions = $section->subsections->whereNull('parent_id');
            @endphp
            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.sections.update', $section) }}" class="js-autosave-form grid gap-3 md:grid-cols-[110px_1fr_120px_auto] md:items-end">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Code</label>
                            <input name="code" value="{{ $section->code }}" class="fns-input" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Section name</label>
                            <input name="name" value="{{ $section->name }}" class="fns-input" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Order</label>
                            <input type="number" name="display_order" value="{{ $section->display_order }}" class="fns-input" min="0" max="999" required>
                        </div>
                        <label class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_active" value="1" @checked($section->is_active) class="rounded border-slate-300">
                            Active
                        </label>
                        <div class="md:col-span-4">
                            <textarea name="description" class="fns-input" rows="2" placeholder="Description">{{ $section->description }}</textarea>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button"
                                        class="fns-btn fns-btn-danger js-delete-setting"
                                        data-url="{{ route('head_of_finance.settings.expense-structure.sections.destroy', $section) }}"
                                        data-message="Delete this section?">
                                    Delete section
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto px-5 py-4">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-3">Code</th>
                                <th class="py-2 pr-3">Subsection name</th>
                                <th class="py-2 pr-3">Parent</th>
                                <th class="py-2 pr-3">Pattern</th>
                                <th class="py-2 pr-3">Order</th>
                                <th class="py-2 pr-3">Active</th>
                                <th class="py-2 pr-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($section->subsections as $subsection)
                                <tr class="js-autosave-row" data-url="{{ route('head_of_finance.settings.expense-structure.subsections.update', $subsection) }}">
                                    <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.subsections.update', $subsection) }}" class="js-autosave-source-form">
                                        @csrf
                                        @method('PATCH')
                                        <td class="py-2 pr-3">
                                            <input name="code" value="{{ $subsection->code }}" class="fns-input min-w-28" required>
                                        </td>
                                        <td class="py-2 pr-3">
                                            <input name="name" value="{{ $subsection->name }}" class="fns-input min-w-80" required>
                                            <input type="hidden" name="description" value="{{ $subsection->description }}">
                                        </td>
                                        <td class="py-2 pr-3">
                                            <select name="parent_id" class="fns-input min-w-44">
                                                <option value="">No parent</option>
                                                @foreach($parentOptions as $parent)
                                                    @if($parent->id !== $subsection->id)
                                                        <option value="{{ $parent->id }}" @selected($subsection->parent_id === $parent->id)>
                                                            {{ $parent->code }} - {{ $parent->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2 pr-3">
                                            <select name="default_pattern_id" class="fns-input min-w-44">
                                                <option value="">No pattern</option>
                                                @foreach($patterns as $pattern)
                                                    <option value="{{ $pattern->id }}" @selected($subsection->default_pattern_id === $pattern->id)>
                                                        {{ $pattern->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2 pr-3">
                                            <input type="number" name="display_order" value="{{ $subsection->display_order }}" min="0" max="999" class="fns-input w-24" required>
                                        </td>
                                        <td class="py-2 pr-3 text-center">
                                            <input type="checkbox" name="is_active" value="1" @checked($subsection->is_active) class="rounded border-slate-300">
                                        </td>
                                        <td class="py-2 pr-3 whitespace-nowrap">
                                            <button type="button"
                                                    class="fns-btn fns-btn-danger fns-btn-sm js-delete-setting"
                                                    data-url="{{ route('head_of_finance.settings.expense-structure.subsections.destroy', $subsection) }}"
                                                    data-message="Delete this subsection?">
                                                Delete
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach

                            <tr class="bg-slate-50">
                                <form method="POST" action="{{ route('head_of_finance.settings.expense-structure.subsections.store', $section) }}">
                                    @csrf
                                    <td class="py-3 pr-3">
                                        <input name="code" class="fns-input min-w-28" placeholder="{{ $section->code }}.1" required>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input name="name" class="fns-input min-w-80" placeholder="Subsection name" required>
                                        <input type="hidden" name="description" value="">
                                    </td>
                                    <td class="py-3 pr-3">
                                        <select name="parent_id" class="fns-input min-w-44">
                                            <option value="">No parent</option>
                                            @foreach($parentOptions as $parent)
                                                <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <select name="default_pattern_id" class="fns-input min-w-44">
                                            <option value="">No pattern</option>
                                            @foreach($patterns as $pattern)
                                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <input type="number" name="display_order" value="{{ ($section->subsections->max('display_order') ?? 0) + 1 }}" min="0" max="999" class="fns-input w-24" required>
                                    </td>
                                    <td class="py-3 pr-3 text-center">
                                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                                    </td>
                                    <td class="py-3 pr-3">
                                        <button type="submit" class="fns-btn fns-btn-secondary fns-btn-sm">Add</button>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
                No sections yet for this planning year.
            </div>
        @endforelse
    @else
        <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
            Create a planning year first.
        </div>
    @endif
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
