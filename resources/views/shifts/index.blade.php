@extends('layouts.app')
@section('title','Shift Kasir')
@section('content')
@if(auth()->user()->isCashier())
<div class="card" style="margin-bottom:20px;max-width:650px">
@if(!$open)<h2>Buka shift</h2><form method="POST" action="{{ route('shifts.store') }}">@csrf
<label for="opening_cash">Saldo awal tunai (Rp)</label><input id="opening_cash" name="opening_cash" type="number" min="0" step="0.01" required value="{{ old('opening_cash',0) }}"><button style="margin-top:14px">Buka Shift</button></form>
@else<h2>Shift berjalan</h2><p>Dibuka {{ $open->opened_at->format('d/m/Y H:i') }}</p><p>Saldo awal: Rp {{ number_format($open->opening_cash,0,',','.') }}</p>
<form method="POST" action="{{ route('shifts.close',$open->id) }}">@csrf @method('PATCH')
<label for="closing_cash">Uang tunai aktual saat tutup (Rp)</label><input id="closing_cash" name="closing_cash" type="number" min="0" step="0.01" required>
<label for="notes">Catatan penutupan</label><input id="notes" name="notes" maxlength="2000">
<button style="margin-top:14px">Kirim Penutupan Shift</button></form>@endif
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
</div>@endif
<div class="card"><h2>Riwayat shift</h2><div style="overflow:auto"><table><thead><tr><th>Kasir</th><th>Cabang</th><th>Dibuka</th><th>Saldo awal</th><th>Ekspektasi</th><th>Aktual</th><th>Selisih</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($shifts as $shift)<tr><td>{{ $shift->cashier?->name }}</td><td>{{ $shift->branch?->name }}</td><td>{{ $shift->opened_at->format('d/m/Y H:i') }}</td><td>Rp {{ number_format($shift->opening_cash,0,',','.') }}</td><td>{{ $shift->expected_cash===null?'-':'Rp '.number_format($shift->expected_cash,0,',','.') }}</td><td>{{ $shift->closing_cash===null?'-':'Rp '.number_format($shift->closing_cash,0,',','.') }}</td><td>{{ $shift->difference===null?'-':'Rp '.number_format($shift->difference,0,',','.') }}</td><td>{{ ['open'=>'Berjalan','submitted'=>'Menunggu tinjauan','reviewed'=>'Ditinjau'][$shift->status] }}</td><td>
@if(auth()->user()->isOwner() && $shift->status==='submitted')<form method="POST" action="{{ route('shifts.review',$shift->id) }}">@csrf @method('PATCH')<button>Tinjau Selesai</button></form>@endif</td></tr>
@empty<tr><td colspan="9">Belum ada shift.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:15px">{{ $shifts->links() }}</div></div>
@endsection