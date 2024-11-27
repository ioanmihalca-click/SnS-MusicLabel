
<div class="mx-auto max-w-7xl">
    <!-- Header & Search -->
    <div class="mb-12 space-y-6">
        <h1 class="text-4xl font-bold text-white">Our Blog <span class="text-red-800">Posts</span></h1>
        
        <!-- Search Bar -->
        {{-- <div class="relative max-w-xl">
            <input 
                type="text"
                wire:model.debounce.300ms="search"
                placeholder="Search posts..."
                class="w-full px-4 py-3 text-white placeholder-gray-400 border border-gray-800 rounded-lg bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-red-800 focus:border-transparent"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div> --}}

    <!-- Blog Grid -->
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($blogs as $blog)
            <article 
                class="relative overflow-hidden transition-all duration-300 group bg-gradient-to-b from-gray-900/50 to-black rounded-xl hover:transform hover:-translate-y-1 hover:shadow-2xl"
            >
                <!-- Image Container -->
                <div class="relative aspect-[16/9] overflow-hidden">
                    @if($blog->cover_image)
                        <img 
                            src="{{ asset('storage/' . $blog->cover_image) }}" 
                            alt="{{ $blog->title }}" 
                            class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110"
                        >
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-red-800/20 to-black"></div>
                    @endif
                    
                    <!-- Date Badge -->
                    <div class="absolute px-3 py-1 text-sm text-gray-300 rounded-full top-4 right-4 bg-black/70 backdrop-blur-sm">
                        {{ $blog->published_at->format('F j, Y') }}
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-4">
                    <h2 class="text-xl font-bold text-white transition-colors duration-300 line-clamp-1 group-hover:text-red-800">
                        <a href="{{ route('blog.show', $blog->slug) }}" class="hover:text-red-700">
                            {{ $blog->title }}
                        </a>
                    </h2>

                    <p class="text-gray-400 line-clamp-3">
                        {!! strip_tags($blog->content) !!}
                    </p>

                    <!-- Read More Link -->
                    <div class="pt-4">
                        <a 
                            href="{{ route('blog.show', $blog->slug) }}" 
                            class="inline-flex items-center space-x-2 text-red-800 transition-colors duration-300 hover:text-red-700"
                        >
                            <span>Read more</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="py-12 text-center col-span-full">
                <div class="max-w-sm mx-auto space-y-4">
                    <div class="text-gray-400">
                        <svg class="w-16 h-16 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <h3 class="mt-4 text-xl font-semibold text-gray-300">No posts found</h3>
                        <p class="mt-2 text-gray-400">{{ $search ? 'Try different search terms or' : 'Check back later for' }} new blog posts.</p>
                    </div>
                    @if($search)
                        <button 
                            wire:click="$set('search', '')" 
                            class="inline-flex items-center px-4 py-2 text-gray-300 transition-colors duration-300 border border-gray-700 rounded-md hover:bg-gray-800"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Clear search
                        </button>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($blogs->hasPages())
        <div class="mt-12">
            {{ $blogs->links() }}
        </div>
    @endif

    <!-- Loading State -->
    <div 
        wire:loading.delay 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
    >
        <div class="flex items-center p-4 space-x-4 bg-gray-900 rounded-lg">
            <svg class="w-6 h-6 text-red-800 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white">Loading...</span>
        </div>
    </div>
</div>