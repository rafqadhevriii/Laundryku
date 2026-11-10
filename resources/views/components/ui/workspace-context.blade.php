@props(['businessWide'=>false])
<p class="module-context"><x-ui.icon name="building"/><span>{{ $businessWide?'Seluruh usaha · Semua cabang':$activeBranchLabel }}</span></p>
