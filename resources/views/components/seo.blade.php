{{--
    The SEO part of <head>, from an App\Support\Seo\SeoData: title, description,
    canonical, Open Graph and Twitter cards, the Markdown alternate for agents,
    search engine verification and the page's single JSON-LD graph.
--}}
@props(['seo'])

<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">

@if ($seo->noindex)
    <meta name="robots" content="noindex, follow">
@else
    <link rel="canonical" href="{{ $seo->canonicalUrl() }}">
    <link rel="alternate" type="text/markdown" href="{{ $seo->markdownUrl() }}">
@endif
<link rel="describedby" href="{{ \App\Support\Seo\PublicUrl::to('llms.txt') }}" type="text/plain">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:site_name" content="{{ \App\Support\Seo\SeoData::SITE_NAME }}">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonicalUrl() }}">
<meta property="og:image" content="{{ $seo->imageUrl() }}">
<meta property="og:image:alt" content="{{ $seo->title }}">

{{-- Twitter / X --}}
<meta name="twitter:card" content="{{ $seo->twitterCard() }}">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">
<meta name="twitter:image" content="{{ $seo->imageUrl() }}">

@if (filled(config('services.google.site_verification')))
    <meta name="google-site-verification" content="{{ config('services.google.site_verification') }}">
@endif
@if (filled(config('services.bing.site_verification')))
    <meta name="msvalidate.01" content="{{ config('services.bing.site_verification') }}">
@endif

<script type="application/ld+json">{!! $seo->jsonLd() !!}</script>
