@extends('layouts.app')
@section('title','Data Pelanggan')
@section('content')
<div class="module-stack">
<x-ui.section-header title="Kenali pelanggan Anda" description="Cari kontak pelanggan dan perbarui informasi agar pencatatan pesanan lebih cepat."><x-slot:actions><x-ui.button :href="route('customers.create')" icon="plus">Tambah Pelanggan</x-ui.button></x-slot:actions></x-ui.section-header>
<x-ui.workspace-context/>
<section class="card">
<form method="GET" action="{{ route('customers.index') }}" class="module-search">
<x-ui.form-control name="search" label="Cari pelanggan" :value="request('search')" placeholder="Nama atau nomor telepon"/>
<div class="form-actions"><x-ui.button icon="search">Cari</x-ui.button>@if(request()->filled('search'))<x-ui.button variant="ghost" :href="route('customers.index')">Reset</x-ui.button>@endif</div>
</form>
<p class="result-count">{{ $customers->total() }} pelanggan sesuai pencarian · {{ $activeBranchLabel }}</p>
@if($customers->isEmpty())<x-ui.empty-state title="Belum ada pelanggan." description="Coba kata pencarian lain atau tambahkan pelanggan pada cabang aktif." icon="users"/>
@else
<x-ui.data-table label="Daftar pelanggan"><thead><tr><th scope="col">Nama & kontak</th><th scope="col">Cabang</th><th scope="col">Alamat</th><th scope="col">Aksi</th></tr></thead><tbody>
@foreach($customers as $customer)<tr><th scope="row"><strong>{{ $customer->name }}</strong><span class="cell-subtext">{{ $customer->phone??'Telepon belum diisi' }}</span></th><td>{{ $customer->branch?->name }}</td><td>{{ $customer->address??'Alamat belum diisi' }}</td><td><x-ui.button variant="ghost" :href="route('customers.edit',$customer->id)" :aria-label="'Ubah pelanggan '.$customer->name">Ubah</x-ui.button></td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
{{ $customers->links('components.ui.pagination') }}
</section></div>
@endsection
