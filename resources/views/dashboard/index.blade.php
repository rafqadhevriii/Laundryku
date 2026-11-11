@extends('layouts.app')
@section('title','Dashboard '.($user->isOwner()?'Pemilik':'Kasir'))
@section('content')
@php
$metricMap=collect($stats)->keyBy('label');
$cards=[
 ['label'=>'Pesanan hari ini','icon'=>'bag','tone'=>'mint','href'=>route('orders.index')],
 ['label'=>'Siap diambil','icon'=>'check','tone'=>'mint','href'=>route('orders.index')],
 ['label'=>'Belum lunas','icon'=>'wallet','tone'=>'amber','href'=>route('orders.index')],
 ['label'=>'Pembayaran diterima hari ini','icon'=>'chart','tone'=>'mint','href'=>$user->isOwner()?route('owner.reports.index'):route('orders.index')],
];
$attentionCount=$overdue->count()+$lowStock->count();
@endphp
<div class="dashboard-modern">
 <header class="dashboard-hero">
  <div>
   <p class="dashboard-kicker">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
   <h1>Ringkasan hari ini</h1>
   <p>{{ $user->name }} · {{ $activeBranchLabel }}</p>
  </div>
  <x-ui.button :href="route('orders.create')" icon="bag" class="dashboard-new-order">Tambah Pesanan</x-ui.button>
 </header>

 <section class="dashboard-metrics" aria-label="Statistik operasional utama">
  @foreach($cards as $card)
  <a class="dashboard-metric dashboard-metric-{{ $card['tone'] }}" href="{{ $card['href'] }}">
   <span class="dashboard-metric-heading"><span>{{ $card['label']==='Pembayaran diterima hari ini'?'Pendapatan hari ini':$card['label'] }}</span>
    <span class="dashboard-metric-icon" aria-hidden="true"><x-ui.icon :name="$card['icon']"/></span>
   </span>
   <strong class="dashboard-metric-value">{{ $metricMap->get($card['label'])['value'] ?? 0 }}</strong>
   <span class="dashboard-metric-footer">Lihat rincian <x-ui.icon name="arrow"/></span>
  </a>
  @endforeach
 </section>

 <div class="dashboard-main-grid {{ !$user->isOwner()?'dashboard-main-grid-single':'' }}">
 @if($user->isOwner())
 <section class="card dashboard-revenue" aria-labelledby="dashboard-revenue-title">
  <div class="dashboard-section-top">
   <div><h2 id="dashboard-revenue-title">Pendapatan mingguan</h2><p>Hanya pembayaran yang sudah lunas dan diterima.</p></div>
   
  </div>
  @if($maxRevenue>0)
   <div class="dashboard-chart" role="img" aria-label="Pendapatan harian selama tujuh hari terakhir. @foreach($revenueHistory as $day){{ $day['date'] }}: Rp {{ number_format($day['total'],0,',','.') }}. @endforeach">
    @foreach($revenueHistory as $day)
    <div class="dashboard-chart-column">
     <div class="dashboard-bar-track">
      <span class="dashboard-bar {{ $day['total']===$maxRevenue?'dashboard-bar-high':'' }}"
       style="--bar-height: {{ $day['total']>0?max(3,($day['total']/$maxRevenue)*100):0 }}%;"
       title="{{ $day['date'] }}: Rp {{ number_format($day['total'],0,',','.') }}"></span>
     </div>
     <span class="dashboard-chart-day">{{ $day['day'] }}</span>
    </div>
    @endforeach
   </div>
  @else
   <div class="dashboard-chart-empty">
    <x-ui.icon name="chart"/>
    <strong>Belum ada pembayaran diterima</strong>
    <p>Grafik akan terisi setelah ada pembayaran lunas dalam tujuh hari terakhir.</p>
   </div>
  @endif
  <a href="{{ route('owner.reports.index') }}" class="dashboard-text-link">Lihat laporan pendapatan <x-ui.icon name="arrow"/></a>
 </section>
 @endif
 <section class="card dashboard-attention" aria-labelledby="dashboard-attention-title">
  <div class="dashboard-section-top">
   <div><h2 id="dashboard-attention-title">Perlu perhatian</h2><p>Prioritas operasional yang perlu ditindaklanjuti.</p></div>
   @if($attentionCount>0)<span class="dashboard-attention-count">{{ $attentionCount }} item</span>@endif
  </div>
  @if($overdue->isEmpty() && $lowStock->isEmpty() && $ready->isEmpty())
   <div class="dashboard-attention-empty"><x-ui.icon name="check"/><span>Semua terpantau. Tidak ada tindakan mendesak.</span></div>
  @else
   <ul class="dashboard-todo-list">
    @foreach($overdue as $order)
     <li><span class="dashboard-todo-dot dashboard-todo-dot-danger" aria-hidden="true"></span><div><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a><small>Pesanan terlambat · {{ $order->customer?->name }}</small></div><x-ui.status-badge status="processing"/></li>
    @endforeach
    @foreach($ready as $order)
     <li><span class="dashboard-todo-dot dashboard-todo-dot-success" aria-hidden="true"></span><div><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a><small>Siap diambil · {{ $order->customer?->name }}</small></div><x-ui.status-badge status="ready"/></li>
    @endforeach
    @foreach($lowStock as $item)
     <li><span class="dashboard-todo-dot dashboard-todo-dot-warning" aria-hidden="true"></span><div><a href="{{ route('inventory.index') }}">{{ $item->name }}</a><small>Stok menipis · {{ number_format($item->quantity,2,',','.') }} {{ $item->unit }}</small></div><span class="status-badge status-warning">Stok</span></li>
    @endforeach
   </ul>
  @endif
  <a href="{{ route('orders.index') }}" class="dashboard-text-link">Lihat semua pesanan <x-ui.icon name="arrow"/></a>
 </section>
 </div>

 <section class="card dashboard-recent" aria-labelledby="dashboard-recent-title">
  <div class="dashboard-section-top">
   <div><h2 id="dashboard-recent-title">Pesanan terbaru</h2><p>Aktivitas pesanan terbaru.</p></div>
   <a href="{{ route('orders.index') }}" class="dashboard-text-link">Lihat semua <x-ui.icon name="arrow"/></a>
  </div>
  @if($recent->isEmpty())
    <x-ui.empty-state title="Belum ada pesanan." description="Pesanan pertama Anda akan tampil di bagian ini." icon="bag">
     <x-ui.button :href="route('orders.create')">Tambah Pesanan</x-ui.button>
    </x-ui.empty-state>
  @else
   <div class="table-wrap dashboard-table-wrap">
    <table>
     <caption class="sr-only">Pesanan terbaru {{ $activeBranchLabel }}</caption>
     <thead><tr><th scope="col">No. Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col">Aksi</th></tr></thead>
     <tbody>
     @foreach($recent as $order)
      <tr><th scope="row"><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a></th><td>{{ $order->customer?->name ?? 'Tanpa nama' }}</td><td><x-ui.status-badge :status="$order->status"/></td><td class="money">Rp {{ number_format($order->total,0,',','.') }}</td><td><a href="{{ route('orders.show',$order) }}" class="dashboard-open" aria-label="Buka detail pesanan {{ $order->order_number }}">Detail <x-ui.icon name="arrow"/></a></td></tr>
     @endforeach
     </tbody>
    </table>
   </div>
  @endif
 </section>
</div>
@endsection
