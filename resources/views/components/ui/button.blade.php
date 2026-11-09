@props(['variant' => 'primary', 'icon' => null, 'href' => null, 'type' => 'submit'])
@if($href)
<a href="{{ $href }}" {{ $attributes->class(['btn','btn-'.$variant]) }}>@if($icon)<x-ui.icon :name="$icon"/>@endif{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->class(['btn','btn-'.$variant]) }}>@if($icon)<x-ui.icon :name="$icon"/>@endif<span>{{ $slot }}</span></button>
@endif
