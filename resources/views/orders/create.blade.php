@extends('layouts.app')
@section('title','Pesanan Baru')
@section('content')
<div class="operation-intro composer-intro"><div><p class="intro-greeting">Pesanan baru</p><p class="muted">Pelanggan / Layanan / Simpan</p></div><x-ui.button :href="route('orders.index')" variant="ghost">Kembali</x-ui.button></div>
<x-ui.form-feedback/>
@if($activeBranchId===null)
<x-ui.branch-required/>
@else
@if($customers->isEmpty() || $services->isEmpty())<x-ui.alert type="error">Pastikan sudah ada pelanggan dan layanan aktif sebelum membuat pesanan. <a href="{{ route('customers.create') }}">Tambah pelanggan</a>@if(auth()->user()->isOwner()) · <a href="{{ route('owner.services.create') }}">Tambah layanan</a>@endif</x-ui.alert>@endif
<form method="POST" action="{{ route('orders.store') }}" data-order-form>@csrf
<div class="order-composer"><div class="composer-fields">
<section class="card"><x-ui.section-header title="1. Pelanggan"/>

<x-ui.select name="customer_id" label="Pelanggan" required data-order-customer><option value="">Pilih pelanggan</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" data-branch="{{ $customer->branch_id }}" @selected(old('customer_id')==$customer->id)>{{ $customer->name }} ({{ $customer->phone??'tanpa telepon' }})</option>@endforeach</x-ui.select>
<p class="field-hint">Belum terdaftar? <a href="{{ route('customers.create') }}">Tambah pelanggan</a> sebelum melanjutkan.</p>
</section>
<section class="card section-spaced"><x-ui.section-header title="2. Layanan & jumlah"/>
@php($oldItems=old('items',[['service_id'=>'','quantity'=>'1']]))
<div id="items" data-order-items>@foreach(is_array($oldItems)?$oldItems:[['service_id'=>'','quantity'=>'1']] as $i=>$item)<x-orders.service-row :services="$services" :index="$i" :item="is_array($item)?$item:[]"/>@endforeach</div>
<template data-order-template><x-orders.service-row :services="$services" index="__INDEX__"/></template>
<x-ui.button type="button" variant="ghost" data-order-add hidden>+ Tambah Layanan</x-ui.button><details class="module-details composer-help"><summary>Aturan berat dan jumlah</summary><p class="field-hint">Layanan per barang memakai jumlah bulat, sedangkan layanan kilogram dapat menggunakan dua angka desimal. berat di bawah minimum tetap boleh dan ditagih sesuai berat asli. Maksimal 20 layanan per pesanan.</p></details><p class="form-live" data-order-announcement role="status" aria-live="polite"></p><noscript><p class="field-hint">Aktifkan JavaScript lokal untuk menambah/menghapus baris dan melihat ringkasan pilihan. Baris yang tersedia tetap dapat dikirim.</p></noscript>
<x-ui.field-error name="items"/>
</section>
<section class="card section-spaced"><x-ui.section-header title="3. Catatan tambahan" /><div class="field"><label for="notes">Catatan pesanan</label><textarea id="notes" name="notes" rows="3" maxlength="2000" @if($errors->has('notes')) aria-invalid="true" aria-describedby="notes-error" @endif>{{ old('notes') }}</textarea><x-ui.field-error name="notes"/></div></section>
</div>
<aside class="card composer-summary" aria-labelledby="summary-title"><h2 id="summary-title">Ringkasan pesanan</h2><p data-summary-customer class="summary-customer">Pilih pelanggan untuk pesanan ini.</p><ul class="summary-lines" data-summary-lines><li>Tarif tersedia pada pilihan layanan di samping.</li></ul><div class="summary-estimate"><x-ui.icon name="clock"/><p data-summary-duration>Estimasi mengikuti durasi layanan yang dipilih.</p></div><div class="summary-total-note"><strong>Tagihan dihitung saat disimpan</strong><p>Harga layanan dan estimasi selesai mengikuti data saat pesanan dibuat.</p></div><x-ui.button class="full-width" icon="check">Simpan Pesanan</x-ui.button><x-ui.button :href="route('orders.index')" variant="ghost" class="full-width">Batal</x-ui.button></aside>
</div></form>
@endif
@endsection
