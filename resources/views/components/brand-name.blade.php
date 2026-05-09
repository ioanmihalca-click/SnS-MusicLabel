@props(['uppercase' => false, 'as' => 'span'])

@php
    $name = "Snow 'n' Stuff";
    $rendered = $uppercase ? mb_strtoupper($name) : $name;
@endphp

<{{ $as }} {{ $attributes }}>{!! $rendered !!}</{{ $as }}>
