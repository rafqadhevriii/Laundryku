@extends('layouts.app')
@section('title','Siapkan Usaha Laundry')
@section('content')
<div class="card" style="max-width:520px;margin:0 auto"><h2>Daftarkan pemilik pertama</h2>
<p class="muted">Langkah awal ini hanya tersedia sebelum ada pengguna.</p>
<form method="POST" action="{{ route('setup.store') }}">@csrf
<label for="business_name">Nama usaha</label><input id="business_name" name="business_name" value="{{ old('business_name') }}" required>
@error('business_name')<p class="error">{{ $message }}</p>@enderror
<label for="owner_name">Nama pemilik</label><input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
@error('owner_name')<p class="error">{{ $message }}</p>@enderror
<label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" required>
@error('username')<p class="error">{{ $message }}</p>@enderror
<label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required>
@error('email')<p class="error">{{ $message }}</p>@enderror
<label for="password">Password</label><input id="password" name="password" type="password" required minlength="8">
@error('password')<p class="error">{{ $message }}</p>@enderror
<label for="password_confirmation">Ulangi password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="8">
<button style="width:100%;margin-top:24px">Buat Usaha dan Akun Pemilik</button></form></div>
@endsection