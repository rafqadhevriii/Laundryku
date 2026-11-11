@extends('layouts.app')
@section('title',$service->exists?'Ubah Layanan':'Tambah Layanan')
@section('content')
<div class="module-stack">
<x-ui.section-header title="Rincian layanan & tarif"/>

<x-ui.form-feedback/>
<section class="card module-form-card"><form method="POST" action="{{ $service->exists?route('owner.services.update',$service->id):route('owner.services.store') }}" class="module-form">@csrf
@if($service->exists)@method('PUT')@endif
<x-ui.form-control name="name" label="Nama layanan (wajib)" :value="$service->name" required maxlength="150" placeholder="Contoh: Cuci Setrika Reguler"/>
<div class="module-form-grid">
<x-ui.select name="unit" label="Satuan perhitungan (wajib)" required><option value="kg" @selected(old('unit',$service->unit)==='kg')>Per kilogram</option><option value="item" @selected(old('unit',$service->unit)==='item')>Per barang</option></x-ui.select>
<x-ui.form-control name="price" label="Harga per satuan (Rp, wajib)" type="number" :value="$service->price" min="0" step="0.01" required inputmode="decimal"/>
<x-ui.form-control name="minimum_weight" label="Minimum berat (kg, opsional)" type="number" :value="$service->minimum_weight" min="0.01" step="0.01" hint="Untuk layanan kilogram. Jika berat kurang dari minimum, tagihan tetap berdasarkan berat asli."/>
<x-ui.form-control name="estimated_hours" label="Estimasi pengerjaan (jam, wajib)" type="number" :value="$service->estimated_hours??48" min="1" required/>
</div>
<x-ui.form-control name="description" label="Keterangan" type="textarea" :value="$service->description" rows="3" maxlength="2000"/>
<x-ui.select name="is_active" label="Status layanan" hint="Layanan nonaktif tidak tersedia untuk pesanan baru."><option value="1" @selected(old('is_active',$service->exists?(int)$service->is_active:1)==1)>Aktif</option><option value="0" @selected(old('is_active',$service->exists?(int)$service->is_active:1)==0)>Nonaktif</option></x-ui.select>
<div class="form-actions"><x-ui.button icon="check">Simpan</x-ui.button><x-ui.button variant="ghost" :href="route('owner.services.index')">Batal</x-ui.button></div>
</form></section></div>
@endsection
