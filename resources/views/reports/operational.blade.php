@extends('layouts.app')
@section('title','Laporan Operasional')
@section('content')
<div class="module-stack">


<x-ui.form-feedback/>
<x-ui.report-filter :action="route('owner.operational.index')" :start="$start" :end="$end" :branch="$branch" :branches="$branches"><x-ui.button variant="ghost" :href="route('owner.operational.excel',request()->query())" icon="download">Unduh Excel</x-ui.button></x-ui.report-filter>
<p class="notice">Jumlah pesanan dihitung berdasarkan tanggal pesanan dibuat. Pesanan terlambat mencakup pesanan dalam periode yang belum selesai sampai saat laporan dibuka. Kondisi stok menunjukkan jumlah terkini.</p>
<div class="module-kpis module-kpis-four">
@foreach(['received'=>'Diterima','processing'=>'Diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil','cancelled'=>'Dibatalkan'] as $key=>$label)
<x-ui.kpi :label="$label" :value="$statusCounts[$key]??0" context="Pesanan dibuat pada periode" icon="bag"/>@endforeach
<x-ui.kpi label="Shift dibuka" :value="$shiftSummary['total']" context="Dibuka pada periode laporan" icon="clock"/>
<x-ui.kpi label="Shift menunggu review" :value="$shiftSummary['submitted']" context="Menunggu tinjauan Pemilik" icon="clock" tone="warning"/>
<x-ui.kpi label="Total selisih kas shift ditutup" :value="'Rp '.number_format($shiftSummary['difference'],0,',','.')" context="Selisih tersimpan pada shift final" icon="wallet"/>
</div>
<div class="module-two-column">
<section class="card"><h2>Pesanan terlambat</h2><x-ui.data-table label="Pesanan terlambat"><thead><tr><th scope="col">Pesanan</th><th scope="col">Cabang</th><th scope="col">Pelanggan</th><th scope="col">Estimasi</th></tr></thead><tbody>
@forelse($overdue as $order)<tr><td><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a></td><td>{{ $order->branch?->name }}</td><td>{{ $order->customer?->name }}</td><td>{{ $order->estimated_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="4"><x-ui.empty-state title="Tidak ada pesanan terlambat." icon="check"/></td></tr>@endforelse
</tbody></x-ui.data-table>{{ $overdue->links('components.ui.pagination') }}</section>
<section class="card"><h2>Stok menipis saat ini</h2><x-ui.data-table label="Stok menipis saat ini"><thead><tr><th scope="col">Bahan</th><th scope="col">Cabang</th><th scope="col">Stok</th><th scope="col">Minimum</th></tr></thead><tbody>
@forelse($lowStock as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->branch?->name }}</td><td>{{ number_format($item->quantity,2,',','.') }} {{ $item->unit }}</td><td>{{ number_format($item->minimum_quantity,2,',','.') }}</td></tr>@empty<tr><td colspan="4"><x-ui.empty-state title="Tidak ada stok menipis." icon="check"/></td></tr>@endforelse
</tbody></x-ui.data-table>{{ $lowStock->links('components.ui.pagination') }}</section></div>
<div class="card"><h2>Riwayat shift kasir</h2><x-ui.data-table label="Riwayat shift kasir"><thead><tr><th scope="col">Dibuka</th><th scope="col">Cabang</th><th scope="col">Kasir</th><th scope="col">Status</th><th scope="col">Kas seharusnya</th><th scope="col">Kas dihitung</th><th scope="col">Selisih</th></tr></thead><tbody>
@forelse($shiftRows as $shift)<tr><td>{{ $shift->opened_at?->format('d/m/Y H:i') }}</td><td>{{ $shift->branch?->name }}</td><td>{{ $shift->cashier?->name }}</td><td><x-ui.status-badge domain="shift" :status="$shift->status"/></td><td>{{ $shift->expected_cash===null?'-':'Rp '.number_format($shift->expected_cash,0,',','.') }}</td><td>{{ $shift->closing_cash===null?'-':'Rp '.number_format($shift->closing_cash,0,',','.') }}</td><td>{{ $shift->difference===null?'-':'Rp '.number_format($shift->difference,0,',','.') }}</td></tr>@empty<tr><td colspan="7"><x-ui.empty-state title="Belum ada shift pada periode ini." icon="check"/></td></tr>@endforelse
</tbody></x-ui.data-table>{{ $shiftRows->links('components.ui.pagination') }}</div>
<div class="card"><h2>Aktivitas stok pada periode</h2><x-ui.data-table label="Aktivitas stok pada periode"><thead><tr><th scope="col">Jenis</th><th scope="col">Status</th><th scope="col">Jumlah transaksi</th></tr></thead><tbody>
@forelse($movementCounts as $movement)<tr><td>{{ ['in'=>'Masuk','out'=>'Keluar','adjustment'=>'Koreksi'][$movement->type]??$movement->type }}</td><td><x-ui.status-badge domain="movement" :status="$movement->status"/></td><td>{{ $movement->total }}</td></tr>@empty<tr><td colspan="3"><x-ui.empty-state title="Belum ada aktivitas stok." icon="check"/></td></tr>@endforelse
</tbody></x-ui.data-table></div>
</div>
@endsection
