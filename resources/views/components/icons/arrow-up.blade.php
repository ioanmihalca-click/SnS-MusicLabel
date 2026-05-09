@props(['strokeWidth' => 2])

<svg {{ $attributes->merge(['class' => 'w-4 h-4', 'fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor', 'aria-hidden' => 'true']) }}
    xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $strokeWidth }}" d="M5 10l7-7m0 0l7 7m-7-7v18" />
</svg>
