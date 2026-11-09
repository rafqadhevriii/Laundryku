<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nota {{ $order->order_number }}</title>
<style>
*{box-sizing:border-box}body{font:12px Arial,sans-serif;color:#111;margin:0;background:#f1f5f9}.receipt{background:#fff;width:{{ $width }}mm;max-width:100%;margin:20px auto;padding:4mm}.center{text-align:center}.line{border-top:1px dashed #555;margin:9px 0}.flex{display:flex;justify-content:space-between;gap:5px}.flex span:last-child{text-align:right}p{margin:5px 0}h2{margin:0 0 6px;font-size:17px}table{width:100%;border-collapse:collapse}td{padding:5px 0;vertical-align:top}.actions{margin:20px auto;text-align:center}button,a{font:14px Arial;padding:9px 14px;cursor:pointer}a{color:#2563eb}@media print{@page{size:{{ $width }}mm auto;margin:0}body{background:white}.receipt{margin:0;padding:3mm;box-shadow:none}.actions{display:none}}
</style></head><body>
<div class="actions"><button onclick="window.print()">Cetak Nota</button> <a href="{{ route('orders.show',$order->id) }}">Kembali</a></div>
<div class="receipt">
<div class="center"><h2>{{ $business->name }}</h2>
@if($order->branch?->name)<p>{{ $order->branch->name }}</p>@endif
@if($business->address)<p>{{ $business->address }}</p>@endif
@if($business->phone)<p>{{ $business->phone }}</p>@endif
</div><div class="line"></div>
<p>No. {{ $order->order_number }}</p><p>Tanggal: {{ $order->created_at->format('d/m/Y H:i') }}</p>
<p>Pelanggan: {{ $order->customer?->name }}</p>
@if($order->customer?->phone)<p>Telepon: {{ $order->customer->phone }}</p>@endif
<p>Estimasi: {{ $order->estimated_at?->format('d/m/Y H:i')??'-' }}</p>
<div class="line"></div>
<table><tbody>@foreach($order->items as $item)
<tr><td>{{ $item->service_name }}<p>{{ number_format($item->quantity,2,',','.') }} {{ $item->unit }} x Rp {{ number_format($item->unit_price,0,',','.') }}</p></td><td style="text-align:right">Rp {{ number_format($item->subtotal,0,',','.') }}</td></tr>
@endforeach</tbody></table>
<div class="line"></div>
<div class="flex"><strong>Total</strong><strong>Rp {{ number_format($order->total,0,',','.') }}</strong></div>
<p>Metode: {{ ['cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS'][$order->payment?->method]??'Belum dipilih' }}</p>
<p>Status pembayaran: {{ ['pending'=>'Menunggu konfirmasi','paid'=>'Lunas','rejected'=>'Ditolak'][$order->payment?->status]??'Belum dibayar' }}</p>
@if($order->notes)<p>Catatan: {{ $order->notes }}</p>@endif
<div class="line"></div><p class="center">Terima kasih telah menggunakan layanan kami.</p>
</div></body></html>
