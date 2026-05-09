<button
    x-cloak
    x-data="{ show: false }"
    x-init="window.addEventListener('scroll', () => { show = window.pageYOffset > 500 })"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="window.scrollTo({top: 0, behavior: 'smooth'})"
    type="button"
    aria-label="Scroll to top"
    class="group fixed z-50 bottom-6 right-6 p-2.5 rounded-lg bg-gray-800/80 backdrop-blur-sm transform transition-all duration-200 ease-out hover:bg-red-900/90 hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black"
>
    <x-icons.arrow-up class="w-4 h-4 transition-colors duration-200 text-white/80 group-hover:text-white" />
</button>
