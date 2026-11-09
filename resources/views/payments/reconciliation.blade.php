@extends('layouts.app')
@section('title','Rekonsiliasi Pembayaran Tunai Historis')
@section('content')
<div class="card">
<p>Periksa bukti sebelum memilih shift. Pembayaran tidak dihubungkan otomatis. Hanya shift terbuka pada cabang yang sama dan mencakup waktu pembayaran yang dapat dipilih. Nilainya akan masuk perhitungan saat shift ditutup.</p>
<p>Shift yang sudah ditutup/ditinjau bersifat final dan tidak dihitung ulang di halaman ini. Jika atribusi tidak dapat diselesaikan, tandai <strong>historis belum teralokasi</strong> dengan alasan. Uang tersebut tetap masuk pendapatan satu kali, tetapi tidak masuk kas shift dan tidak lagi memblokir penutupan.</p>
<form method="GET" class="grid">
<div><label>Cabang</label><select name="branch_id"><option value="">Semua cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
<div><label>Dari tanggal bayar</label><input type="date" name="start" value="{{ request('start') }}"></div>
<div><label>Sampai tanggal bayar</label><input type="date" name="end" value="{{ request('end') }}"></div>
<div><label>Nomor pesanan</label><input name="order_number" value="{{ request('order_number') }}" maxlength="150"></div>
<div><label>Keputusan</label><select name="review"><option value="all">Semua tanpa shift</option><option value="unreviewed" @selected(request('review')==='unreviewed')>Belum ditinjau</option><option value="unallocated" @selected(request('review')==='unallocated')>Historis belum teralokasi</option></select></div>
<div><button style="margin-top:40px">Terapkan filter</button></div>
</form>
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<p>Belum ditinjau: Rp {{ number_format($summary['unreviewed'],0,',','.') }} | Historis belum teralokasi: Rp {{ number_format($summary['unallocated'],0,',','.') }} (sesuai filter cabang/tanggal/pesanan).</p>
</div>
@forelse($payments as $payment)
<div class="card" style="margin-top:18px">
<h2><a href="{{ route('orders.show',$payment->order_id) }}">{{ $payment->order->order_number }}</a> — {{ $payment->order->branch?->name }}</h2>
<p>Rp {{ number_format($payment->amount,0,',','.') }} | Dibayar: {{ $payment->paid_at?->format('d/m/Y H:i')??'Waktu tidak tersedia' }} | Dicatat: {{ $payment->recorder?->name }}</p>
@if($payment->reconciliation_status==='unallocated')
<p><strong>Historis belum teralokasi</strong> — {{ $payment->reconciliation_reason }} ({{ $payment->reconciler?->name }}, {{ $payment->reconciled_at?->format('d/m/Y H:i') }}). Dapat dihubungkan kemudian jika bukti baru tersedia.</p>
@else<p>Belum ditinjau; masih dapat memblokir penutupan shift terkait.</p>@endif
<form method="POST" action="{{ route('owner.reconciliation.update',$payment->id) }}">@csrf @method('PATCH')
<label for="decision-{{ $payment->id }}">Keputusan</label><select id="decision-{{ $payment->id }}" name="decision" required><option value="">Pilih keputusan</option><option value="assign">Hubungkan ke shift berdasarkan bukti</option><option value="unallocated">Tandai historis belum teralokasi</option></select>
<label for="shift-{{ $payment->id }}">Shift tujuan (wajib jika menghubungkan)</label>
<select id="shift-{{ $payment->id }}" name="cash_shift_id"><option value="">Pilih shift — tidak dipilih otomatis</option>
@foreach($shifts as $shift)
@if($shift->branch_id===$payment->order->branch_id && $payment->paid_at && $payment->paid_at->gte($shift->opened_at) && $payment->paid_at->lte(now()))<option value="{{ $shift->id }}">#{{ $shift->id }} — {{ $shift->cashier?->name }} — {{ $shift->opened_at->format('d/m/Y H:i') }}</option>@endif
@endforeach</select>
<label for="reason-{{ $payment->id }}">Alasan dan bukti keputusan (wajib)</label><input id="reason-{{ $payment->id }}" name="reason" required maxlength="2000">
<button style="margin-top:14px">Simpan keputusan</button></form>
</div>
@empty<div class="card" style="margin-top:18px">Tidak ada pembayaran tunai tanpa shift sesuai filter.</div>@endforelse
{{ $payments->links() }}
<div class="card" style="margin-top:18px"><h2>Laporan keputusan rekonsiliasi</h2><p>Mencakup pengaitan dan pengecualian historis sesuai filter cabang/tanggal bayar/pesanan. Keputusan terdahulu tetap disimpan.</p>
<div style="overflow:auto"><table><thead><tr><th>Waktu / Pemilik</th><th>Pesanan</th><th>Keputusan sebelum → sesudah</th><th>Shift tujuan</th><th>Alasan</th></tr></thead><tbody>
@forelse($history as $entry)<tr><td>{{ $entry->created_at->format('d/m/Y H:i') }} / {{ $entry->user?->name }}</td><td>{{ $entry->details['order_number'] }}</td><td>{{ $entry->details['before']['reconciliation_status']??'Belum ditinjau' }} → {{ ['assigned'=>'Terhubung ke shift','unallocated'=>'Historis belum teralokasi'][$entry->details['after']['reconciliation_status']] }}</td><td>{{ $entry->details['target_shift_id']??'Tidak dialokasikan' }}</td><td>{{ $entry->details['reason'] }}</td></tr>
@empty<tr><td colspan="5">Belum ada keputusan.</td></tr>@endforelse
</tbody></table></div>{{ $history->links() }}</div>
@endsection
