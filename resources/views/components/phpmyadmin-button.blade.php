@props([
    'label' => 'Database Admin',
    'icon' => true,
    'size' => 'md', // sm, md, lg
    'variant' => 'primary' // primary, secondary, outline
])

@php
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
        default => 'px-4 py-2 text-base'
    };

    $variantClasses = match($variant) {
        'primary' => 'bg-orange-500 text-white hover:bg-orange-600',
        'secondary' => 'bg-gray-600 text-white hover:bg-gray-700',
        'fns' => 'bg-[#c9991a] text-white hover:bg-[#b88a1a]', // FNS brand color
        'outline' => 'bg-transparent border-2 border-orange-500 text-orange-500 hover:bg-orange-50',
        'fns-outline' => 'bg-transparent border-2 border-[#c9991a] text-[#c9991a] hover:bg-[#c9991a] hover:text-white',
        default => 'bg-[#c9991a] text-white hover:bg-[#b88a1a]'
    };

    $baseClasses = 'rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2 shadow-sm hover:shadow-md';
@endphp

<button
    onclick="openPhpMyAdmin()"
    class="{{ $sizeClasses }} {{ $variantClasses }} {{ $baseClasses }}"
>
    @if($icon)
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
    </svg>
    @endif
    <span>{{ $label }}</span>
</button>