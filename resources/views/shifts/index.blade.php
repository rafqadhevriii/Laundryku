@extends('layouts.app')
@section('title','Shift Kasir')
@section('content')
<div class="operation-intro"><div><p class="intro-greeting">Kas tercatat, pergantian shift lebih jelas.</p><p class="muted">Tunai terhubung ke shift penerima. Transfer/QRIS terpisah dari uang tunai.</p></div>@if(auth()->user()->isOwner())<x-ui.button :href="route('owner.reconciliation.index')" variant="ghost" icon="reconcile">Rekonsiliasi historis</x-ui.button>@endif</div>
<x-ui.form-feedback/>
@if(auth()->user()->isCashier())
<section class="card shift-workspace">
@if(!$open)
<x-ui.section-header title="Buka shift Anda" description="Hitung uang tunai di laci kas sebelum mulai menerima pembayaran."/>
<div class="shift-form-grid"><form method="POST" action="{{ route('shifts.store') }}">@csrf
<div class="field"><label for="opening_cash">Saldo awal tunai (Rp)</label><input id="opening_cash" name="opening_cash" type="number" min="0" max="999999999999.99" step="0.01" inputmode="decimal" required value="{{ old('opening_cash',0) }}" @if($errors->has('opening_cash')) aria-invalid="true" aria-describedby="opening_cash-error" @endif><x-ui.field-error name="opening_cash"/></div><x-ui.button class="full-width" icon="clock">Buka Shift</x-ui.button></form>
<div class="shift-help"><x-ui.icon name="wallet"/><h3>Satu shift aktif untuk akun Anda</h3><p>Saldo awal adalah uang tunai yang sudah ada sebelum penerimaan. Pembayaran tunai baru akan dicatat ke shift aktif Anda.</p></div></div>
@else
<x-ui.section-header title="Shift Anda sedang berjalan" :description="'Dibuka '.$open->opened_at->format('d/m/Y H:i').' · '.auth()->user()->branch?->name"><x-slot:actions><x-ui.status-badge domain="shift" :status="$open->status"/></x-slot:actions></x-ui.section-header>
<div class="shift-balance-grid"><div><span>Saldo awal tunai</span><strong>Rp {{ number_format($open->opening_cash,2,',','.') }}</strong></div><div><span>Kas masuk shift</span><strong class="balance-unavailable">Rincian belum tersedia</strong></div><div><span>Saldo seharusnya & selisih</span><strong class="balance-unavailable">Dihitung saat penutupan</strong></div></div>
<div class="shift-form-grid"><form method="POST" action="{{ route('shifts.close',$open->id) }}" data-confirm-title="Kirim penutupan shift?" data-confirm-description="Pastikan tunai aktual sudah dihitung. Saldo seharusnya dan selisih dihitung otomatis saat penutupan; shift yang ditutup tidak dapat menerima pembayaran tunai baru.">@csrf @method('PATCH')
<div class="field"><label for="closing_cash">Uang tunai aktual saat tutup (Rp)</label><input id="closing_cash" name="closing_cash" type="number" min="0" max="999999999999.99" step="0.01" inputmode="decimal" required value="{{ old('closing_cash') }}" @if($errors->has('closing_cash')) aria-invalid="true" @endif aria-describedby="closing_cash-hint {{ $errors->has('closing_cash')?'closing_cash-error':'' }}"><p class="field-hint" id="closing_cash-hint">Masukkan jumlah uang fisik yang benar-benar ada di laci kas.</p><x-ui.field-error name="closing_cash"/></div>
<x-ui.field name="notes" label="Catatan penutupan (opsional)" maxlength="2000"/><x-ui.button class="full-width" icon="check">Kirim Penutupan Shift</x-ui.button></form>
<div class="shift-help"><x-ui.icon name="report"/><h3>Periksa sebelum mengirim</h3><p>Saldo seharusnya dan selisih akan ditampilkan di riwayat setelah penutupan berhasil.</p><p>Pembayaran digital tidak masuk saldo tunai. Jika pembayaran historis belum ditinjau, hubungi Pemilik untuk rekonsiliasi sebelum menutup.</p></div></div>
@endif</section>
@endif
<section class="card section-spaced"><x-ui.section-header title="Riwayat shift" description="Saldo akhir dan selisih mengikuti hasil penutupan shift."/>
@if($shifts->isEmpty())<x-ui.empty-state title="Belum ada shift." :description="auth()->user()->isOwner()?'Shift kasir dari cabang usaha Anda akan muncul di sini.':'Buka shift pertama Anda sebelum menerima tunai.'" icon="clock"/>
@else
<div class="table-wrap"><table><caption class="sr-only">Saldo dan status shift sesuai hak akses Anda</caption><thead><tr><th scope="col">Kasir / cabang</th><th scope="col">Dibuka / ditutup</th><th scope="col">Saldo awal</th><th scope="col">Saldo seharusnya</th><th scope="col">Saldo aktual</th><th scope="col">Selisih kas</th><th scope="col">Status / tindakan</th></tr></thead><tbody>
@foreach($shifts as $shift)<tr><th scope="row" class="row-heading">{{ $shift->cashier?->name }}<small>{{ $shift->branch?->name }}</small></th><td>{{ $shift->opened_at->format('d/m/Y H:i') }}<small class="cell-subtitle">{{ $shift->closed_at?'Tutup '.$shift->closed_at->format('d/m/Y H:i'):'Belum ditutup' }}</small></td><td class="money">Rp {{ number_format($shift->opening_cash,2,',','.') }}</td><td class="money">{{ $shift->expected_cash===null?'Belum dihitung':'Rp '.number_format($shift->expected_cash,2,',','.') }}</td><td class="money">{{ $shift->closing_cash===null?'Belum ditutup':'Rp '.number_format($shift->closing_cash,2,',','.') }}</td><td>@if($shift->difference===null)<span class="muted">Belum dihitung</span>@else<span class="difference {{ (float)$shift->difference!==0.0?'difference-warning':'difference-balanced' }}">Rp {{ number_format($shift->difference,2,',','.') }}</span><small class="cell-subtitle">{{ (float)$shift->difference<0?'Kurang':((float)$shift->difference>0?'Lebih':'Sesuai') }}</small>@endif</td><td><x-ui.status-badge domain="shift" :status="$shift->status"/>
@if(auth()->user()->isOwner() && $shift->status==='submitted')<form method="POST" action="{{ route('shifts.review',$shift->id) }}" class="shift-review" data-confirm-title="Selesaikan tinjauan shift?" data-confirm-description="Periksa saldo dan selisih shift {{ $shift->cashier?->name }} sebelum menandainya ditinjau. Nilai saldo tidak dihitung ulang.">@csrf @method('PATCH')<x-ui.button variant="ghost">Tinjau Selesai</x-ui.button></form>@endif
@if($shift->notes)<p class="field-hint">Catatan: {{ $shift->notes }}</p>@endif</td></tr>@endforeach
</tbody></table></div>{{ $shifts->links('components.ui.pagination') }}
@endif</section>
@endsection
