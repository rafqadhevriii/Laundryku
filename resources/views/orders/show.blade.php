@extends('layouts.app')
@section('title','Detail Pesanan')
@section('content')
@php($payment=$order->payment)
<x-ui.form-feedback/>
<div class="operation-intro coach-order-intro"><div><p class="eyebrow">Pesanan</p><p class="order-identity">{{ $order->order_number }}</p><div class="badge-group"><x-ui.status-badge :status="$order->status"/><x-ui.status-badge domain="payment" :status="$payment?->status"/></div></div><div class="action-group"><x-ui.button :href="route('orders.receipt',['order'=>$order->id,'paper'=>'80'])" variant="ghost" icon="report">Nota 80mm</x-ui.button><x-ui.button :href="route('orders.receipt',['order'=>$order->id,'paper'=>'58'])" variant="ghost">Nota 58mm</x-ui.button></div></div>
<section class="card"><x-ui.section-header title="Progres pengerjaan"/>
@if($order->status==='cancelled')<p class="notice notice-danger">Pesanan dibatalkan. Tidak ada tindakan pengerjaan berikutnya.</p>
@else
@php($steps=['received'=>'Diterima','processing'=>'Diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil'])
@php($current=array_search($order->status,array_keys($steps),true))
<ol class="order-timeline" aria-label="Tahapan pengerjaan">@foreach($steps as $value=>$label)<li class="{{ $loop->index<=$current?'step-reached':'' }} {{ $value===$order->status?'step-current':'' }}" @if($value===$order->status) aria-current="step" @endif><span class="step-marker" aria-hidden="true">@if($loop->index<$current)<x-ui.icon name="check"/>@else{{ $loop->iteration }}@endif</span><div><strong>{{ $label }}</strong><small>{{ $value===$order->status?'Status saat ini':($loop->index<$current?'Sudah dilewati':'Tahap berikutnya') }}</small></div></li>@endforeach</ol>
@endif
@php($next=['received'=>'processing','processing'=>'ready','ready'=>'picked_up'])
@if(isset($next[$order->status]))
<div class="next-action">@if($order->status==='ready' && $payment?->status!=='paid')<p><strong>Lunasi pembayaran sebelum penyerahan.</strong><br><a href="#payment-section">Periksa pembayaran pesanan</a>.</p>@else
<div><strong>Tindakan berikutnya</strong><p>{{ ['received'=>'Mulai pengerjaan setelah rincian pesanan diperiksa.','processing'=>'Tandai siap jika seluruh layanan telah selesai.','ready'=>'Pastikan cucian sudah diserahkan kepada pelanggan.'][$order->status] }}</p></div>
<form method="POST" action="{{ route('orders.status',$order->id) }}" @if($order->status==='ready') data-confirm-title="Tandai pesanan sudah diambil?" data-confirm-description="Pastikan cucian {{ $order->order_number }} sudah diserahkan. Status ini tidak dapat dikembalikan melalui aplikasi." @endif>@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $next[$order->status] }}"><x-ui.button icon="arrow">{{ ['received'=>'Mulai Proses','processing'=>'Tandai Siap Diambil','ready'=>'Tandai Sudah Diambil'][$order->status] }}</x-ui.button></form>
@endif</div>
@endif
<x-ui.field-error name="status"/></section>
<div class="detail-grid section-spaced">
<section class="card"><x-ui.section-header title="Informasi pesanan"/><dl class="identity-list"><div><dt>Pelanggan</dt><dd>{{ $order->customer?->name??'Tidak tersedia' }}</dd></div>@if(auth()->user()->isOwner())<div><dt>Cabang</dt><dd>{{ $order->branch?->name }}</dd></div>@endif<div><dt>Dibuat</dt><dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd></div><div><dt>Dicatat oleh</dt><dd>{{ $order->creator?->name }}</dd></div><div><dt>Estimasi selesai</dt><dd>{{ $order->estimated_at?->format('d/m/Y H:i')??'Belum tersedia' }}</dd></div>@if($order->picked_up_at)<div><dt>Diambil</dt><dd>{{ $order->picked_up_at->format('d/m/Y H:i') }}</dd></div>@endif</dl>
@if($order->estimated_at && now()->greaterThan($order->estimated_at) && !in_array($order->status,['ready','picked_up','cancelled']))<p class="notice notice-danger">Pesanan melewati estimasi selesai.</p>@endif
@if($order->notes)<div class="order-notes"><h3>Catatan pesanan</h3><p>{{ $order->notes }}</p></div>@endif</section>
<section class="card"><x-ui.section-header title="Rincian layanan"/><div class="table-wrap"><table><caption class="sr-only">Layanan pesanan {{ $order->order_number }}</caption><thead><tr><th scope="col">Layanan</th><th scope="col">Jumlah</th><th scope="col">Tarif</th><th scope="col">Subtotal</th></tr></thead><tbody>@foreach($order->items as $item)<tr><th scope="row" class="row-heading">{{ $item->service_name }}</th><td>{{ $item->quantity }} {{ $item->unit }}</td><td class="money">Rp {{ number_format($item->unit_price,2,',','.') }}</td><td class="money">Rp {{ number_format($item->subtotal,2,',','.') }}</td></tr>@endforeach</tbody></table></div><div class="order-total"><span>Total pesanan</span><strong>Rp {{ number_format($order->total,2,',','.') }}</strong></div></section>
</div>
@include('orders.partials.payment')
<x-ui.button :href="route('orders.index')" variant="ghost" class="section-spaced">Kembali ke daftar pesanan</x-ui.button>
@endsection
