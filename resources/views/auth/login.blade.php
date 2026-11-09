@extends('layouts.guest')
@section('title','Masuk Laundryku')
@section('content')
<p class="eyebrow">Selamat datang di Laundryku</p><h1>Selamat datang kembali</h1><p class="auth-intro">Masuk untuk melanjutkan aktivitas laundry Anda.</p>
@if($errors->any())<x-ui.alert type="error"><strong>Belum berhasil masuk.</strong> Periksa isian di bawah dan coba lagi.</x-ui.alert>@endif
<form method="POST" action="{{ route('login.store') }}" data-pending-form>@csrf
<x-ui.field name="username" label="Username" required autofocus autocomplete="username" autocapitalize="none" spellcheck="false"/>
<x-ui.field name="password" label="Password" type="password" required autocomplete="current-password"/>
<x-ui.button class="auth-submit" icon="arrow" data-pending-label="Sedang masuk…">Masuk</x-ui.button>
</form>
<div class="auth-help"><x-ui.icon name="badge"/><p>Gunakan akun <strong>Pemilik</strong> atau <strong>Kasir</strong> Anda. Jika memerlukan bantuan akses, hubungi Pemilik usaha.</p></div>
@endsection
