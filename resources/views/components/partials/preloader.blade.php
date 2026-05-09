<div
    x-data="{ loading: true }"
    x-init="setTimeout(() => loading = false, 1000)"
    x-show="loading"
    x-transition.opacity.duration.700ms
    role="status"
    aria-live="polite"
    aria-label="Loading"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
>
    <div class="grid w-24 h-24 place-items-center">
        {{-- Outer ring --}}
        <div class="absolute w-24 h-24 border-4 border-gray-700 rounded-full border-t-red-900 animate-spin"></div>

        {{-- Middle ring --}}
        <div class="absolute w-16 h-16 border-4 border-gray-700 rounded-full border-t-red-700 animate-[spin_1.5s_linear_infinite]"></div>

        {{-- Inner ring --}}
        <div class="absolute w-8 h-8 border-4 border-gray-700 rounded-full border-t-red-500 animate-[spin_2s_linear_infinite]"></div>
    </div>
</div>
