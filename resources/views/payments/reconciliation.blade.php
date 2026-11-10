@extends('layouts.app')
@section('title','Rekonsiliasi Pembayaran Tunai Historis')
@section('content')
<div class="module-stack">
<x-ui.section-header title="Tinjau tunai historis berdasarkan bukti" description="Pembayaran tidak dihubungkan otomatis. Keputusan dan alasan tersimpan pada riwayat, tanpa menggandakan pendapatan."/>
<x-ui.workspace-context/>
<x-ui.form-feedback/>
<div class="module-kpis">
<x-ui.kpi label="Belum ditinjau" :value="'Rp '.number_format($summary['unreviewed'],0,',','.')" context="Sesuai filter cabang, tanggal & pesanan" tone="warning" icon="clock"/>
<x-ui.kpi label="Historis belum teralokasi" :value="'Rp '.number_format($summary['unallocated'],0,',','.')" context="Tetap masuk pendapatan, tidak masuk kas shift" icon="wallet"/>
</div>
<section class="card"><details class="module-details"><summary>Panduan keputusan rekonsiliasi</summary><div class="module-help">
<p>Periksa bukti sebelum memilih shift. Hanya shift terbuka pada cabang yang sama dan mencakup waktu pembayaran yang dapat dipilih. Nilainya akan masuk perhitungan saat shift ditutup.</p>
<p>Shift yang sudah ditutup/ditinjau bersifat final dan tidak dihitung ulang di halaman ini. Jika atribusi tidak dapat diselesaikan, tandai <strong>historis belum teralokasi</strong> dengan alasan. Uang tersebut tetap masuk pendapatan satu kali, tetapi tidak masuk kas shift dan tidak lagi memblokir penutupan.</p>
</div></details>
<form method="GET" action="{{ route('owner.reconciliation.index') }}" class="module-form-grid">
@if($activeBranchId===null)<x-ui.select name="branch_id" label="Cabang"><option value="">Semua cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</x-ui.select>
@else<div class="field"><p class="field-label">Cabang rekonsiliasi</p><p class="context-chip">{{ $activeBranchLabel }}</p></div>@endif
<x-ui.form-control name="start" label="Dari tanggal bayar" type="date" :value="request('start')"/>
<x-ui.form-control name="end" label="Sampai tanggal bayar" type="date" :value="request('end')"/>
<x-ui.form-control name="order_number" label="Nomor pesanan" :value="request('order_number')" maxlength="150"/>
<x-ui.select name="review" label="Keputusan"><option value="all">Semua tanpa shift</option><option value="unreviewed" @selected(request('review')==='unreviewed')>Belum ditinjau</option><option value="unallocated" @selected(request('review')==='unallocated')>Historis belum teralokasi</option></x-ui.select>
<div class="form-actions"><x-ui.button icon="search">Terapkan filter</x-ui.button><x-ui.button variant="ghost" :href="route('owner.reconciliation.index')">Reset</x-ui.button></div>
</form></section>
@forelse($payments as $payment)
@php($restore=old('_form_context')==='reconcile-'.$payment->id)
<section class="card reconciliation-card"><div class="reconciliation-heading"><div><h2><a href="{{ route('orders.show',$payment->order_id) }}">{{ $payment->order->order_number }}</a></h2><p class="muted">{{ $payment->order->branch?->name }}</p></div><x-ui.status-badge domain="reconciliation" :status="$payment->reconciliation_status??'unreviewed'"/></div>
<dl class="module-facts"><div><dt>Jumlah pembayaran</dt><dd class="amount-emphasis">Rp {{ number_format($payment->amount,0,',','.') }}</dd></div><div><dt>Dibayar</dt><dd>{{ $payment->paid_at?->format('d/m/Y H:i')??'Waktu tidak tersedia' }}</dd></div><div><dt>Dicatat oleh</dt><dd>{{ $payment->recorder?->name }}</dd></div></dl>
@if($payment->reconciliation_status==='unallocated')<div class="notice"><strong>Historis belum teralokasi</strong><p>{{ $payment->reconciliation_reason }} ({{ $payment->reconciler?->name }}, {{ $payment->reconciled_at?->format('d/m/Y H:i') }}). Dapat dihubungkan kemudian jika bukti baru tersedia.</p></div>
@else<p class="notice">Belum ditinjau; masih dapat memblokir penutupan shift terkait.</p>@endif
<form method="POST" action="{{ route('owner.reconciliation.update',$payment->id) }}" class="module-form" data-confirm-title="Simpan keputusan rekonsiliasi?" data-confirm-description="Pastikan pesanan, keputusan, shift tujuan, serta alasan sesuai bukti. Pengaitan yang tersimpan tidak dapat dipindah melalui halaman ini.">@csrf @method('PATCH')
<input type="hidden" name="_form_context" value="reconcile-{{ $payment->id }}">
<div class="module-form-grid">
<x-ui.select name="decision" :id="'decision-'.$payment->id" :error-key="$restore?'decision':'_inactive_decision'" label="Keputusan (wajib)" required><option value="">Pilih keputusan</option><option value="assign" @selected($restore && old('decision')==='assign')>Hubungkan ke shift berdasarkan bukti</option><option value="unallocated" @selected($restore && old('decision')==='unallocated')>Tandai historis belum teralokasi</option></x-ui.select>
<x-ui.select name="cash_shift_id" :id="'shift-'.$payment->id" :error-key="$restore?'cash_shift_id':'_inactive_shift'" label="Shift tujuan" hint="Wajib jika menghubungkan. Tidak dipilih otomatis; hanya shift terbuka yang sesuai ditampilkan."><option value="">Pilih shift — tidak dipilih otomatis</option>
@foreach($shifts as $shift)
@if($shift->branch_id===$payment->order->branch_id && $payment->paid_at && $payment->paid_at->gte($shift->opened_at) && $payment->paid_at->lte(now()))<option value="{{ $shift->id }}" @selected($restore && old('cash_shift_id')==$shift->id)>#{{ $shift->id }} — {{ $shift->cashier?->name }} — {{ $shift->opened_at->format('d/m/Y H:i') }}</option>@endif
@endforeach</x-ui.select>
</div>
<div class="field"><label for="reason-{{ $payment->id }}">Alasan dan bukti keputusan (wajib)</label><textarea id="reason-{{ $payment->id }}" name="reason" rows="3" required maxlength="2000" @if($restore && $errors->has('reason')) aria-invalid="true" aria-describedby="reason-error-{{ $payment->id }}" @endif>{{ $restore?old('reason'):'' }}</textarea>@if($restore)<x-ui.field-error name="reason" :id="'reason-error-'.$payment->id"/>@endif</div>
<div class="form-actions"><x-ui.button icon="check">Simpan keputusan</x-ui.button></div></form>
</section>
@empty<section class="card"><x-ui.empty-state title="Tidak ada pembayaran tunai tanpa shift sesuai filter." description="Coba filter lain. Pembayaran yang sudah terhubung tetap tercatat dalam riwayat keputusan." icon="check"/></section>@endforelse
{{ $payments->links('components.ui.pagination') }}
<section class="card"><x-ui.section-header title="Laporan keputusan rekonsiliasi" description="Mencakup pengaitan dan pengecualian historis sesuai filter cabang/tanggal bayar/pesanan. Keputusan terdahulu tetap disimpan."/>
@if($history->isEmpty())<x-ui.empty-state title="Belum ada keputusan." description="Keputusan yang disimpan akan mencatat Pemilik, waktu, dan alasannya." icon="clock"/>
@else
<x-ui.data-table label="Riwayat keputusan rekonsiliasi"><thead><tr><th scope="col">Waktu / Pemilik</th><th scope="col">Pesanan</th><th scope="col">Keputusan sebelum → sesudah</th><th scope="col">Shift tujuan</th><th scope="col">Alasan</th></tr></thead><tbody>
@foreach($history as $entry)<tr><td>{{ $entry->created_at->format('d/m/Y H:i') }}<span class="cell-subtext">{{ $entry->user?->name }}</span></td><th scope="row">{{ $entry->details['order_number'] }}</th><td>{{ ['assigned'=>'Terhubung ke shift','unallocated'=>'Historis belum teralokasi'][$entry->details['before']['reconciliation_status']??'']??'Belum ditinjau' }} → {{ ['assigned'=>'Terhubung ke shift','unallocated'=>'Historis belum teralokasi'][$entry->details['after']['reconciliation_status']] }}</td><td>{{ $entry->details['target_shift_id']??'Tidak dialokasikan' }}</td><td>{{ $entry->details['reason'] }}</td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
{{ $history->links('components.ui.pagination') }}
</section></div>
@endsection
