@extends('layouts.app')
@section('title','Data Pelanggan')
@section('content')
<div class="card"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap"><form method="GET" action="{{ route('customers.index') }}" style="display:flex;gap:8px"><input name="search" aria-label="Cari pelanggan" placeholder="Cari nama atau nomor telepon" value="{{ request('search') }}"><button>Cari</button></form><a class="btn" href="{{ route('customers.create') }}">Tambah Pelanggan</a></div>
<div style="overflow:auto;margin-top:20px"><table><thead><tr><th>Nama</th><th>Telepon</th><th>Cabang</th><th>Alamat</th><th>Aksi</th></tr></thead><tbody>
@forelse($customers as $customer)<tr><td>{{ $customer->name }}</td><td>{{ $customer->phone??'-' }}</td><td>{{ $customer->branch?->name }}</td><td>{{ $customer->address??'-' }}</td><td><a href="{{ route('customers.edit',$customer->id) }}">Ubah</a></td></tr>
@empty<tr><td colspan="5" class="muted">Belum ada pelanggan.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:20px">{{ $customers->links() }}</div></div>
@endsection