@php
    $focusRing = 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black';
@endphp

<div class="max-w-4xl mx-auto">
    <!-- Main Article -->
    <article class="overflow-hidden bg-gradient-to-b from-gray-900/50 to-black rounded-xl">
        <!-- Hero Image -->
        @if ($blog->cover_image)
            <div class="relative w-full overflow-hidden aspect-video">
                <img src="{{ asset('storage/' . $blog->cover_image) }}"
                    alt="{{ $blog->title }}"
                    width="1200"
                    height="630"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="object-cover w-full h-full">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
            </div>
        @endif

        <!-- Content Container -->
        <div class="px-6 py-8 md:px-12 md:py-10">
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
    @if ($relatedArticles->isNotEmpty())
        <section class="mt-16">
            <h2 class="mb-8 text-2xl font-bold text-white">
                Related <span class="text-red-800">Articles</span>
            </h2>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedArticles as $article)
                    <article
                        class="overflow-hidden transition-all duration-300 group bg-gradient-to-b from-gray-900/50 to-black rounded-xl hover:transform hover:-translate-y-1 hover:shadow-xl">
                        <!-- Article Image -->
                        <div class="relative aspect-[16/9] overflow-hidden">
                            @if ($article->cover_image)
                                <img src="{{ asset('storage/' . $article->cover_image) }}"
                                    alt="{{ $article->title }}"
                                    width="1200"
                                    height="630"
                                    loading="lazy"
                                    decoding="async"
                                    class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110">
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
                            <h3 class="text-xl font-bold text-white transition-colors duration-300 line-clamp-2 group-hover:text-red-800">
                                <a href="{{ route('blog.show', $article->slug) }}" class="rounded {{ $focusRing }}">
                                    {{ $article->title }}
                                </a>
                            </h3>

                            <p class="text-gray-400 line-clamp-2">
                                {!! strip_tags($article->content) !!}
                            </p>

                            <a href="{{ route('blog.show', $article->slug) }}"
                                class="inline-flex items-center space-x-2 text-red-800 transition-colors duration-300 rounded hover:text-red-700 {{ $focusRing }}">
                                <span>Read more</span>
                                <x-icons.arrow-right />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Back to Blog Link -->
    <div class="mt-12">
        <a href="{{ route('blog.index') }}"
            class="inline-flex items-center space-x-2 text-gray-400 transition-colors duration-300 rounded hover:text-red-800 {{ $focusRing }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to all posts</span>
        </a>
    </div>

    <!-- Social Share Buttons -->
    <div class="fixed flex flex-col space-y-4 bottom-8 left-8">
        <button x-data type="button"
            @click="window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text={{ urlencode($blog->title) }}', '_blank')"
            class="p-3 text-gray-400 transition-colors duration-300 rounded-full bg-black/70 backdrop-blur-sm hover:text-red-800 {{ $focusRing }}"
            aria-label="Share on Twitter"
            title="Share on Twitter">
            <x-icons.social.twitter />
        </button>

        <button x-data type="button"
            @click="window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank')"
            class="p-3 text-gray-400 transition-colors duration-300 rounded-full bg-black/70 backdrop-blur-sm hover:text-red-800 {{ $focusRing }}"
            aria-label="Share on Facebook"
            title="Share on Facebook">
            <x-icons.social.facebook />
        </button>

        <button x-data type="button"
            @click="window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(window.location.href) + '&title={{ urlencode($blog->title) }}', '_blank')"
            class="p-3 text-gray-400 transition-colors duration-300 rounded-full bg-black/70 backdrop-blur-sm hover:text-red-800 {{ $focusRing }}"
            aria-label="Share on LinkedIn"
            title="Share on LinkedIn">
            <x-icons.social.linkedin />
        </button>
    </div>
</div>
