@extends('layouts.guest')
@section('title','Masuk Laundryku')
@section('content')
<p class="coachpro-login-kicker">SELAMAT DATANG</p>
<h1>Masuk ke Laundryku</h1>
<p class="auth-intro">Gunakan akun Anda untuk melanjutkan.</p>
@if($errors->any())<x-ui.alert type="error"><strong>Belum berhasil masuk.</strong> Periksa username dan password.</x-ui.alert>@endif
<form method="POST" action="{{ route('login.store') }}" data-pending-form>
 @csrf
 <x-ui.field name="username" label="Username" required autofocus autocomplete="username" autocapitalize="none" spellcheck="false"/>
 <x-ui.field name="password" label="Password" type="password" required autocomplete="current-password"/>
 <x-ui.button class="auth-submit" icon="arrow" data-pending-label="Sedang masuk...">Masuk</x-ui.button>
</form>
@endsection