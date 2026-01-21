
<div class="mx-auto max-w-7xl">
    <!-- Header & Search -->
    <div class="mb-16 space-y-6">
        <div class="text-center md:text-left">
            <span class="badge-primary mb-4">
                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/>
                    <path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/>
                </svg>
                Latest Updates
            </span>
            <h1 class="text-4xl md:text-5xl font-bold font-display">
                <span class="text-white">Our Blog </span>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-red-500 to-red-800">Posts</span>
            </h1>
        </div>
    </div>

    <!-- Blog Grid -->
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($blogs as $blog)
            <article class="group relative">
                <!-- Card Container -->
                <div class="relative rounded-2xl overflow-hidden transition-all duration-500 group-hover:-translate-y-2"
                     style="
                        background: linear-gradient(145deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
                        border: 1px solid rgba(75, 75, 75, 0.15);
                        box-shadow: 
                            0 4px 20px -5px rgba(0, 0, 0, 0.4),
                            0 2px 8px -2px rgba(0, 0, 0, 0.2),
                            inset 0 1px 0 0 rgba(255, 255, 255, 0.02);
                     ">
                    
                    <!-- Image Container -->
                    <div class="relative aspect-[16/9] overflow-hidden">
                        @if($blog->cover_image)
                            <img 
                                src="{{ asset('storage/' . $blog->cover_image) }}" 
                                alt="{{ $blog->title }}" 
                                class="object-cover w-full h-full transition-transform duration-700 group-hover:scale-110"
                            >
                        @else
                            <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(0, 0, 0, 0.8) 100%);"></div>
                        @endif
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-transparent to-transparent"></div>
                        
                        <!-- Date Badge -->
                        <div class="absolute top-4 right-4 px-3 py-1.5 text-xs font-medium text-gray-300 rounded-full backdrop-blur-md"
                             style="background: linear-gradient(145deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)); border: 1px solid rgba(255, 255, 255, 0.08);">
                            {{ $blog->published_at->format('M j, Y') }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-4">
                        <h2 class="text-xl font-bold text-white transition-colors duration-300 line-clamp-1 group-hover:text-red-500">
                            <a href="{{ route('blog.show', $blog->slug) }}">
                                {{ $blog->title }}
                            </a>
                        </h2>

                        <p class="text-gray-500 text-sm leading-relaxed line-clamp-3">
                            {!! strip_tags($blog->content) !!}
                        </p>

                        <!-- Read More Link -->
                        <div class="pt-4" style="border-top: 1px solid rgba(255, 255, 255, 0.05);">
                            <a 
                                href="{{ route('blog.show', $blog->slug) }}" 
                                class="inline-flex items-center gap-2 text-sm font-bold text-red-500 transition-all duration-300 hover:text-white group/link"
                            >
                                <span>Read more</span>
                                <svg class="w-4 h-4 transition-transform duration-300 group-hover/link:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hover Glow -->
                <div class="absolute -inset-2 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-700 pointer-events-none -z-10"
                     style="background: radial-gradient(ellipse at center, rgba(220, 38, 38, 0.1) 0%, transparent 70%);"></div>
            </article>
        @empty
            <div class="py-16 text-center col-span-full">
                <div class="max-w-sm mx-auto space-y-6 p-8 rounded-2xl"
                     style="background: linear-gradient(145deg, rgba(20, 20, 20, 0.8), rgba(10, 10, 10, 0.9)); border: 1px solid rgba(75, 75, 75, 0.12);">
                    <div class="text-gray-400">
                        <svg class="w-16 h-16 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <h3 class="mt-4 text-xl font-bold text-gray-300">No posts found</h3>
                        <p class="mt-2 text-gray-500">{{ $search ? 'Try different search terms or' : 'Check back later for' }} new blog posts.</p>
                    </div>
                    @if($search)
                        <button 
                            wire:click="$set('search', '')" 
                            class="inline-flex items-center px-5 py-2.5 text-gray-300 transition-all duration-300 rounded-xl hover:text-white"
                            style="background: linear-gradient(145deg, rgba(40, 40, 40, 0.8), rgba(30, 30, 30, 0.9)); border: 1px solid rgba(75, 75, 75, 0.15);"
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
        <div class="mt-16">
            {{ $blogs->links() }}
        </div>
    @endif

    <!-- Loading State -->
    <div 
        wire:loading.delay 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
    >
        <div class="flex items-center gap-4 px-6 py-4 rounded-2xl"
             style="background: linear-gradient(145deg, rgba(25, 25, 25, 0.95), rgba(15, 15, 15, 0.98)); border: 1px solid rgba(75, 75, 75, 0.15);">
            <svg class="w-6 h-6 text-red-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white font-medium">Loading...</span>
        </div>
    </div>
</div>