<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold text-green-600 mb-2">📊 Accountant Dashboard</h1>
                <p class="text-gray-600">Welcome, <strong>{{ auth()->user()->full_name }}</strong></p>
                <p class="mt-2">Role: <span
                        class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-semibold">{{ auth()->user()->role?->role_name }}</span>
                </p>
                <hr class="my-4">
                <p class="text-gray-500 text-sm">You can manage transactions, receipts, and financial records here.</p>
            </div>
        </div>
    </div>
</x-app-layout>
