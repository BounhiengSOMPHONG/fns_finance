@extends('layouts.admin')

@section('title', 'Expense planning')
@section('page-title', 'Expense planning')

@section('content')
<div class="fns-toolbar">
    <div class="fns-toolbar-left">
        <button type="button" id="open-planning-year-modal" class="fns-btn fns-btn-primary">
            Create planning year
        </button>
    </div>
</div>

<div class="fns-table-wrap">
    <table class="fns-table">
        <thead>
            <tr>
                <th>Year</th>
                <th>Name</th>
                <th class="col-num">Rows</th>
                <th class="col-num">Total</th>
                <th style="width:180px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td><strong>{{ $plan->year }}</strong></td>
                <td>{{ $plan->name }}</td>
                <td class="col-num">{{ number_format($plan->expensePlans()->count()) }}</td>
                <td class="col-num">{{ number_format($plan->totalAmount(), 0) }}</td>
                <td>
                    <div style="display:flex;gap:.5rem;">
                        <a href="{{ route('head_of_finance.expense.manage', $plan) }}" class="fns-btn fns-btn-primary fns-btn-sm">Manage</a>
                        <form method="POST" action="{{ route('head_of_finance.expense.destroy', $plan) }}" onsubmit="return confirm('Delete this planning year?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="fns-btn fns-btn-danger fns-btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;color:var(--fns-gray-400);padding:2rem;">No planning years yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem;">{{ $plans->links() }}</div>

<div id="planning-year-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
    <div id="planning-year-backdrop" class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-lg rounded-lg bg-white shadow-2xl ring-1 ring-slate-900/10">
        <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Create planning year</h2>
                <p class="mt-1 text-sm text-slate-500">The new year will copy the previous year structure.</p>
            </div>
            <button type="button" id="close-planning-year-modal" class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Close modal">
                <span class="block text-xl leading-none">&times;</span>
            </button>
        </div>

        <form method="POST" action="{{ route('head_of_finance.expense.store') }}" class="px-6 py-5">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Planning year <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="year" min="2000" max="2100" required
                           value="{{ old('year', date('Y')) }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 @error('year') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    @error('year')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Planning {{ date('Y') }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" rows="3"
                              class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-4">
                <button type="button" id="cancel-planning-year-modal" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Create
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('planning-year-modal');
    const openButton = document.getElementById('open-planning-year-modal');
    const closeButtons = [
        document.getElementById('close-planning-year-modal'),
        document.getElementById('cancel-planning-year-modal'),
        document.getElementById('planning-year-backdrop'),
    ];

    const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        modal.querySelector('input[name="year"]').focus();
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    openButton.addEventListener('click', openModal);
    closeButtons.forEach((button) => button.addEventListener('click', closeModal));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    @if($errors->has('year') || $errors->has('name') || $errors->has('description'))
        openModal();
    @endif
});
</script>
@endpush
@endsection
