@extends('layouts.app')
@section('title','Laporan Pendapatan')
@section('content')
<div class="module-stack">


<x-ui.form-feedback/>
<x-ui.report-filter :action="route('owner.reports.index')" :start="$start" :end="$end" :branch="$branch" :branches="$branches">
<x-ui.button variant="ghost" :href="route('owner.reports.export',request()->query())" icon="download">Ekspor CSV</x-ui.button><x-ui.button variant="ghost" :href="route('owner.reports.pdf',request()->query())" icon="download">Unduh PDF</x-ui.button><x-ui.button variant="ghost" :href="route('owner.reports.excel',request()->query())" icon="download">Unduh Excel</x-ui.button>
</x-ui.report-filter>
<div class="module-kpis module-kpis-four">
<x-ui.kpi label="Pembayaran lunas diterima" :value="'Rp '.number_format($totalRevenue,0,',','.')" context="Sesuai periode & cabang laporan" icon="wallet" tone="success"/>
<x-ui.kpi label="Pengeluaran tercatat" :value="'Rp '.number_format($totalExpenses,0,',','.')" context="Berdasarkan tanggal pengeluaran" icon="wallet"/>
<x-ui.kpi label="Selisih penerimaan dan pengeluaran" :value="'Rp '.number_format($totalRevenue-$totalExpenses,0,',','.')" context="Bukan laba bersih akuntansi" icon="chart" tone="info"/>
<x-ui.kpi label="Pesanan dibuat pada periode" :value="$totalOrders" context="Berdasarkan tanggal pesanan dibuat" icon="bag"/>
</div>
<section class="card"><x-ui.section-header title="Rincian pembayaran lunas" description="Pendapatan dihitung berdasarkan tanggal pembayaran terkonfirmasi, bukan tanggal pesanan dibuat. Selisih belum termasuk seluruh komponen laba usaha."/>
@if($payments->isEmpty())<x-ui.empty-state title="Belum ada pembayaran lunas pada periode ini." description="Coba rentang tanggal atau cabang lain. Pembayaran pending tidak dihitung sebagai pendapatan." icon="wallet"/>
@else
<x-ui.data-table label="Rincian pembayaran lunas"><thead><tr><th scope="col">Tanggal</th><th scope="col">Pesanan & pelanggan</th><th scope="col">Cabang</th><th scope="col">Metode</th><th scope="col" class="number-cell">Jumlah</th></tr></thead><tbody>
@foreach($payments as $p)<tr><td>{{ $p->paid_at?->format('d/m/Y H:i') }}</td><th scope="row"><a href="{{ route('orders.show',$p->order_id) }}">{{ $p->order?->order_number }}</a><span class="cell-subtext">{{ $p->order?->customer?->name }}</span></th><td>{{ $p->order?->branch?->name }}</td><td>{{ ['cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS'][$p->method]??$p->method }}</td><td class="number-cell">Rp {{ number_format($p->amount,0,',','.') }}</td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
{{ $payments->links('components.ui.pagination') }}
</section></div>
@endsection
