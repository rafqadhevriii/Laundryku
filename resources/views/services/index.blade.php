@extends('layouts.app')
@section('title','Layanan dan Harga')
@section('content')
<div class="module-stack">
<x-ui.section-header title="Layanan yang ditawarkan" description="Atur tarif per kilogram atau barang dan estimasi pengerjaan untuk seluruh usaha."><x-slot:actions><x-ui.button :href="route('owner.services.create')" icon="plus">Tambah Layanan</x-ui.button></x-slot:actions></x-ui.section-header>
<x-ui.workspace-context :business-wide="true"/>
<section class="card">
<p class="result-count">{{ $services->total() }} layanan · Tarif yang diperbarui berlaku untuk pesanan berikutnya.</p>
@if($services->isEmpty())<x-ui.empty-state title="Belum ada layanan laundry." description="Tambahkan layanan agar kasir dapat mencatat pesanan." icon="tag"/>
@else
<x-ui.data-table label="Daftar layanan dan harga"><thead><tr><th scope="col">Layanan</th><th scope="col">Tarif & satuan</th><th scope="col">Minimum berat</th><th scope="col">Estimasi</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead><tbody>
@foreach($services as $service)<tr><th scope="row">{{ $service->name }}</th><td class="number-cell">Rp {{ number_format($service->price,0,',','.') }}<span class="cell-subtext">{{ $service->unit==='kg'?'Per kilogram':'Per barang' }}</span></td><td>{{ $service->minimum_weight ? $service->minimum_weight.' kg' : 'Tidak diisi' }}</td><td>{{ $service->estimated_hours }} jam</td><td><x-ui.status-badge domain="active" :status="(string)(int)$service->is_active"/></td><td><x-ui.button variant="ghost" :href="route('owner.services.edit',$service->id)" :aria-label="'Ubah layanan '.$service->name">Ubah</x-ui.button></td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
{{ $services->links('components.ui.pagination') }}
</section></div>
@endsection
