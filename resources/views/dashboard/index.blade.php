@extends('layouts.app')
@section('title','Beranda')
@section('content')
@php
$metricMap=collect($stats)->keyBy('label');
$cards=[
 ['label'=>'Pesanan hari ini','key'=>'Pesanan hari ini','icon'=>'bag','tone'=>'violet','href'=>route('orders.index')],
 ['label'=>'Sedang diproses','key'=>'Sedang diproses','icon'=>'clock','tone'=>'blue','href'=>route('orders.index')],
 ['label'=>'Siap diambil','key'=>'Siap diambil','icon'=>'check','tone'=>'orange','href'=>route('orders.index')],
 ['label'=>'Pendapatan hari ini','key'=>'Pembayaran diterima hari ini','icon'=>'chart','tone'=>'green','href'=>$user->isOwner()?route('owner.reports.index'):route('orders.index')],
];
$needsAttention=$overdue->count()+$lowStock->count();
@endphp
<div class="studio-dashboard">
 <header class="studio-welcome">
  <div>
   <p class="studio-eyebrow"><span class="studio-online-indicator"></span><span data-business-date>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span></p>
   <h1>Selamat datang, {{ Str::before($user->name,' ') }} <span aria-hidden="true">✦</span></h1>
  </div>
  @if($user->isOwner() && $activeBranchId===null)
  <x-ui.button type="button" icon="plus" class="studio-primary-action" data-open-branch>Buat pesanan</x-ui.button>
  @else
  <x-ui.button :href="route('orders.create')" icon="plus" class="studio-primary-action">Buat pesanan</x-ui.button>
  @endif
 </header>

 <section class="studio-stats" aria-label="Ringkasan hari ini">
 @foreach($cards as $card)
 <a href="{{ $card['href'] }}" class="studio-stat studio-{{ $card['tone'] }}">
  <span class="studio-stat-top"><span class="studio-stat-icon"><x-ui.icon :name="$card['icon']"/></span><x-ui.icon name="arrow"/></span>
  <span class="studio-stat-value">{{ $metricMap->get($card['key'])['value'] ?? 0 }}</span>
  <span class="studio-stat-label">{{ $card['label'] }}</span>
 </a>
 @endforeach
 </section>

 <div class="studio-insights {{ !$user->isOwner()?'studio-insights-single':'' }}">
 @if($user->isOwner())
 <section class="studio-panel studio-chart-panel" aria-labelledby="studio-chart-title">
  <div class="studio-panel-heading"><div><h2 id="studio-chart-title">Tren pendapatan</h2><span>7 hari terakhir</span></div><a class="studio-link" href="{{ route('owner.reports.index') }}">Laporan <x-ui.icon name="arrow"/></a></div>
  @if($maxRevenue>0)
  <div class="studio-chart" role="img" aria-label="Pendapatan harian tujuh hari terakhir. @foreach($revenueHistory as $day){{ $day['date'] }}: Rp {{ number_format($day['total'],0,',','.') }}. @endforeach">
  @foreach($revenueHistory as $day)
   <div class="studio-chart-slot" title="{{ $day['date'] }}: Rp {{ number_format($day['total'],0,',','.') }}">
    <span class="studio-chart-track"><span class="studio-chart-bar" style="--bar-height:{{ $day['total']>0?max(4,($day['total']/$maxRevenue)*100):0 }}%; --bar-order:{{ $loop->index }}"></span></span>
    <span class="studio-chart-label">{{ $day['day'] }}</span>
   </div>
  @endforeach
  </div>
  @else
  <div class="studio-chart-empty"><x-ui.icon name="chart"/><span>Belum ada pembayaran lunas minggu ini.</span></div>
  @endif
 </section>
 @endif

 <section class="studio-panel studio-priority-panel" aria-labelledby="studio-priority-title">
  <div class="studio-panel-heading"><div><h2 id="studio-priority-title">Butuh tindakan</h2><span>{{ $needsAttention ? $needsAttention.' hal perlu dicek':'Semua terkendali' }}</span></div>
  @if($needsAttention)<span class="studio-priority-number">{{ $needsAttention }}</span>@endif
  </div>
  @if(!$needsAttention)
  <div class="studio-priority-empty"><span class="studio-success-emoji" aria-hidden="true">✓</span><strong>Operasional lancar</strong></div>
  @else
  <ul class="studio-priority-items">
  @foreach($overdue as $order)
   <li><span class="studio-priority-icon studio-priority-late"><x-ui.icon name="clock"/></span><span><strong><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a></strong><small>Pesanan melewati estimasi</small></span><x-ui.icon name="chevron"/></li>
  @endforeach
  @foreach($lowStock as $item)
   <li><span class="studio-priority-icon studio-priority-stock"><x-ui.icon name="box"/></span><span><strong><a href="{{ route('inventory.index') }}">{{ $item->name }}</a></strong><small>Stok tersisa {{ number_format($item->quantity,2,',','.') }} {{ $item->unit }}</small></span><x-ui.icon name="chevron"/></li>
  @endforeach
  </ul>
  @endif
 </section>
 </div>

 <section class="studio-panel studio-orders-panel" aria-labelledby="studio-orders-title">
  <div class="studio-panel-heading"><div><h2 id="studio-orders-title">Pesanan terbaru</h2></div><a href="{{ route('orders.index') }}" class="studio-link">Semua pesanan <x-ui.icon name="arrow"/></a></div>
  @if($recent->isEmpty())
  <x-ui.empty-state title="Belum ada pesanan." description="Mulai dengan menambahkan pesanan laundry." icon="bag"/>
  @else
  <div class="table-wrap studio-order-table"><table>
   <caption class="sr-only">Daftar pesanan terbaru</caption>
   <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col">Detail</th></tr></thead>
   <tbody>@foreach($recent as $order)
   <tr><th scope="row"><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a></th><td>{{ $order->customer?->name ?? 'Tanpa nama' }}</td><td><x-ui.status-badge :status="$order->status"/></td><td class="money">Rp {{ number_format($order->total,0,',','.') }}</td><td><a class="studio-row-action" href="{{ route('orders.show',$order) }}" aria-label="Buka pesanan {{ $order->order_number }}"><x-ui.icon name="arrow"/></a></td></tr>
   @endforeach</tbody>
  </table></div>
  @endif
 </section>
</div>
@endsection
