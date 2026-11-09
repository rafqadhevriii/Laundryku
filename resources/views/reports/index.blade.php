@extends('layouts.app')
@section('title','Laporan Pendapatan')
@section('content')
<div class="card"><h2>Filter laporan</h2>
<form method="GET" action="{{ route('owner.reports.index') }}" class="grid">
<div><label for="start">Dari tanggal</label><input id="start" name="start" type="date" value="{{ $start }}" required></div>
<div><label for="end">Sampai tanggal</label><input id="end" name="end" type="date" value="{{ $end }}" required></div>
@if($activeBranchId===null)
<div><label for="branch_id">Cabang</label><select id="branch_id" name="branch_id"><option value="">Semua cabang</option>@foreach($branches as $b)<option value="{{ $b->id }}" @selected($branch==$b->id)>{{ $b->name }}</option>@endforeach</select></div>
@else<div><p class="field-hint">Cabang laporan</p><p class="context-chip">{{ $activeBranchLabel }}</p></div>@endif

<div style="display:flex;align-items:end;gap:10px"><button>Tampilkan</button><a class="btn" href="{{ route('owner.reports.export',request()->query()) }}">Ekspor CSV</a><a class="btn" href="{{ route('owner.reports.pdf',request()->query()) }}">Unduh PDF</a><a class="btn" href="{{ route('owner.reports.excel',request()->query()) }}">Unduh Excel</a></div>
</form>
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
</div>
<div class="grid" style="margin-top:20px">
<div class="card"><p class="muted">Pembayaran lunas diterima</p><h2>Rp {{ number_format($totalRevenue,0,',','.') }}</h2></div>
<div class="card"><p class="muted">Pengeluaran tercatat</p><h2>Rp {{ number_format($totalExpenses,0,',','.') }}</h2></div>
<div class="card"><p class="muted">Selisih penerimaan dan pengeluaran</p><h2>Rp {{ number_format($totalRevenue-$totalExpenses,0,',','.') }}</h2></div>
<div class="card"><p class="muted">Pesanan dibuat pada periode</p><h2>{{ $totalOrders }}</h2></div>
</div>
<div class="card" style="margin-top:20px"><h2>Rincian pembayaran lunas</h2>
<p class="muted">Pendapatan dihitung berdasarkan tanggal pembayaran terkonfirmasi, bukan tanggal pesanan dibuat. Selisih belum termasuk seluruh komponen laba usaha.</p>
<div style="overflow:auto"><table><thead><tr><th>Tanggal</th><th>Pesanan</th><th>Pelanggan</th><th>Cabang</th><th>Metode</th><th>Jumlah</th></tr></thead><tbody>
@forelse($payments as $p)<tr><td>{{ $p->paid_at?->format('d/m/Y H:i') }}</td><td>{{ $p->order?->order_number }}</td><td>{{ $p->order?->customer?->name }}</td><td>{{ $p->order?->branch?->name }}</td><td>{{ ['cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS'][$p->method]??$p->method }}</td><td>Rp {{ number_format($p->amount,0,',','.') }}</td></tr>
@empty<tr><td colspan="6">Belum ada pembayaran lunas pada periode ini.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:18px">{{ $payments->links() }}</div></div>
@endsection