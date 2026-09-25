@php
    $coverUrl = $blog->coverUrl();
    $shareLinks = [
        'X' => 'https://x.com/intent/post?'.http_build_query(['url' => $shareUrl, 'text' => $blog->title]),
        'Facebook' => 'https://www.facebook.com/sharer/sharer.php?'.http_build_query(['u' => $shareUrl]),
        'LinkedIn' => 'https://www.linkedin.com/sharing/share-offsite/?'.http_build_query(['url' => $shareUrl]),
    ];
    $monoClass = 'm-0 font-meta text-[10.5px] uppercase tracking-[.08em]';
@endphp

<div>
    <article class="site-wrap pt-[clamp(32px,5vw,64px)]">
        <header class="flex max-w-[1000px] flex-col gap-5">
            <x-breadcrumbs :trail="$trail" />

            <p class="{{ $monoClass }} flex gap-2.5 text-dim">
                <span class="text-frost">News</span>
                <time datetime="{{ $blog->published_at->toDateString() }}">{{ $blog->published_at->format('d.m.Y') }}</time>
            </p>

            <h1 class="m-0 font-display text-[clamp(2.8rem,6.4vw,5.6rem)] font-black uppercase leading-[.86] [overflow-wrap:anywhere]">{{ $blog->title }}</h1>
        </header>

        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt=""
                data-markdown-ignore
                width="1200"
                height="630"
                fetchpriority="high"
                decoding="async"
                class="mt-[clamp(28px,4vw,48px)] aspect-[1200/630] w-full max-w-[1000px] bg-slab object-cover"
            >
        @endif

        <div class="article-body mt-[clamp(28px,4vw,48px)] max-w-[720px]">
            {!! $body !!}
        </div>

        <div data-markdown-ignore class="mt-12 flex max-w-[720px] flex-wrap items-center gap-2 border-t border-rule pt-5">
            <span class="{{ $monoClass }} mr-2 text-dim">Share</span>
            @foreach ($shareLinks as $network => $href)
                <a
                    href="{{ $href }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center rounded-full border border-rule2 px-[13px] py-[9px] text-[13px] font-medium leading-none transition-colors hover:border-frost"
                >{{ $network }}<span class="sr-only"> (share this post)</span></a>
            @endforeach
        </div>
    </article>

    @if ($relatedArticles->isNotEmpty())
        <section id="more-news" class="site-wrap pt-[clamp(64px,9vw,120px)]">
            <x-section-heading title="More news" />

            <div class="grid gap-x-[22px] gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedArticles as $article)
                    <x-news-card :item="\App\Support\NewsItem::fromPost($article)" wire:key="related-{{ $article->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    <div class="site-wrap pt-12">
        <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 border-b border-rule2 pb-[3px] font-semibold transition-colors hover:border-frost"><span aria-hidden="true">←</span> All news</a>
    </div>
</div>
