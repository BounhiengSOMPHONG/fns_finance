@once
@push('scripts')
<script>
    function openPhpMyAdmin() {
        document.getElementById('phpmyadmin-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePhpMyAdmin() {
        document.getElementById('phpmyadmin-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePhpMyAdmin();
        }
    });

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('phpmyadmin-modal');
        if (event.target === modal) {
            closePhpMyAdmin();
        }
    });
</script>
@endpush
@endonce

<!-- Modal Backdrop -->
<div id="phpmyadmin-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <!-- Modal Content -->
    <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-800">phpMyAdmin - Database Manager</h2>
            </div>
            <button onclick="closePhpMyAdmin()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body (iframe) -->
        <div class="flex-1 overflow-hidden">
            <iframe
                src="http://localhost:8085/"
                class="w-full h-full border-0"
                title="phpMyAdmin"
                allowfullscreen
            ></iframe>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t bg-gray-50 flex items-center justify-between rounded-b-lg">
            <div class="text-sm text-gray-600">
                <span class="font-medium">Database:</span> fns_1 |
                <span class="font-medium">User:</span> root |
                <a href="http://localhost:8085" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Open in new tab</a>
            </div>
            <button onclick="closePhpMyAdmin()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>