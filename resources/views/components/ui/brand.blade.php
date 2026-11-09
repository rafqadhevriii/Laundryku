@props(['light' => false])
<span {{ $attributes->class(['brand']) }}><img src="{{ asset('brand/laundryku-logo'.($light?'-light':'').'.svg') }}" alt="Laundryku" width="222" height="48"></span>
