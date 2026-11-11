@extends('layouts.app')
@section('title','Pengaturan Profil')
@section('content')
<div class="profile-page">
 <div class="profile-intro">
  <span class="profile-avatar-large" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name,0,1)) }}</span>
  <div><h2>Akun saya</h2><p>Informasi dan keamanan akun</p></div>
 </div>
 <div class="profile-panels">
  <section class="card profile-panel" aria-labelledby="profile-info-heading">
   <div class="profile-panel-heading"><h3 id="profile-info-heading">Informasi pribadi</h3><p>Nama, username, dan email akun.</p></div>
   <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
    @csrf @method('PATCH')
    <label for="profile-name">Nama lengkap</label>
    <input id="profile-name" name="name" value="{{ old('name',$user->name) }}" required maxlength="255" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
    @error('name')<p class="error" id="profile-name-error">{{ $message }}</p>@enderror
    <label for="profile-username">Username</label>
    <input id="profile-username" name="username" value="{{ old('username',$user->username) }}" required minlength="3" maxlength="50" autocomplete="username" @error('username') aria-invalid="true" aria-describedby="profile-username-error" @enderror>
    @error('username')<p class="error" id="profile-username-error">{{ $message }}</p>@enderror
    <label for="profile-email">Email</label>
    <input id="profile-email" type="email" name="email" value="{{ old('email',$user->email) }}" required maxlength="255" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="profile-email-error" @enderror>
    @error('email')<p class="error" id="profile-email-error">{{ $message }}</p>@enderror
    <button class="btn btn-primary profile-submit" type="submit">Simpan perubahan</button>
   </form>
  </section>
  <section class="card profile-panel" aria-labelledby="profile-password-heading">
   <div class="profile-panel-heading"><h3 id="profile-password-heading">Keamanan akun</h3><p>Gunakan kata sandi baru untuk melindungi akun.</p></div>
   <form method="POST" action="{{ route('profile.password') }}" class="profile-form">
    @csrf @method('PATCH')
    <label for="profile-current-password">Kata sandi saat ini</label>
    <input id="profile-current-password" type="password" name="current_password" required autocomplete="current-password" @error('current_password') aria-invalid="true" aria-describedby="profile-current-password-error" @enderror>
    @error('current_password')<p class="error" id="profile-current-password-error">{{ $message }}</p>@enderror
    <label for="profile-new-password">Kata sandi baru</label>
    <input id="profile-new-password" type="password" name="password" required minlength="8" autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="profile-new-password-error" @enderror>
    @error('password')<p class="error" id="profile-new-password-error">{{ $message }}</p>@enderror
    <label for="profile-confirm-password">Konfirmasi kata sandi baru</label>
    <input id="profile-confirm-password" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
    <button class="btn btn-primary profile-submit" type="submit">Perbarui kata sandi</button>
   </form>
  </section>
 </div>
</div>
@endsection
