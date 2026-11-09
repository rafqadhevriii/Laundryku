@extends('layouts.app')
@section('title','Layanan dan Harga')
@section('content')
<div class="card"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px"><p class="muted">Kelola jenis layanan dan tarif laundry.</p><a class="btn" href="{{ route('owner.services.create') }}">Tambah Layanan</a></div>
<div style="overflow:auto"><table><thead><tr><th>Nama layanan</th><th>Satuan</th><th>Harga</th><th>Minimum</th><th>Estimasi</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($services as $service)<tr><td>{{ $service->name }}</td><td>{{ $service->unit==='kg'?'Kilogram':'Barang' }}</td><td>Rp {{ number_format($service->price,0,',','.') }}</td><td>{{ $service->minimum_weight ? $service->minimum_weight.' kg' : '-' }}</td><td>{{ $service->estimated_hours }} jam</td><td>{{ $service->is_active?'Aktif':'Nonaktif' }}</td><td><a href="{{ route('owner.services.edit',$service->id) }}">Ubah</a></td></tr>
@empty<tr><td colspan="7" class="muted">Belum ada layanan laundry.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:20px">{{ $services->links() }}</div></div>
@endsection