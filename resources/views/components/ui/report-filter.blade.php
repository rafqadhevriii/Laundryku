@props(['action','start','end','branch','branches'])
<section class="card"><x-ui.section-header title="Periode & cakupan laporan" description="Terapkan filter untuk memperbarui rincian. Tombol unduh menggunakan filter laporan yang sedang tampil."/>
<form method="GET" action="{{ $action }}" class="report-filter">
<x-ui.form-control name="start" label="Dari tanggal" type="date" :value="$start" required/>
<x-ui.form-control name="end" label="Sampai tanggal" type="date" :value="$end" required/>
@if($activeBranchId===null)<x-ui.select name="branch_id" label="Cabang laporan"><option value="">Semua cabang</option>@foreach($branches as $b)<option value="{{ $b->id }}" @selected($branch==$b->id)>{{ $b->name }}</option>@endforeach</x-ui.select>
@else<div class="field"><p class="field-label">Cabang laporan</p><p class="context-chip">{{ $activeBranchLabel }}</p></div>@endif
<div class="form-actions"><x-ui.button icon="search">Tampilkan</x-ui.button></div>
</form><div class="report-downloads"><span>Unduh laporan:</span>{{ $slot }}</div>
</section>
