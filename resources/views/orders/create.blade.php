@extends('layouts.app')
@section('title','Pesanan Baru')
@section('content')
<div class="operation-intro"><div><p class="intro-greeting">Catat pesanan dengan jelas.</p><p class="muted">Pilih pelanggan, layanan, lalu masukkan berat atau jumlah barang.</p></div><x-ui.button :href="route('orders.index')" variant="ghost">Kembali ke pesanan</x-ui.button></div>
<x-ui.form-feedback/>
@if($customers->isEmpty() || $services->isEmpty())<x-ui.alert type="error">Pastikan sudah ada pelanggan dan layanan aktif sebelum membuat pesanan. <a href="{{ route('customers.create') }}">Tambah pelanggan</a>@if(auth()->user()->isOwner()) · <a href="{{ route('owner.services.create') }}">Tambah layanan</a>@endif</x-ui.alert>@endif
<form method="POST" action="{{ route('orders.store') }}" data-order-form>@csrf
<div class="order-composer"><div class="composer-fields">
<section class="card"><x-ui.section-header title="1. Pelanggan & cabang" description="Pelanggan harus berasal dari cabang yang sama."/>
@if(auth()->user()->isOwner())<x-ui.select name="branch_id" label="Cabang" required data-order-branch><option value="">Pilih cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</x-ui.select>
@else<p class="context-chip"><x-ui.icon name="building"/>{{ auth()->user()->branch?->name }}</p>@endif
<x-ui.select name="customer_id" label="Pelanggan" required data-order-customer><option value="">Pilih pelanggan</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" data-branch="{{ $customer->branch_id }}" @selected(old('customer_id')==$customer->id)>{{ $customer->name }} ({{ $customer->phone??'tanpa telepon' }})</option>@endforeach</x-ui.select>
<p class="field-hint">Belum terdaftar? <a href="{{ route('customers.create') }}">Tambah pelanggan</a> sebelum melanjutkan.</p>
</section>
<section class="card section-spaced"><x-ui.section-header title="2. Layanan & jumlah" description="Gunakan satu baris untuk setiap layanan berbeda. Maksimal 20 layanan."/>
@php($oldItems=old('items',[['service_id'=>'','quantity'=>'1']]))
<div id="items" data-order-items>@foreach(is_array($oldItems)?$oldItems:[['service_id'=>'','quantity'=>'1']] as $i=>$item)<x-orders.service-row :services="$services" :index="$i" :item="is_array($item)?$item:[]"/>@endforeach</div>
<template data-order-template><x-orders.service-row :services="$services" index="__INDEX__"/></template>
<x-ui.button type="button" variant="ghost" data-order-add hidden>+ Tambah Layanan</x-ui.button><p class="field-hint">Layanan per barang memakai jumlah bulat; layanan kg menerima hingga dua angka desimal.</p><p class="form-live" data-order-announcement role="status" aria-live="polite"></p><noscript><p class="field-hint">Aktifkan JavaScript lokal untuk menambah/menghapus baris dan melihat ringkasan pilihan. Baris yang tersedia tetap dapat dikirim.</p></noscript>
<x-ui.field-error name="items"/>
</section>
<section class="card section-spaced"><x-ui.section-header title="3. Catatan tambahan" description="Opsional, misalnya instruksi khusus dari pelanggan."/><div class="field"><label for="notes">Catatan pesanan</label><textarea id="notes" name="notes" rows="3" maxlength="2000" @if($errors->has('notes')) aria-invalid="true" aria-describedby="notes-error" @endif>{{ old('notes') }}</textarea><x-ui.field-error name="notes"/></div></section>
</div>
<aside class="card composer-summary" aria-labelledby="summary-title"><p class="eyebrow">Periksa sebelum menyimpan</p><h2 id="summary-title">Ringkasan pesanan</h2><p data-summary-customer class="summary-customer">Pilih pelanggan untuk pesanan ini.</p><ul class="summary-lines" data-summary-lines><li>Tarif tersedia pada pilihan layanan di samping.</li></ul><div class="summary-estimate"><x-ui.icon name="clock"/><p data-summary-duration>Estimasi mengikuti durasi layanan yang dipilih.</p></div><div class="summary-total-note"><strong>Total resmi setelah disimpan</strong><p>Total dihitung dari jumlah × tarif setiap layanan setelah pesanan disimpan. Harga mengikuti tarif saat pemesanan; estimasi selesai mengikuti layanan terlama.</p></div><x-ui.button class="full-width" icon="check">Simpan Pesanan</x-ui.button><x-ui.button :href="route('orders.index')" variant="ghost" class="full-width">Batal</x-ui.button></aside>
</div></form>
@endsection
