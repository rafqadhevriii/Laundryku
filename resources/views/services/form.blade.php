@extends('layouts.app')
@section('title',$service->exists?'Ubah Layanan':'Tambah Layanan')
@section('content')
<div class="card" style="max-width:650px"><form method="POST" action="{{ $service->exists?route('owner.services.update',$service->id):route('owner.services.store') }}">@csrf
@if($service->exists)@method('PUT')@endif
<label for="name">Nama layanan</label><input id="name" name="name" value="{{ old('name',$service->name) }}" required placeholder="Contoh: Cuci Setrika Reguler">
<label for="unit">Satuan perhitungan</label><select id="unit" name="unit" required><option value="kg" @selected(old('unit',$service->unit)==='kg')>Per kilogram</option><option value="item" @selected(old('unit',$service->unit)==='item')>Per barang</option></select>
<label for="price">Harga per satuan (Rp)</label><input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price',$service->price) }}" required>
<label for="minimum_weight">Minimum berat (kg), opsional</label><input id="minimum_weight" name="minimum_weight" type="number" min="0.01" step="0.01" value="{{ old('minimum_weight',$service->minimum_weight) }}"><p class="muted" style="font-size:13px">Untuk layanan kilogram. Jika berat kurang dari minimum, tagihan tetap berdasarkan berat asli.</p>
<label for="estimated_hours">Estimasi pengerjaan (jam)</label><input id="estimated_hours" name="estimated_hours" type="number" min="1" value="{{ old('estimated_hours',$service->estimated_hours??48) }}" required>
<label for="description">Keterangan</label><textarea id="description" name="description" rows="3" style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px">{{ old('description',$service->description) }}</textarea>
<label for="is_active">Status layanan</label><select id="is_active" name="is_active"><option value="1" @selected(old('is_active',$service->exists?(int)$service->is_active:1)==1)>Aktif</option><option value="0" @selected(old('is_active',$service->exists?(int)$service->is_active:1)==0)>Nonaktif</option></select>
@if($errors->any())<div class="error" style="margin-top:14px">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div style="display:flex;gap:10px;margin-top:20px"><button>Simpan</button><a class="nav" href="{{ route('owner.services.index') }}">Batal</a></div></form></div>
@endsection