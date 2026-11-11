@extends('layouts.app')
@section('title','Kelola Akun Kasir')
@section('content')
<div class="module-stack">


<x-ui.form-feedback/>
<div class="module-split">
<section class="card"><h2>Tambah kasir</h2><form method="POST" action="{{ route('owner.cashiers.store') }}" class="module-form">@csrf
<x-ui.form-control name="name" label="Nama (wajib)" required maxlength="255" autocomplete="name"/>
<x-ui.form-control name="username" label="Username (wajib)" required minlength="3" maxlength="50" autocomplete="off" autocapitalize="none" spellcheck="false" hint="3–50 karakter, gunakan huruf, angka, tanda hubung atau garis bawah. Harus unik."/>
<x-ui.form-control name="email" label="Email (wajib)" type="email" required maxlength="255" autocomplete="email" hint="Gunakan email yang belum dipakai akun lain."/>
<x-ui.select name="branch_id" label="Cabang penugasan (wajib)" required hint="Penugasan akun; tidak mengikuti pilihan cabang kerja Pemilik."><option value="">Pilih cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</x-ui.select>
<x-ui.field name="password" label="Password (wajib)" type="password" required minlength="8" autocomplete="new-password" hint="Minimal 8 karakter. Password tidak ditampilkan kembali saat validasi gagal."/>
<x-ui.field name="password_confirmation" label="Ulangi password (wajib)" type="password" required minlength="8" autocomplete="new-password"/>
<div class="form-actions"><x-ui.button icon="plus">Simpan Kasir</x-ui.button></div>
</form></section>
<section class="card"><x-ui.section-header title="Daftar kasir" :description="$cashiers->count().' akun kasir dalam usaha'"/>
@if($cashiers->isEmpty())<x-ui.empty-state title="Belum ada akun kasir." description="Buat akun dan pilih cabang penugasannya." icon="users"/>
@else
<x-ui.data-table label="Daftar akun kasir"><thead><tr><th scope="col">Nama & username</th><th scope="col">Cabang</th><th scope="col">Status</th></tr></thead><tbody>
@foreach($cashiers as $cashier)<tr><th scope="row">{{ $cashier->name }}<span class="cell-subtext">{{ $cashier->username }}</span></th><td>{{ $cashier->branch?->name }}</td><td><x-ui.status-badge domain="active" :status="(string)(int)$cashier->is_active"/></td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
</section></div></div>
@endsection
