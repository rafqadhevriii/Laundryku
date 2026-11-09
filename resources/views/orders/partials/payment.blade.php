<section class="card section-spaced" id="payment-section"><x-ui.section-header title="Pembayaran" description="Pembayaran penuh sekali bayar. Transfer dan QRIS perlu konfirmasi Pemilik."><x-slot:actions><x-ui.status-badge domain="payment" :status="$payment?->status"/></x-slot:actions></x-ui.section-header>
<div class="payment-grid"><div><div class="payment-amount"><p>{{ $payment?->status==='paid'?'Pembayaran lunas':'Total yang harus dibayar' }}</p><strong>Rp {{ number_format($order->total,2,',','.') }}</strong></div>
@if($payment)<dl class="identity-list"><div><dt>Metode</dt><dd>{{ ['cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS'][$payment->method]??$payment->method }}</dd></div><div><dt>Jumlah tercatat</dt><dd>Rp {{ number_format($payment->amount,2,',','.') }}</dd></div><div><dt>Status</dt><dd>{{ ['pending'=>'Menunggu konfirmasi','paid'=>'Lunas','rejected'=>'Ditolak'][$payment->status]??$payment->status }}</dd></div>@if($payment->reference)<div><dt>Referensi</dt><dd>{{ $payment->reference }}</dd></div>@endif @if($payment->paid_at)<div><dt>Lunas pada</dt><dd>{{ $payment->paid_at->format('d/m/Y H:i') }}</dd></div>@endif</dl>@endif
@if($payment?->status==='rejected')<div class="notice notice-danger"><strong>Pembayaran sebelumnya ditolak.</strong><p>{{ $payment->notes }}</p><p>Ajukan ulang transfer/QRIS untuk konfirmasi Pemilik. Riwayat penolakan tetap disimpan.</p></div>@elseif($payment?->notes)<p class="order-notes">{{ $payment->notes }}</p>@endif
@if($payment?->status==='pending')<p class="notice">Menunggu konfirmasi Pemilik. Pesanan belum dianggap lunas.</p>@elseif($payment?->status==='paid')<p class="notice notice-success"><x-ui.icon name="check"/>Pembayaran telah lunas. Tidak dapat dicatat ulang.</p>@endif
</div><div>
@if((!$payment || $payment->status==='rejected') && $order->status!=='cancelled')
@include('orders.partials.payment-form')
@elseif($order->status==='cancelled' && (!$payment || $payment->status==='rejected'))<p class="notice">Pesanan dibatalkan tidak dapat dibayar.</p>
@elseif($payment?->status==='pending' && auth()->user()->isOwner())
<div class="payment-decisions"><form method="POST" action="{{ route('payments.confirm',$order->id) }}" data-confirm-title="Konfirmasi pembayaran lunas?" data-confirm-description="Pastikan penerimaan {{ strtoupper($payment->method) }} sebesar Rp {{ number_format($payment->amount,2,',','.') }} telah diperiksa. Tindakan ini membuat pesanan lunas.">@csrf @method('PATCH')<x-ui.button icon="check">Konfirmasi Lunas</x-ui.button></form>
<form method="POST" action="{{ route('payments.reject',$order->id) }}" data-confirm-title="Tolak pembayaran ini?" data-confirm-description="Alasan penolakan akan disimpan dalam riwayat. Kasir dapat mengajukan ulang transfer atau QRIS.">@csrf @method('PATCH')<x-ui.field name="notes" label="Alasan penolakan" required maxlength="1000"/><x-ui.button variant="danger">Tolak Pembayaran</x-ui.button></form></div>
@elseif($payment?->status==='pending')<p class="field-hint">Hubungi Pemilik untuk memeriksa pembayaran digital ini. Kasir tidak dapat mengonfirmasi pelunasan.</p>
@endif
<x-ui.field-error name="payment"/>
</div></div>
@include('orders.partials.payment-history')
</section>
