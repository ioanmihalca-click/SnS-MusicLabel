@php
    $posts = $blogs->getCollection();
    $lead = $blogs->onFirstPage() ? $posts->first() : null;
    $rest = $lead ? $posts->skip(1) : $posts;
@endphp

<div class="site-wrap pt-[clamp(32px,5vw,64px)]">
    <x-breadcrumbs :trail="$trail" class="mb-6" />

    <x-section-heading as="h1" title="News" subtitle="Releases, charts, playlists and label updates." />

    <div wire:loading.class="opacity-60" class="transition-opacity">
        @if ($posts->isNotEmpty())
            @if ($lead)
                @php
                    $leadUrl = route('blog.show', $lead->slug);
                    $leadCoverUrl = $lead->coverUrl();
                @endphp

                <article wire:key="lead-{{ $lead->id }}" class="group mb-[clamp(48px,6vw,80px)] grid items-center gap-[clamp(20px,4vw,56px)] md:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
                    <a href="{{ $leadUrl }}" tabindex="-1" aria-hidden="true" data-markdown-ignore class="block aspect-[1200/630] overflow-hidden bg-slab">
                        @if ($leadCoverUrl)
                            <img
                                src="{{ $leadCoverUrl }}"
                                alt=""
                                width="1200"
                                height="630"
                                fetchpriority="high"
                                decoding="async"
                                class="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(.2,.7,.1,1)] group-hover:scale-[1.03]"
                            >
                        @else
                            <span class="grid h-full w-full place-items-center bg-gradient-to-br from-slab2 to-slab">
                                <x-icons.logomark class="h-20 w-20 text-signal" />
                            </span>
                        @endif
                    </a>

                    <div class="flex min-w-0 flex-col gap-4">
                        <p class="m-0 flex gap-2.5 font-meta text-[10.5px] uppercase tracking-[.08em] text-dim">
                            <span class="text-frost">Latest</span>
                            <time datetime="{{ $lead->published_at->toDateString() }}">{{ $lead->published_at->format('d.m.Y') }}</time>
                        </p>

                        <h2 class="m-0 font-display text-[clamp(2.2rem,4.4vw,3.8rem)] font-extrabold uppercase leading-[.9] [overflow-wrap:anywhere]">
                            <a href="{{ $leadUrl }}" class="decoration-2 underline-offset-4 hover:underline">{{ $lead->title }}</a>
                        </h2>

                        <p class="m-0 line-clamp-4 text-mist">{{ $lead->summary() }}</p>

                        <div>
                            <a href="{{ $leadUrl }}" class="inline-flex items-center gap-2 border-b border-rule2 pb-[3px] font-semibold transition-colors hover:border-frost">Read more<span class="sr-only">: {{ $lead->title }}</span> <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                </article>
            @endif

            @if ($rest->isNotEmpty())
                <div class="grid gap-x-[22px] gap-y-10 border-t border-rule pt-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($rest as $post)
                        <x-news-card :item="\App\Support\NewsItem::fromPost($post)" heading-level="h2" with-summary wire:key="post-{{ $post->id }}" />
                    @endforeach
                </div>
            @endif
        @else
            <div class="border-t border-rule pt-5">
                <p class="m-0 text-mist">No posts found{{ $search !== '' ? ' for "'.$search.'"' : '' }}.</p>

                @if ($search !== '')
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="mt-4 rounded-full border border-rule2 px-4 py-2.5 text-sm font-semibold text-frost transition-colors hover:border-frost"
                    >Clear search</button>
                @endif
            </div>
        @endif
    </div>

    @if ($blogs->hasPages())
        <div data-markdown-ignore>
            {{ $blogs->links() }}
        </div>
    @endif
</div>
