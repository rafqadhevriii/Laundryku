@extends('layouts.app')
@section('title',$customer->exists?'Ubah Pelanggan':'Tambah Pelanggan')
@section('content')
@if(!$customer->exists && $activeBranchId===null)
<x-ui.branch-required/>
@else
<div class="card" style="max-width:650px"><form method="POST" action="{{ $customer->exists?route('customers.update',$customer->id):route('customers.store') }}">@csrf
@if($customer->exists)@method('PUT')@endif
<label for="name">Nama pelanggan</label><input id="name" name="name" required value="{{ old('name',$customer->name) }}">
<label for="phone">Nomor WhatsApp / telepon</label><input id="phone" name="phone" value="{{ old('phone',$customer->phone) }}" placeholder="Contoh: 081234567890">
<p class="context-chip"><x-ui.icon name="building"/>Cabang: {{ $customer->exists?$customer->branch?->name:$activeBranchLabel }}</p>
<label for="address">Alamat</label><input id="address" name="address" value="{{ old('address',$customer->address) }}">
<label for="notes">Catatan khusus</label><textarea id="notes" name="notes" rows="3" style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px">{{ old('notes',$customer->notes) }}</textarea>
@if($errors->any())<div class="error" style="margin-top:14px">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div style="display:flex;gap:10px;margin-top:20px"><button>Simpan</button><a href="{{ route('customers.index') }}" class="nav">Batal</a></div></form></div>
@endif
@endsection