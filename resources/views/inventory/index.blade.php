@extends('layouts.app')
@section('title','Stok Bahan Laundry')
@section('content')
@if(auth()->user()->isOwner())
<div class="card" style="margin-bottom:20px"><h2>Tambah barang stok</h2>
<form method="POST" action="{{ route('inventory.store') }}">@csrf
<div class="grid">
<div><label for="name">Nama barang</label><input id="name" name="name" required value="{{ old('name') }}"></div>
<div><label for="branch_id">Cabang</label><select id="branch_id" name="branch_id" required><option value="">Pilih cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
<div><label for="category">Kategori</label><select id="category" name="category">@foreach(['Deterjen','Pewangi dan pelembut','Pemutih dan bahan khusus','Kemasan dan hanger','Operasional lain'] as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach</select></div>
<div><label for="unit">Satuan</label><input id="unit" name="unit" placeholder="Liter, kg, pcs" required></div>
<div><label for="minimum_quantity">Batas minimum</label><input id="minimum_quantity" name="minimum_quantity" type="number" min="0" step="0.01" required value="0"></div>
</div><button style="margin-top:15px">Simpan Barang</button></form></div>
@endif
<div class="card"><h2>Daftar persediaan</h2>
<div style="overflow:auto"><table><thead><tr><th>Barang</th><th>Cabang</th><th>Kategori</th><th>Stok</th><th>Minimum</th><th>Status</th><th>Mutasi / Koreksi</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->name }}</td><td>{{ $item->branch?->name }}</td><td>{{ $item->category }}</td><td>{{ $item->quantity }} {{ $item->unit }}</td><td>{{ $item->minimum_quantity }}</td>
<td>@if((float)$item->quantity<=(float)$item->minimum_quantity)<span class="error">Stok menipis</span>@else Aman @endif</td>
<td><form method="POST" action="{{ route('inventory.move',$item->id) }}" style="display:flex;gap:6px;flex-wrap:wrap">@csrf
<select name="type" aria-label="Jenis mutasi" style="max-width:125px"><option value="in">Masuk</option><option value="out">Keluar</option><option value="adjustment">Koreksi</option></select>
<input name="quantity" aria-label="Jumlah" type="number" min="0" step="0.01" required style="max-width:110px" placeholder="Jumlah">
<input name="notes" aria-label="Catatan" maxlength="255" style="max-width:150px" placeholder="Catatan">
<button>Simpan</button></form></td></tr>
@empty<tr><td colspan="7">Belum ada data stok.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:16px">{{ $items->links() }}</div></div>
@if($pending->isNotEmpty())
<div class="card" style="margin-top:20px"><h2>Koreksi menunggu persetujuan</h2>
<div style="overflow:auto"><table><thead><tr><th>Barang</th><th>Pengaju</th><th>Jumlah usulan</th><th>Catatan</th><th>Tindakan</th></tr></thead><tbody>
@foreach($pending as $movement)<tr><td>{{ $movement->item?->name }}</td><td>{{ $movement->creator?->name }}</td><td>{{ $movement->quantity }}</td><td>{{ $movement->notes }}</td><td>
@if(auth()->user()->isOwner())<form method="POST" action="{{ route('inventory.review',$movement->id) }}" style="display:flex;gap:8px">@csrf @method('PATCH')
<button name="decision" value="approve">Setujui</button><button name="decision" value="reject" style="background:#b91c1c">Tolak</button></form>
@else<span class="muted">Menunggu Pemilik</span>@endif
</td></tr>@endforeach</tbody></table></div></div>@endif
@if($errors->any())<div class="card" style="margin-top:16px"><div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>@endif
@endsection