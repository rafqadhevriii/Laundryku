@extends('layouts.app')
@section('title','Laporan Operasional')
@section('content')
<div class="card"><h2>Filter laporan operasional</h2><form method="GET" action="{{ route('owner.operational.index') }}" class="grid">
<div><label for="start">Dari tanggal</label><input id="start" type="date" name="start" value="{{ $start }}" required></div>
<div><label for="end">Sampai tanggal</label><input id="end" type="date" name="end" value="{{ $end }}" required></div>
@if($activeBranchId===null)
<div><label for="branch_id">Cabang</label><select id="branch_id" name="branch_id"><option value="">Semua cabang</option>@foreach($branches as $b)<option value="{{ $b->id }}" @selected($branch==$b->id)>{{ $b->name }}</option>@endforeach</select></div>
@else<div><p class="field-hint">Cabang laporan</p><p class="context-chip">{{ $activeBranchLabel }}</p></div>@endif

<div style="display:flex;align-items:end"><button type="submit">Tampilkan</button><a class="btn" style="margin-left:10px" href="{{ route('owner.operational.excel',request()->query()) }}">Unduh Excel</a></div></form>
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif</div>
<p class="muted" style="margin:18px 0">Jumlah pesanan dihitung berdasarkan tanggal pesanan dibuat. Pesanan terlambat mencakup pesanan dalam periode yang belum selesai sampai saat laporan dibuka. Kondisi stok menunjukkan jumlah terkini.</p>
<div class="grid">
@foreach(['received'=>'Diterima','processing'=>'Diproses','ready'=>'Siap diambil','picked_up'=>'Sudah diambil','cancelled'=>'Dibatalkan'] as $key=>$label)
<div class="card"><p class="muted">{{ $label }}</p><h2>{{ $statusCounts[$key]??0 }}</h2></div>@endforeach
<div class="card"><p class="muted">Shift dibuka</p><h2>{{ $shiftSummary['total'] }}</h2></div>
<div class="card"><p class="muted">Shift menunggu review</p><h2>{{ $shiftSummary['submitted'] }}</h2></div>
<div class="card"><p class="muted">Total selisih kas shift ditutup</p><h2>Rp {{ number_format($shiftSummary['difference'],0,',','.') }}</h2></div>
</div>
<div class="grid" style="margin-top:20px">
<section class="card"><h2>Pesanan terlambat</h2><div style="overflow:auto"><table><thead><tr><th>Pesanan</th><th>Cabang</th><th>Pelanggan</th><th>Estimasi</th></tr></thead><tbody>
@forelse($overdue as $order)<tr><td><a href="{{ route('orders.show',$order) }}">{{ $order->order_number }}</a></td><td>{{ $order->branch?->name }}</td><td>{{ $order->customer?->name }}</td><td>{{ $order->estimated_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="4">Tidak ada pesanan terlambat.</td></tr>@endforelse
</tbody></table></div>{{ $overdue->links() }}</section>
<section class="card"><h2>Stok menipis saat ini</h2><div style="overflow:auto"><table><thead><tr><th>Bahan</th><th>Cabang</th><th>Stok</th><th>Minimum</th></tr></thead><tbody>
@forelse($lowStock as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->branch?->name }}</td><td>{{ number_format($item->quantity,2,',','.') }} {{ $item->unit }}</td><td>{{ number_format($item->minimum_quantity,2,',','.') }}</td></tr>@empty<tr><td colspan="4">Tidak ada stok menipis.</td></tr>@endforelse
</tbody></table></div>{{ $lowStock->links() }}</section></div>
<div class="card" style="margin-top:20px"><h2>Riwayat shift kasir</h2><div style="overflow:auto"><table><thead><tr><th>Dibuka</th><th>Cabang</th><th>Kasir</th><th>Status</th><th>Kas seharusnya</th><th>Kas dihitung</th><th>Selisih</th></tr></thead><tbody>
@forelse($shiftRows as $shift)<tr><td>{{ $shift->opened_at?->format('d/m/Y H:i') }}</td><td>{{ $shift->branch?->name }}</td><td>{{ $shift->cashier?->name }}</td><td>{{ $shift->status }}</td><td>{{ $shift->expected_cash===null?'-':'Rp '.number_format($shift->expected_cash,0,',','.') }}</td><td>{{ $shift->closing_cash===null?'-':'Rp '.number_format($shift->closing_cash,0,',','.') }}</td><td>{{ $shift->difference===null?'-':'Rp '.number_format($shift->difference,0,',','.') }}</td></tr>@empty<tr><td colspan="7">Belum ada shift pada periode ini.</td></tr>@endforelse
</tbody></table></div>{{ $shiftRows->links() }}</div>
<div class="card" style="margin-top:20px"><h2>Aktivitas stok pada periode</h2><div style="overflow:auto"><table><thead><tr><th>Jenis</th><th>Status</th><th>Jumlah transaksi</th></tr></thead><tbody>
@forelse($movementCounts as $movement)<tr><td>{{ ['in'=>'Masuk','out'=>'Keluar','adjustment'=>'Koreksi'][$movement->type]??$movement->type }}</td><td>{{ $movement->status }}</td><td>{{ $movement->total }}</td></tr>@empty<tr><td colspan="3">Belum ada aktivitas stok.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
