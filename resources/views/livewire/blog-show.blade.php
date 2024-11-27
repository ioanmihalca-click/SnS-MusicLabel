<div class="max-w-4xl mx-auto">
    <!-- Main Article -->
    <article class="overflow-hidden bg-gradient-to-b from-gray-900/50 to-black rounded-xl">
        <!-- Hero Image -->
        @if($blog->cover_image)
            <div class="relative w-full overflow-hidden aspect-video">
                <img 
                    src="{{ asset('storage/' . $blog->cover_image) }}" 
                    alt="{{ $blog->title }}" 
                    class="object-cover w-full h-full"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
            </div>
        @endif

        <!-- Content Container -->
        <div class="px-6 py-8 md:px-12 md:py-10">
            <!-- Article Header -->
            <header class="mb-8">
                <h1 class="mb-4 text-3xl font-bold text-white md:text-4xl">
                    {{ $blog->title }}
                </h1>
                <div class="flex items-center space-x-4 text-sm text-gray-400">
                    <time datetime="{{ $blog->published_at->toDateString() }}">
                        {{ $blog->published_at->format('F j, Y') }}
                    </time>
                    <span class="text-gray-600">•</span>
                    <span>Snow 'n' Stuff</span>
                </div>
            </header>

            <!-- Article Content -->
            <div class="prose prose-invert max-w-none">
                {!! $blog->content !!}
            </div>
        </div>
    </article>

    <!-- Related Articles Section -->
    @if($relatedArticles->isNotEmpty())
        <section class="mt-16">
            <h2 class="mb-8 text-2xl font-bold text-white">
                Related <span class="text-red-800">Articles</span>
            </h2>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($relatedArticles as $article)
                    <article class="overflow-hidden transition-all duration-300 group bg-gradient-to-b from-gray-900/50 to-black rounded-xl hover:transform hover:-translate-y-1 hover:shadow-xl">
                        <!-- Article Image -->
                        <div class="relative aspect-[16/9] overflow-hidden">
                            @if($article->cover_image)
                                <img 
                                    src="{{ asset('storage/' . $article->cover_image) }}" 
                                    alt="{{ $article->title }}"
                                    class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110"
                                >
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-red-800/20 to-black"></div>
                            @endif
                            
                            <!-- Date Badge -->
                            <div class="absolute px-3 py-1 text-sm text-gray-300 rounded-full top-4 right-4 bg-black/70 backdrop-blur-sm">
                                {{ $article->published_at->format('F j, Y') }}
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="p-6 space-y-4">
                            <h3 class="text-xl font-bold text-white transition-colors duration-300 group-hover:text-red-800">
                                <a href="{{ route('blog.show', $article->slug) }}">
                                    {{ $article->title }}
                                </a>
                            </h3>
                            
                            <p class="text-gray-400 line-clamp-2">
                                {!! strip_tags($article->content) !!}
                            </p>

                            <a 
                                href="{{ route('blog.show', $article->slug) }}" 
                                class="inline-flex items-center space-x-2 text-red-800 transition-colors duration-300 hover:text-red-700"
                            >
                                <span>Read more</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Back to Blog Link -->
    <div class="mt-12">
        <a 
            href="{{ route('blog.index') }}" 
            class="inline-flex items-center space-x-2 text-gray-400 transition-colors duration-300 hover:text-red-800"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Back to all posts</span>
        </a>
    </div>

    <!-- Social Share Buttons -->
    <div class="fixed flex flex-col space-y-4 bottom-8 left-8">
        <button 
            x-data
            @click="window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text={{ urlencode($blog->title) }}', '_blank')"
            class="p-3 text-gray-400 transition-colors duration-300 rounded-full bg-black/70 backdrop-blur-sm hover:text-red-800"
            title="Share on Twitter"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
            </svg>
        </button>

        <button 
            x-data
            @click="window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank')"
            class="p-3 text-gray-400 transition-colors duration-300 rounded-full bg-black/70 backdrop-blur-sm hover:text-red-800"
            title="Share on Facebook"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.407.593 24 1.325 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.593 1.323-1.325V1.325C24 .593 23.407 0 22.675 0z"/>
            </svg>
        </button>
    </div>
</div>