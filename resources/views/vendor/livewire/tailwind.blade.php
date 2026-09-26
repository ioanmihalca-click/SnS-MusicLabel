{{--
    Livewire's default pagination, in the site's look. Every page is a real
    link (crawlable, opens in a new tab), which Livewire intercepts to swap
    the page in place. The paginator's URLs are relative ("blog?page=2"),
    hence url().
--}}
@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';

$pageName = $paginator->getPageName();
$pillClass = 'inline-flex min-w-[42px] items-center justify-center rounded-full border px-4 py-2.5 text-sm font-semibold leading-none transition-colors';
$linkClass = $pillClass.' border-rule2 text-frost hover:border-frost';
$disabledClass = $pillClass.' cursor-default border-rule text-dim';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination" class="mt-12 flex items-center justify-between gap-4 border-t border-rule pt-6">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="{{ $disabledClass }}">← Previous</span>
            @else
                <a
                    href="{{ url($paginator->previousPageUrl()) }}"
                    rel="prev"
                    wire:click.prevent="previousPage('{{ $pageName }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    class="{{ $linkClass }}"
                >← Previous</a>
            @endif

            <p class="m-0 font-meta text-[11px] uppercase tracking-[.08em] text-mist sm:hidden">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>

            <ul class="m-0 hidden list-none flex-wrap items-center justify-center gap-2 p-0 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li aria-disabled="true" class="px-1 text-dim">{{ $element }}</li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li wire:key="paginator-{{ $pageName }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="{{ $pillClass }} border-frost bg-frost text-ink">{{ $page }}</span>
                                @else
                                    <a
                                        href="{{ url($url) }}"
                                        wire:click.prevent="gotoPage({{ $page }}, '{{ $pageName }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                        class="{{ $linkClass }}"
                                    >{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach
            </ul>

            @if ($paginator->hasMorePages())
                <a
                    href="{{ url($paginator->nextPageUrl()) }}"
                    rel="next"
                    wire:click.prevent="nextPage('{{ $pageName }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    class="{{ $linkClass }}"
                >Next →</a>
            @else
                <span aria-disabled="true" class="{{ $disabledClass }}">Next →</span>
            @endif
        </nav>
    @endif
</div>
