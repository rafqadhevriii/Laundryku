@extends('layouts.app')
@section('title','Dashboard '.($user->isOwner()?'Pemilik':'Kasir'))
@section('content')
@php
$scope=$activeBranchLabel;
$primary=['Pesanan hari ini','Siap diambil','Belum lunas','Pembayaran diterima hari ini'];
$meta=[
 'Pesanan hari ini'=>['bag','info','Dibuat hari ini','orders.index','Lihat pesanan'],
 'Siap diambil'=>['check','success','Status saat ini','orders.index','Tinjau pesanan'],
 'Belum lunas'=>['wallet','warning','Pesanan selain yang dibatalkan','orders.index','Tinjau pembayaran'],
 'Pembayaran diterima hari ini'=>['chart','success','Lunas / terkonfirmasi hari ini',$user->isOwner()?'owner.reports.index':'orders.index',$user->isOwner()?'Lihat laporan':'Lihat pesanan'],
 'Cabang aktif'=>['building','neutral',$activeBranchId?'Cabang terpilih · saat ini':'Usaha Anda · saat ini',null,null],
 'Sedang diproses'=>['shirt','info','Status saat ini',null,null],
 'Pesanan terlambat'=>['clock','danger','Belum siap dan melewati estimasi',null,null],
 'Stok menipis'=>['box','warning','Di bawah / sama dengan minimum',null,null],
 'Pengeluaran hari ini'=>['wallet','neutral','Pengeluaran tercatat hari ini',null,null],
 'Shift berjalan'=>['clock','info','Shift aktif akun Anda',null,null],
];
@endphp
<div class="operation-intro"><div><p class="intro-greeting">Selamat datang, {{ $user->name }}.</p><p class="muted">{{ $scope }} · Ringkasan operasional {{ now()->translatedFormat('d/m/Y') }}</p></div><div class="action-group"><x-ui.button :href="route('orders.create')" icon="bag">Pesanan baru</x-ui.button>@if($user->isOwner())<x-ui.button :href="route('owner.reports.index')" variant="ghost" icon="chart">Lihat laporan</x-ui.button>@else<x-ui.button :href="route('shifts.index')" variant="ghost" icon="clock">Kelola shift</x-ui.button>@endif</div></div>
<div class="kpi-grid">
@foreach($stats as $stat)@if(in_array($stat['label'],$primary))
@php([$icon,$tone,$context,$target,$action]=$meta[$stat['label']])
<x-ui.kpi :label="$stat['label']" :value="$stat['value']" :context="$scope.' · '.$context" :icon="$icon" :tone="$tone" :href="$target?route($target):null" :action="$action"/>
@endif
@endforeach
</div>
<div class="kpi-grid kpi-grid-secondary">
@foreach($stats as $stat)@if(!in_array($stat['label'],$primary))
@php([$icon,$tone,$context]=$meta[$stat['label']]??['grid','neutral','Status saat ini'])
<x-ui.kpi :label="$stat['label']" :value="$stat['value']" :context="$context" :icon="$icon" :tone="$tone"/>
@endif
@endforeach
</div>
<div class="attention-grid">
<section class="card attention-panel"><x-ui.section-header title="Pesanan terlambat" description="Prioritaskan pekerjaan yang melewati estimasi."><x-slot:actions><span class="status-badge status-danger">{{ $overdue->count() }} ditampilkan</span></x-slot:actions></x-ui.section-header>
@if($overdue->isEmpty())<x-ui.empty-state title="Tidak ada pesanan terlambat." description="Pesanan yang melewati estimasi akan muncul di sini." icon="check"/>
@else<ul class="attention-list">@foreach($overdue as $order)<li><div><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a><p>{{ $order->customer?->name }}</p><small>{{ $user->isOwner()?$order->branch?->name.' · ':'' }}Estimasi {{ $order->estimated_at?->format('d/m H:i') }}</small></div><span class="status-badge status-danger">Terlambat</span></li>@endforeach</ul>@endif</section>
<section class="card attention-panel"><x-ui.section-header title="Siap diambil" description="Periksa pelunasan sebelum menyerahkan cucian."><x-slot:actions><span class="status-badge status-success">{{ $ready->count() }} ditampilkan</span></x-slot:actions></x-ui.section-header>
@if($ready->isEmpty())<x-ui.empty-state title="Belum ada cucian yang siap diambil." description="Pesanan siap diambil akan muncul di sini." icon="bag"/>
@else<ul class="attention-list">@foreach($ready as $order)<li><div><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a><p>{{ $order->customer?->name }}</p><small>{{ $order->branch?->name }}</small></div><x-ui.status-badge :status="$order->status"/></li>@endforeach</ul>@endif</section>
<section class="card attention-panel"><x-ui.section-header title="Stok menipis" description="Bahan yang perlu diperiksa untuk operasional."><x-slot:actions><span class="status-badge status-warning">{{ $lowStock->count() }} ditampilkan</span></x-slot:actions></x-ui.section-header>
@if($lowStock->isEmpty())<x-ui.empty-state title="Stok masih di atas batas minimum." description="Bahan yang menipis akan muncul di sini." icon="box"/>
@else<ul class="attention-list">@foreach($lowStock as $item)<li><div><strong>{{ $item->name }}</strong><p>{{ $item->branch?->name }}</p></div><span class="status-badge status-warning">{{ number_format($item->quantity,2,',','.') }} {{ $item->unit }}</span></li>@endforeach</ul>@endif
<a class="section-footer-link" href="{{ route('inventory.index') }}">Kelola stok bahan<x-ui.icon name="arrow"/></a></section>
</div>
<section class="card section-spaced"><x-ui.section-header title="Pesanan terbaru" description="Maksimal delapan pesanan terbaru sesuai akses Anda."><x-slot:actions><x-ui.button :href="route('orders.index')" variant="ghost">Lihat semua</x-ui.button></x-slot:actions></x-ui.section-header>
@if($recent->isEmpty())<x-ui.empty-state title="Belum ada pesanan." description="Mulai mencatat pesanan agar aktivitas laundry dapat dipantau."><x-ui.button :href="route('orders.create')">Buat pesanan pertama</x-ui.button></x-ui.empty-state>
@else<div class="table-wrap"><table><caption class="sr-only">Pesanan terbaru {{ $scope }}</caption><thead><tr><th scope="col">Nomor pesanan</th><th scope="col">Pelanggan</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col">Detail</th></tr></thead><tbody>
@foreach($recent as $order)<tr><th scope="row" class="row-heading">{{ $order->order_number }}</th><td>{{ $order->customer?->name }}</td><td><x-ui.status-badge :status="$order->status"/></td><td class="money">Rp {{ number_format($order->total,2,',','.') }}</td><td><a href="{{ route('orders.show',$order) }}" aria-label="Buka pesanan {{ $order->order_number }}">Buka<x-ui.icon name="chevron"/></a></td></tr>@endforeach
</tbody></table></div>@endif</section>
@endsection
