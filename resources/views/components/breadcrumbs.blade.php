{{--
    The visible breadcrumb trail. The same array feeds the page's BreadcrumbList
    JSON-LD (App\Support\Seo\Schema::breadcrumbs()): item names keyed by path,
    starting with the homepage.
--}}
@props([
    'trail',
])

<nav aria-label="Breadcrumb" data-markdown-ignore {{ $attributes->class('font-meta text-[11px] uppercase tracking-[.08em] text-dim') }}>
    <ol class="flex flex-wrap gap-x-2 gap-y-1">
        @foreach ($trail as $path => $name)
            <li class="flex min-w-0 gap-2">
                @if ($loop->last)
                    <span aria-current="page" class="truncate">{{ $name }}</span>
                @else
                    <a href="{{ url($path) }}" wire:navigate class="text-mist transition-colors hover:text-frost">{{ $name }}</a>
                    <span aria-hidden="true">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
