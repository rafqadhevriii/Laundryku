@extends('layouts.guest')
@section('title','Siapkan Usaha Laundry')
@section('content')
<p class="eyebrow">Langkah pertama</p><h1>Mulai kelola usaha Anda</h1><p class="auth-intro">Siapkan identitas usaha dan akun Pemilik pertama. Semua kolom wajib diisi.</p>
<div class="setup-note"><x-ui.icon name="building"/><span>Penyiapan ini hanya tersedia sebelum ada pengguna.</span></div>
@if($errors->any())<x-ui.alert type="error"><strong>Periksa kembali data Anda.</strong> Perbaiki isian yang ditandai sebelum melanjutkan.</x-ui.alert>@endif
<form method="POST" action="{{ route('setup.store') }}" data-pending-form>@csrf
<x-ui.field name="business_name" label="Nama usaha" required maxlength="150" autocomplete="organization"/>
<x-ui.field name="owner_name" label="Nama pemilik" required maxlength="255" autocomplete="name"/>
<div class="form-row"><x-ui.field name="username" label="Username" required maxlength="50" autocomplete="username" autocapitalize="none" spellcheck="false"/><x-ui.field name="email" label="Email" type="email" required maxlength="255" autocomplete="email"/></div>
<x-ui.field name="password" label="Password" type="password" required minlength="8" autocomplete="new-password" hint="Gunakan minimal 8 karakter."/>
<x-ui.field name="password_confirmation" label="Ulangi password" type="password" required minlength="8" autocomplete="new-password"/>
<x-ui.button class="auth-submit" icon="arrow" data-pending-label="Menyiapkan usaha…">Buat Usaha dan Akun Pemilik</x-ui.button>
</form>
@endsection
