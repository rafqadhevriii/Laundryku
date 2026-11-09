@if($paginator->hasPages())
<nav class="operation-pagination" aria-label="Navigasi halaman">
@if($paginator->onFirstPage())<span class="pagination-disabled" aria-disabled="true">Sebelumnya</span>@else<a class="btn btn-ghost" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif
<p>Halaman <strong>{{ $paginator->currentPage() }}</strong> dari {{ $paginator->lastPage() }}</p>
@if($paginator->hasMorePages())<a class="btn btn-ghost" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>@else<span class="pagination-disabled" aria-disabled="true">Berikutnya</span>@endif
<div class="pagination-pages">
@foreach($elements as $element)
@if(is_string($element))
<span class="pagination-ellipsis" aria-hidden="true">{{ $element }}</span>
@else
@foreach($element as $page=>$url)
@if($page===$paginator->currentPage())
<span class="pagination-current" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
@else
<a class="btn btn-ghost pagination-page" href="{{ $url }}" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
@endif
@endforeach
@endif
@endforeach
</div>
</nav>
@endif
