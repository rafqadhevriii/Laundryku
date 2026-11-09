@props(['title','description'=>null,'id'=>null])
<div {{ $attributes->class(['section-header']) }}><div><h2 @if($id) id="{{ $id }}" @endif>{{ $title }}</h2>@if($description)<p>{{ $description }}</p>@endif</div>@isset($actions)<div class="section-actions">{{ $actions }}</div>@endisset</div>
