@props(['label'])
<div {{ $attributes->class(['table-wrap','module-table']) }} role="region" aria-label="{{ $label }}" tabindex="0">
<table><caption class="sr-only">{{ $label }}</caption>{{ $slot }}</table>
</div>
