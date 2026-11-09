@props(['title','description'=>null,'icon'=>'bag'])
<div {{ $attributes->class(['empty-state']) }}><span class="empty-state-icon"><x-ui.icon :name="$icon"/></span><h3>{{ $title }}</h3>@if($description)<p>{{ $description }}</p>@endif{{ $slot }}</div>
