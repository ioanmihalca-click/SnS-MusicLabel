<div class="max-w-4xl mx-auto">
    <!-- Main Article -->
    <article class="overflow-hidden rounded-2xl"
             style="
                background: linear-gradient(145deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
                border: 1px solid rgba(75, 75, 75, 0.15);
                box-shadow: 
                    0 8px 30px -10px rgba(0, 0, 0, 0.5),
                    0 4px 15px -5px rgba(0, 0, 0, 0.3),
                    inset 0 1px 0 0 rgba(255, 255, 255, 0.02);
             ">
        <!-- Hero Image -->
        @if ($blog->cover_image)
            <div class="relative w-full overflow-hidden aspect-video">
                <img src="{{ asset('storage/' . $blog->cover_image) }}" alt="{{ $blog->title }}"
                    class="object-cover w-full h-full">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-transparent to-transparent"></div>
            </div>
        @endif

        <!-- Content Container -->
        <div class="px-6 py-10 md:px-12 md:py-12">
            <!-- Article Header -->
            <header class="mb-10">
                <h1 class="mb-5 text-3xl md:text-4xl font-bold text-white font-display leading-tight">
                    {{ $blog->title }}
                </h1>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <!-- Author avatar placeholder -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                             style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.2) 0%, rgba(127, 29, 29, 0.15) 100%); border: 1px solid rgba(220, 38, 38, 0.2);">
                            <span class="text-red-500 font-bold text-sm">SnS</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">Snow 'n' Stuff</p>
                            <time datetime="{{ $blog->published_at->toDateString() }}" class="text-xs text-gray-500">
                                {{ $blog->published_at->format('F j, Y') }}
                            </time>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Article Content -->
            <div class="prose prose-invert max-w-none prose-headings:font-display prose-headings:font-bold prose-a:text-red-500 prose-a:no-underline hover:prose-a:underline prose-blockquote:border-l-red-500/50 prose-blockquote:bg-white/[0.02] prose-blockquote:py-1 prose-blockquote:px-4 prose-blockquote:rounded-r-lg">
                {!! $blog->content !!}
            </div>
        </div>
    </article>

    <!-- Related Articles Section -->
    @if ($relatedArticles->isNotEmpty())
        <section class="mt-20">
            <div class="flex items-center gap-4 mb-10">
                <div class="h-[1px] flex-grow" style="background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);"></div>
                <h2 class="text-2xl font-bold font-display">
                    <span class="text-white">Related </span>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-red-800">Articles</span>
                </h2>
                <div class="h-[1px] flex-grow" style="background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);"></div>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedArticles as $article)
                    <article class="group relative">
                        <div class="overflow-hidden rounded-2xl transition-all duration-500 group-hover:-translate-y-2"
                             style="
                                background: linear-gradient(145deg, rgba(20, 20, 20, 0.95), rgba(10, 10, 10, 0.98));
                                border: 1px solid rgba(75, 75, 75, 0.15);
                                box-shadow: 
                                    0 4px 20px -5px rgba(0, 0, 0, 0.4),
                                    0 2px 8px -2px rgba(0, 0, 0, 0.2);
                             ">
                            <!-- Article Image -->
                            <div class="relative aspect-[16/9] overflow-hidden">
                                @if ($article->cover_image)
                                    <img src="{{ asset('storage/' . $article->cover_image) }}" alt="{{ $article->title }}"
                                        class="object-cover w-full h-full transition-transform duration-700 group-hover:scale-110">
                                @else
                                    <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(0, 0, 0, 0.8) 100%);"></div>
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-transparent to-transparent"></div>

                                <!-- Date Badge -->
                                <div class="absolute px-3 py-1.5 text-xs text-gray-300 rounded-full top-4 right-4 backdrop-blur-md"
                                     style="background: linear-gradient(145deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)); border: 1px solid rgba(255, 255, 255, 0.08);">
                                    {{ $article->published_at->format('M j, Y') }}
                                </div>
                            </div>

                            <!-- Article Content -->
                            <div class="p-6 space-y-3">
                                <h3 class="text-lg font-bold text-white transition-colors duration-300 line-clamp-2 group-hover:text-red-500">
                                    <a href="{{ route('blog.show', $article->slug) }}">
                                        {{ $article->title }}
                                    </a>
                                </h3>

                                <p class="text-gray-500 text-sm line-clamp-2">
                                    {!! strip_tags($article->content) !!}
                                </p>

                                <a href="{{ route('blog.show', $article->slug) }}"
                                    class="inline-flex items-center gap-2 text-sm font-bold text-red-500 transition-all duration-300 hover:text-white group/link pt-2">
                                    <span>Read more</span>
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover/link:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Hover Glow -->
                        <div class="absolute -inset-2 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-700 pointer-events-none -z-10"
                             style="background: radial-gradient(ellipse at center, rgba(220, 38, 38, 0.1) 0%, transparent 70%);"></div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Back to Blog Link -->
    <div class="mt-16">
        <a href="{{ route('blog.index') }}"
            class="inline-flex items-center gap-3 px-5 py-3 text-gray-400 transition-all duration-300 hover:text-white rounded-xl group"
            style="background: linear-gradient(145deg, rgba(30, 30, 30, 0.8), rgba(20, 20, 20, 0.9)); border: 1px solid rgba(75, 75, 75, 0.12);">
            <svg class="w-5 h-5 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to all posts</span>
        </a>
    </div>

    <!-- Social Share Buttons -->
    <div class="fixed flex flex-col gap-3 bottom-8 left-8">
        @foreach([
            ['action' => 'twitter', 'icon' => 'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z'],
            ['action' => 'facebook', 'icon' => 'M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.407.593 24 1.325 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.593 1.323-1.325V1.325C24 .593 23.407 0 22.675 0z'],
            ['action' => 'linkedin', 'icon' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'],
        ] as $share)
            <button x-data
                @if($share['action'] === 'twitter')
                @click="window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text={{ urlencode($blog->title) }}', '_blank')"
                @elseif($share['action'] === 'facebook')
                @click="window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank')"
                @else
                @click="window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(window.location.href) + '&title={{ urlencode($blog->title) }}', '_blank')"
                @endif
                class="p-3 rounded-xl transition-all duration-300 hover:scale-110 group"
                style="background: linear-gradient(145deg, rgba(20, 20, 20, 0.9), rgba(10, 10, 10, 0.95)); border: 1px solid rgba(75, 75, 75, 0.15); backdrop-filter: blur(10px);"
                title="Share on {{ ucfirst($share['action']) }}">
                <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition-colors duration-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="{{ $share['icon'] }}" />
                </svg>
            </button>
        @endforeach
    </div>
</div>
