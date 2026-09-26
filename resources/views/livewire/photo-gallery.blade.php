{{--
    The photo gallery on /about: black-and-white tiles,
    colour on hover, Fancybox lightbox (bound to `#gallery [data-fancybox]`,
    so the parent section carries id="gallery").
--}}
<div>
    @if ($photos->isNotEmpty())
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($photos as $photo)
                <figure wire:key="photo-{{ $photo->id }}" class="group m-0 aspect-square overflow-hidden bg-slab">
                    <a
                        href="{{ $photo->imageUrl() }}"
                        data-fancybox="gallery"
                        data-src="{{ $photo->imageUrl() }}"
                        data-caption="{{ $photo->title }}"
                        aria-label="Open {{ $photo->title }} in lightbox"
                        class="block h-full w-full"
                    >
                        <img
                            src="{{ $photo->imageUrl() }}"
                            alt="{{ $photo->title }}"
                            width="600"
                            height="600"
                            loading="lazy"
                            decoding="async"
                            class="h-full w-full object-cover brightness-[.85] contrast-[1.1] grayscale transition duration-500 group-hover:brightness-100 group-hover:contrast-100 group-hover:grayscale-0"
                        >
                    </a>
                </figure>
            @endforeach
        </div>

        @if ($photos->hasPages())
            <nav data-markdown-ignore aria-label="Gallery pages" class="mt-6 flex items-center justify-between gap-4 font-meta text-[11px] uppercase tracking-[.08em] text-mist">
                <button
                    type="button"
                    wire:click="previousPage"
                    @disabled($photos->onFirstPage())
                    class="rounded-full border border-rule2 px-4 py-2.5 text-frost transition-colors hover:border-frost disabled:cursor-default disabled:opacity-40 disabled:hover:border-rule2"
                >Previous</button>

                <span>Page {{ $photos->currentPage() }} of {{ $photos->lastPage() }}</span>

                <button
                    type="button"
                    wire:click="nextPage"
                    @disabled(! $photos->hasMorePages())
                    class="rounded-full border border-rule2 px-4 py-2.5 text-frost transition-colors hover:border-frost disabled:cursor-default disabled:opacity-40 disabled:hover:border-rule2"
                >Next</button>
            </nav>
        @endif
    @else
        <p class="m-0 text-mist">Photos are on the way.</p>
    @endif
</div>
