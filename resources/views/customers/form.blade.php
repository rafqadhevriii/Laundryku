@extends('layouts.app')
@section('title',$customer->exists?'Ubah Pelanggan':'Tambah Pelanggan')
@section('content')
<div class="module-stack">
<x-ui.section-header title="Informasi pelanggan"/>
<x-ui.form-feedback/>
@if(!$customer->exists && $activeBranchId===null)<x-ui.branch-required/>
@else
<section class="card module-form-card">
<form method="POST" action="{{ $customer->exists?route('customers.update',$customer->id):route('customers.store') }}" class="module-form">@csrf
@if($customer->exists)@method('PUT')@endif
<x-ui.form-control name="name" label="Nama pelanggan (wajib)" :value="$customer->name" required maxlength="150" autocomplete="name"/>
<x-ui.form-control name="phone" label="Nomor WhatsApp / telepon" type="tel" :value="$customer->phone" maxlength="20" autocomplete="tel" hint="Opsional. Contoh: 081234567890."/>
<x-ui.form-control name="address" label="Alamat" type="textarea" :value="$customer->address" rows="3" maxlength="2000" autocomplete="street-address"/>
<x-ui.form-control name="notes" label="Catatan khusus" type="textarea" :value="$customer->notes" rows="3" maxlength="2000" hint="Opsional. Tuliskan kebutuhan penanganan atau preferensi pelanggan."/>
<div class="form-actions"><x-ui.button icon="check">Simpan</x-ui.button><x-ui.button variant="ghost" :href="route('customers.index')">Batal</x-ui.button></div>
</form></section>
@endif
</div>
@endsection
