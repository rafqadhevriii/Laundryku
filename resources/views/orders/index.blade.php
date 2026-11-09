@extends('layouts.app')
@section('title','Pesanan Laundry')
@section('content')
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
<form method="GET" action="{{ route('orders.index') }}" style="display:flex;gap:8px;flex-wrap:wrap">
<input name="search" placeholder="Nomor pesanan / pelanggan" value="{{ request('search') }}">
<select name="status"><option value="">Semua status</option>
@foreach(['received'=>'Diterima','processing'=>'Diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil','cancelled'=>'Dibatalkan'] as $value=>$label)
<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select><button>Cari</button></form>
<a class="btn" href="{{ route('orders.create') }}">Pesanan Baru</a></div>
<div style="overflow:auto;margin-top:20px"><table><thead><tr><th>Nomor</th><th>Pelanggan</th><th>Cabang</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Estimasi</th><th>Aksi</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td>{{ $order->order_number }}</td><td>{{ $order->customer?->name }}</td><td>{{ $order->branch?->name }}</td><td>Rp {{ number_format($order->total,0,',','.') }}</td><td>{{ ['pending'=>'Menunggu konfirmasi','paid'=>'Lunas','rejected'=>'Ditolak'][$order->payment?->status]??'Belum dibayar' }}</td><td>{{ ['received'=>'Diterima','processing'=>'Diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil','cancelled'=>'Dibatalkan'][$order->status]??$order->status }}</td><td>{{ $order->estimated_at?->format('d/m/Y H:i') }}</td><td><a href="{{ route('orders.show',$order->id) }}">Detail</a></td></tr>
@empty<tr><td colspan="8" class="muted">Belum ada pesanan.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:18px">{{ $orders->links() }}</div></div>
@endsection