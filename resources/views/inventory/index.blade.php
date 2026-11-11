@extends('layouts.app')
@section('title','Stok Bahan Laundry')
@section('content')
<div class="module-stack">


<x-ui.form-feedback/>
<div class="module-kpis">
<x-ui.kpi label="Barang terdaftar" :value="$items->total()" :context="$activeBranchLabel" icon="box"/>
<x-ui.kpi label="Stok menipis di halaman ini" :value="$items->getCollection()->filter(fn($item)=>(float)$item->quantity<=(float)$item->minimum_quantity)->count()" context="Dari barang yang tampil, bukan seluruh halaman" tone="warning" icon="alert"/>
<x-ui.kpi label="Koreksi menunggu" :value="$pending->count()" context="Menunggu persetujuan Pemilik" tone="info" icon="clock"/>
</div>
@if(auth()->user()->isOwner())
@if($activeBranchId===null)<x-ui.branch-required/>
@else
<section class="card"><x-ui.section-header title="Tambah barang stok" description="Daftarkan barang dan batas minimum. Jumlah awal tetap nol; gunakan mutasi Masuk untuk menambah persediaan."/>
<p class="context-chip">Cabang aktif: {{ $activeBranchLabel }}</p>
<form method="POST" action="{{ route('inventory.store') }}" class="module-form">@csrf
<div class="module-form-grid">
<x-ui.form-control name="name" label="Nama barang (wajib)" required maxlength="150"/>
<x-ui.select name="category" label="Kategori (wajib)" required>@foreach(['Deterjen','Pewangi dan pelembut','Pemutih dan bahan khusus','Kemasan dan hanger','Operasional lain'] as $cat)<option value="{{ $cat }}" @selected(old('category')===$cat)>{{ $cat }}</option>@endforeach</x-ui.select>
<x-ui.form-control name="unit" label="Satuan (wajib)" required maxlength="30" placeholder="Liter, kg, pcs" hint="Gunakan satuan yang sama saat mencatat mutasi."/>
<x-ui.form-control name="minimum_quantity" label="Batas minimum (wajib)" type="number" :value="0" min="0" step="0.01" required inputmode="decimal"/>
</div><div class="form-actions"><x-ui.button icon="plus">Simpan Barang</x-ui.button></div>
</form></section>
@endif
@endif
<section class="card"><x-ui.section-header title="Daftar persediaan" description="Masuk menambah stok, Keluar mengurangi stok, Koreksi menetapkan jumlah hasil hitung fisik."/>
@if(auth()->user()->isCashier())<p class="notice">Koreksi stok oleh Kasir menunggu persetujuan Pemilik. Stok belum berubah sebelum disetujui.</p>@endif
@if($items->isEmpty())<x-ui.empty-state title="Belum ada data stok." description="Barang akan muncul setelah didaftarkan oleh Pemilik pada cabang aktif." icon="box"/>
@else
<div class="stock-grid">
@foreach($items as $item)
@php($movementOld=old('_form_context')==='movement-'.$item->id)
<article class="stock-card"><div class="stock-heading"><div><h3>{{ $item->name }}</h3><p>{{ $item->branch?->name }} · {{ $item->category }}</p></div><x-ui.status-badge domain="inventory" :status="(float)$item->quantity<=(float)$item->minimum_quantity?'low':'safe'"/></div>
<dl class="stock-quantity"><div><dt>Stok tersedia</dt><dd>{{ $item->quantity }} <span>{{ $item->unit }}</span></dd></div><div><dt>Batas minimum</dt><dd>{{ $item->minimum_quantity }} <span>{{ $item->unit }}</span></dd></div></dl>
<details class="module-details" @if($movementOld && $errors->any()) open @endif><summary>Catat mutasi / koreksi<span class="sr-only"> {{ $item->name }}</span></summary>
<form method="POST" action="{{ route('inventory.move',$item->id) }}" class="module-form">@csrf
<input type="hidden" name="_form_context" value="movement-{{ $item->id }}">
<x-ui.select name="type" :id="'type-'.$item->id" :error-key="$movementOld?'type':'_inactive_type'" label="Jenis mutasi" required><option value="in" @selected($movementOld && old('type')==='in')>Masuk</option><option value="out" @selected($movementOld && old('type')==='out')>Keluar</option><option value="adjustment" @selected($movementOld && old('type')==='adjustment')>Koreksi</option></x-ui.select>
<div class="field"><label for="quantity-{{ $item->id }}">Jumlah ({{ $item->unit }}, wajib)</label><input id="quantity-{{ $item->id }}" name="quantity" type="number" min="0" step="0.01" required inputmode="decimal" value="{{ $movementOld?old('quantity'):'' }}" aria-describedby="quantity-hint-{{ $item->id }} @if($movementOld && $errors->has('quantity')) quantity-error-{{ $item->id }} @endif" @if($movementOld && $errors->has('quantity')) aria-invalid="true" @endif><p class="field-hint" id="quantity-hint-{{ $item->id }}">Masuk/keluar: jumlah perubahan. Koreksi: jumlah akhir hasil hitung fisik.</p>@if($movementOld)<x-ui.field-error name="quantity" :id="'quantity-error-'.$item->id"/>@endif</div>
<div class="field"><label for="notes-{{ $item->id }}">Catatan</label><input id="notes-{{ $item->id }}" name="notes" maxlength="255" value="{{ $movementOld?old('notes'):'' }}" @if($movementOld && $errors->has('notes')) aria-invalid="true" aria-describedby="notes-error-{{ $item->id }}" @endif>@if($movementOld)<x-ui.field-error name="notes" :id="'notes-error-'.$item->id"/>@endif</div>
<div class="form-actions"><x-ui.button :aria-label="'Simpan mutasi '.$item->name">Simpan</x-ui.button></div>
</form></details></article>
@endforeach
</div>
@endif
{{ $items->links('components.ui.pagination') }}
</section>
<section class="card"><x-ui.section-header title="Koreksi menunggu persetujuan" description="Jumlah usulan adalah stok akhir yang diajukan, bukan penambahan atau pengurangan."/>
@if($pending->isEmpty())<x-ui.empty-state title="Tidak ada koreksi menunggu." description="Pengajuan Kasir yang belum ditinjau akan muncul di sini." icon="check"/>
@else
<x-ui.data-table label="Pengajuan koreksi stok"><thead><tr><th scope="col">Barang & pengaju</th><th scope="col">Jumlah usulan</th><th scope="col">Catatan</th><th scope="col">Status & tindakan</th></tr></thead><tbody>
@foreach($pending as $movement)<tr><th scope="row">{{ $movement->item?->name }}<span class="cell-subtext">{{ $movement->creator?->name }}</span></th><td class="number-cell">{{ $movement->quantity }} {{ $movement->item?->unit }}</td><td>{{ $movement->notes??'Tanpa catatan' }}</td><td><x-ui.status-badge domain="movement" status="pending"/>
@if(auth()->user()->isOwner())<form method="POST" action="{{ route('inventory.review',$movement->id) }}" class="form-actions" data-confirm-title="Tinjau koreksi stok?" data-confirm-description="Setujui akan menetapkan stok akhir sesuai usulan. Tolak mempertahankan stok saat ini. Periksa kembali tombol keputusan yang Anda pilih.">@csrf @method('PATCH')
<x-ui.button name="decision" value="approve">Setujui</x-ui.button><x-ui.button variant="danger" name="decision" value="reject">Tolak</x-ui.button></form>@endif
</td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
</section></div>
@endsection
