@extends('layouts.app')
@section('title','Pesanan Laundry')
@section('content')
<div class="operation-intro"><div><p class="intro-greeting">Semua pesanan, lebih mudah dipantau.</p><p class="muted">Cari pelanggan atau nomor pesanan dan ikuti status pengerjaannya.</p></div><x-ui.button :href="route('orders.create')" icon="bag">Pesanan Baru</x-ui.button></div>
<section class="card"><x-ui.section-header title="Cari & filter pesanan"/>
<form method="GET" action="{{ route('orders.index') }}" class="order-filters">
<div class="field"><label for="order-search">Nomor pesanan / pelanggan</label><input id="order-search" name="search" type="search" placeholder="Contoh: nomor pesanan atau nama" value="{{ request('search') }}"></div>
<x-ui.select name="status" id="order-status" label="Status pengerjaan"><option value="">Semua status</option>@foreach(['received'=>'Diterima','processing'=>'Diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil','cancelled'=>'Dibatalkan'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</x-ui.select>
<div class="filter-actions"><x-ui.button>Cari</x-ui.button>@if(request()->filled('search') || request()->filled('status'))<x-ui.button :href="route('orders.index')" variant="ghost">Reset filter</x-ui.button>@endif</div>
</form><x-ui.form-feedback/></section>
<section class="card section-spaced"><x-ui.section-header title="Daftar pesanan" :description="$orders->total().' pesanan sesuai pencarian dan hak akses Anda.'"/>
@if($orders->isEmpty())
<x-ui.empty-state :title="request()->filled('search')||request()->filled('status')?'Tidak ada pesanan yang sesuai.':'Belum ada pesanan.'" :description="request()->filled('search')||request()->filled('status')?'Coba kata pencarian atau status lain.':'Catat pesanan pertama untuk mulai memantau pekerjaan laundry.'">
@if(request()->filled('search')||request()->filled('status'))<x-ui.button :href="route('orders.index')" variant="ghost">Tampilkan semua pesanan</x-ui.button>@else<x-ui.button :href="route('orders.create')">Buat pesanan pertama</x-ui.button>@endif</x-ui.empty-state>
@else
<p class="result-count">Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan.</p>
<div class="table-wrap orders-table"><table><caption class="sr-only">Daftar pesanan dan pembayaran</caption><thead><tr><th scope="col">Pesanan / pelanggan</th><th scope="col">Cabang</th><th scope="col">Pengerjaan</th><th scope="col">Pembayaran</th><th scope="col">Total</th><th scope="col">Estimasi selesai</th><th scope="col">Aksi</th></tr></thead><tbody>
@foreach($orders as $order)<tr><th scope="row" class="row-heading">{{ $order->order_number }}<small>{{ $order->customer?->name }}</small></th><td>{{ $order->branch?->name }}</td><td><x-ui.status-badge :status="$order->status"/></td><td><x-ui.status-badge domain="payment" :status="$order->payment?->status"/></td><td class="money">Rp {{ number_format($order->total,2,',','.') }}</td><td>{{ $order->estimated_at?->format('d/m/Y H:i')??'Belum tersedia' }}</td><td><a href="{{ route('orders.show',$order->id) }}" aria-label="Detail pesanan {{ $order->order_number }}">Detail<x-ui.icon name="chevron"/></a></td></tr>@endforeach
</tbody></table></div>
<div class="orders-cards">@foreach($orders as $order)<article class="order-preview"><div class="order-preview-head"><a href="{{ route('orders.show',$order->id) }}">{{ $order->order_number }}</a><x-ui.status-badge :status="$order->status"/></div><p class="customer-name">{{ $order->customer?->name }}</p><p class="field-hint">{{ $order->branch?->name }}</p><dl class="compact-details"><div><dt>Total</dt><dd class="money">Rp {{ number_format($order->total,2,',','.') }}</dd></div><div><dt>Pembayaran</dt><dd><x-ui.status-badge domain="payment" :status="$order->payment?->status"/></dd></div><div><dt>Estimasi selesai</dt><dd>{{ $order->estimated_at?->format('d/m/Y H:i')??'Belum tersedia' }}</dd></div></dl><x-ui.button :href="route('orders.show',$order->id)" variant="ghost" class="full-width" :aria-label="'Detail pesanan '.$order->order_number">Detail pesanan<x-ui.icon name="arrow"/></x-ui.button></article>@endforeach</div>
{{ $orders->links('components.ui.pagination') }}
@endif</section>
@endsection
