@props(['route','pattern','icon'])
@php($active=request()->routeIs($pattern))
<a href="{{ route($route) }}" class="nav-link {{ $active?'is-active':'' }}" @if($active) aria-current="page" @endif><x-ui.icon :name="$icon"/><span>{{ $slot }}</span>@if($active)<span class="nav-current" aria-hidden="true"></span>@endif</a>
