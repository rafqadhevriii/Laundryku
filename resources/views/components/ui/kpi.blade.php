@props(['label','value','context','icon'=>'grid','tone'=>'neutral','href'=>null,'action'=>'Lihat rincian'])
<article {{ $attributes->class(['kpi-card','kpi-'.$tone]) }}>
<div class="kpi-top"><p>{{ $label }}</p><span class="kpi-icon"><x-ui.icon :name="$icon"/></span></div>
<p class="kpi-number">{{ $value }}</p><p class="kpi-context">{{ $context }}</p>
@if($href)<a class="kpi-link" href="{{ $href }}">{{ $action }}<x-ui.icon name="arrow"/></a>@endif
</article>
