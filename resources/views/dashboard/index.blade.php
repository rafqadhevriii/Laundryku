@extends('layouts.app')
@section('title','Dashboard '.($user->isOwner()?'Pemilik':'Kasir'))
@section('content')
<style>
.dashboard-intro{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.dashboard-intro p{margin:0}.dashboard-actions{display:flex;gap:8px;flex-wrap:wrap}
.dashboard-actions .btn{padding:10px 14px}
.metric-card{position:relative;min-height:112px}.metric-card p{margin:0 0 13px;font-size:13px}.metric-card h2{font-size:25px;line-height:1.2;margin:0;overflow-wrap:anywhere}
.section-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}.section-heading h2{font-size:18px;margin:0}
.dashboard-panels{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,350px),1fr));gap:18px;margin-top:22px}
.dashboard-panel{min-width:0}.dashboard-panel table{font-size:13px}.dashboard-panel th,.dashboard-panel td{padding:10px 7px}.dashboard-panel .muted{font-size:12px}
.tag{display:inline-block;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:600;white-space:nowrap}
.tag-red{background:#fef2f2;color:#b91c1c}.tag-amber{background:#fffbeb;color:#92400e}.tag-green{background:#ecfdf5;color:#047857}.tag-blue{background:#eff6ff;color:#1d4ed8}
.table-wrap{overflow-x:auto}.table-wrap table{min-width:440px}.empty-state{padding:20px 0;color:#64748b;font-size:13px}
</style>
<div class="dashboard-intro"><p class="muted">Selamat datang, {{ $user->name }}. {{ $user->isOwner()?'Ringkasan operasional seluruh cabang.':'Cabang: '.($user->branch?->name??'-') }}</p>
<div class="dashboard-actions"><a class="btn" href="{{ route('orders.create') }}">+ Pesanan baru</a>@if($user->isOwner())<a class="btn" style="background:#e8efff;color:#1d4ed8" href="{{ route('owner.reports.index') }}">Lihat laporan</a>@endif</div></div>
<div class="grid">
@foreach($stats as $stat)<div class="card metric-card"><p class="muted">{{ $stat['label'] }}</p><h2>{{ $stat['value'] }}</h2></div>@endforeach
</div>
<div class="dashboard-panels">
<section class="card dashboard-panel"><div class="section-heading"><h2>Pesanan terlambat</h2><span class="tag tag-red">{{ $overdue->count() }} ditampilkan</span></div>
@if($overdue->isEmpty())<p class="empty-state">Tidak ada pesanan terlambat.</p>@else<div class="table-wrap"><table><thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Estimasi</th><th>Aksi</th></tr></thead><tbody>
@foreach($overdue as $order)<tr><td>{{ $order->order_number }}@if($user->isOwner())<div class="muted">{{ $order->branch?->name }}</div>@endif</td><td>{{ $order->customer?->name }}</td><td>{{ $order->estimated_at?->format('d/m H:i') }}</td><td><a href="{{ route('orders.show',$order) }}">Buka</a></td></tr>@endforeach
</tbody></table></div>@endif</section>
<section class="card dashboard-panel"><div class="section-heading"><h2>Siap diambil</h2><span class="tag tag-green">{{ $ready->count() }} ditampilkan</span></div>
@if($ready->isEmpty())<p class="empty-state">Belum ada cucian yang siap diambil.</p>@else<div class="table-wrap"><table><thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Cabang</th><th>Aksi</th></tr></thead><tbody>
@foreach($ready as $order)<tr><td>{{ $order->order_number }}</td><td>{{ $order->customer?->name }}</td><td>{{ $order->branch?->name }}</td><td><a href="{{ route('orders.show',$order) }}">Buka</a></td></tr>@endforeach
</tbody></table></div>@endif</section>
<section class="card dashboard-panel"><div class="section-heading"><h2>Stok menipis</h2><span class="tag tag-amber">{{ $lowStock->count() }} ditampilkan</span></div>
@if($lowStock->isEmpty())<p class="empty-state">Stok masih di atas batas minimum.</p>@else<div class="table-wrap"><table><thead><tr><th>Bahan</th><th>Cabang</th><th>Stok</th></tr></thead><tbody>
@foreach($lowStock as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->branch?->name }}</td><td><span class="tag tag-amber">{{ number_format($item->quantity,2,',','.') }} {{ $item->unit }}</span></td></tr>@endforeach
</tbody></table></div>@endif<p style="margin-top:14px"><a href="{{ route('inventory.index') }}">Kelola stok bahan</a></p></section>
</div>
<section class="card" style="margin-top:22px"><div class="section-heading"><h2>Pesanan terbaru</h2><a href="{{ route('orders.index') }}">Lihat semua</a></div>
<div class="table-wrap"><table><thead><tr><th>Nomor</th><th>Pelanggan</th><th>Status</th><th>Total</th><th>Detail</th></tr></thead><tbody>
@forelse($recent as $order)<tr><td>{{ $order->order_number }}</td><td>{{ $order->customer?->name }}</td><td>@php($status=['received'=>['Diterima','tag-blue'],'processing'=>['Diproses','tag-amber'],'ready'=>['Siap diambil','tag-green'],'picked_up'=>['Sudah diambil','tag-blue'],'cancelled'=>['Dibatalkan','tag-red']][$order->status]??[$order->status,'tag-blue'])<span class="tag {{ $status[1] }}">{{ $status[0] }}</span></td><td>Rp {{ number_format($order->total,0,',','.') }}</td><td><a href="{{ route('orders.show',$order) }}">Buka</a></td></tr>
@empty<tr><td colspan="5">Belum ada pesanan.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
