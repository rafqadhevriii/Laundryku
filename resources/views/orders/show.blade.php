@extends('layouts.app')
@section('title','Detail Pesanan')
@section('content')
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
<div class="card"><h2>{{ $order->order_number }}</h2><p><a class="btn" href="{{ route('orders.receipt',$order->id) }}">Cetak Nota</a></p>
<p><span class="muted">Pelanggan:</span> {{ $order->customer?->name }}</p>
<p><span class="muted">Cabang:</span> {{ $order->branch?->name }}</p>
<p><span class="muted">Dibuat:</span> {{ $order->created_at->format('d/m/Y H:i') }}</p>
<p><span class="muted">Estimasi selesai:</span> {{ $order->estimated_at?->format('d/m/Y H:i') }}</p>
<p><span class="muted">Status:</span> {{ ['received'=>'Diterima','processing'=>'Sedang diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil','cancelled'=>'Dibatalkan'][$order->status]??$order->status }}</p>
@if($order->estimated_at && now()->greaterThan($order->estimated_at) && !in_array($order->status,['ready','picked_up','cancelled']))<p class="error">Pesanan melewati estimasi selesai.</p>@endif
@if($order->notes)<p><span class="muted">Catatan:</span> {{ $order->notes }}</p>@endif
@php($next=['received'=>'processing','processing'=>'ready','ready'=>'picked_up'])
@if(isset($next[$order->status]))
<form method="POST" action="{{ route('orders.status',$order->id) }}">@csrf @method('PATCH')
<input type="hidden" name="status" value="{{ $next[$order->status] }}">
<button>{{ ['received'=>'Mulai Proses','processing'=>'Tandai Siap Diambil','ready'=>'Tandai Sudah Diambil'][$order->status] }}</button></form>
@endif
@error('status')<p class="error">{{ $message }}</p>@enderror
</div>
<div class="card"><h2>Rincian layanan</h2><table><thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody>
@foreach($order->items as $item)<tr><td>{{ $item->service_name }}</td><td>{{ $item->quantity }} {{ $item->unit }}</td><td>Rp {{ number_format($item->unit_price,0,',','.') }}</td><td>Rp {{ number_format($item->subtotal,0,',','.') }}</td></tr>@endforeach
</tbody></table><h2 style="text-align:right">Total: Rp {{ number_format($order->total,0,',','.') }}</h2>
<p class="muted">Harga mengikuti tarif saat pesanan dibuat.</p></div></div>
<div class="card" style="margin-top:20px"><h2>Pembayaran</h2>
@php($payment=$order->payment)
@if(!$payment || ($payment->status==='rejected' && auth()->user()->isOwner()))
<p class="muted">Total yang harus dibayar: Rp {{ number_format($order->total,0,',','.') }}. Pembayaran harus lunas sekaligus.</p>
@if($payment?->status==='rejected')<p class="error">Pembayaran sebelumnya ditolak. Pemilik dapat mencatat ulang.</p>@endif
<form method="POST" action="{{ route('payments.store',$order->id) }}">@csrf
<label for="method">Metode pembayaran</label><select id="method" name="method" required><option value="cash">Tunai (langsung lunas)</option><option value="transfer">Transfer (perlu konfirmasi pemilik)</option><option value="qris">QRIS manual (perlu konfirmasi pemilik)</option></select>
@if(auth()->user()->isOwner())
<label for="cash_shift_id">Shift penerima tunai (wajib untuk tunai)</label>
<select id="cash_shift_id" name="cash_shift_id"><option value="">Pilih shift aktif pada cabang ini</option>
@foreach($cashShifts as $shift)<option value="{{ $shift->id }}" @selected(old('cash_shift_id') == $shift->id)>{{ $shift->cashier?->name }} — {{ $shift->opened_at->format('d/m/Y H:i') }}</option>@endforeach</select>
@error('cash_shift_id')<p class="error">{{ $message }}</p>@enderror
@else
<p class="muted">Pembayaran tunai dicatat ke shift aktif Anda. Buka shift sebelum menerima tunai.</p>
@endif
<label for="reference">Referensi transaksi (opsional)</label><input id="reference" name="reference" maxlength="120">
<label for="notes">Catatan (opsional)</label><input id="notes" name="notes" maxlength="1000">
@error('method')<p class="error">{{ $message }}</p>@enderror
<button style="margin-top:15px">Catat Pembayaran</button></form>
@else
<p>Metode: {{ ['cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS'][$payment->method]??$payment->method }}</p>
<p>Jumlah: Rp {{ number_format($payment->amount,0,',','.') }}</p>
<p>Status: {{ ['pending'=>'Menunggu konfirmasi','paid'=>'Lunas','rejected'=>'Ditolak'][$payment->status]??$payment->status }}</p>
@if($payment->reference)<p>Referensi: {{ $payment->reference }}</p>@endif
@if($payment->notes)<p>Catatan: {{ $payment->notes }}</p>@endif
@if($payment->status==='pending' && auth()->user()->isOwner())
<div style="display:flex;gap:10px;flex-wrap:wrap">
<form method="POST" action="{{ route('payments.confirm',$order->id) }}">@csrf @method('PATCH')<button>Konfirmasi Lunas</button></form>
<form method="POST" action="{{ route('payments.reject',$order->id) }}">@csrf @method('PATCH')<input name="notes" placeholder="Alasan penolakan" required><button style="margin-top:8px;background:#b91c1c">Tolak Pembayaran</button></form>
</div>
@endif
@endif
@error('payment')<p class="error">{{ $message }}</p>@enderror
</div>
<p style="margin-top:20px"><a href="{{ route('orders.index') }}">Kembali ke daftar pesanan</a></p>
@endsection