@extends('layouts.app')
@section('title','Masuk Laundryku')
@section('content')
<div class="card" style="max-width:440px;margin:5vh auto"><h2>Selamat datang kembali</h2><p class="muted">Masuk menggunakan akun pemilik atau kasir.</p>
<form method="POST" action="{{ route('login.store') }}">@csrf
<label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
@error('username')<p class="error">{{ $message }}</p>@enderror
<label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
@error('password')<p class="error">{{ $message }}</p>@enderror
<button style="width:100%;margin-top:24px">Masuk</button></form>
</div>
@endsection