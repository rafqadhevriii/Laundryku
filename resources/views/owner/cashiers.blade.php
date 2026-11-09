@extends('layouts.app')
@section('title','Kelola Akun Kasir')
@section('content')
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(310px,1fr))">
<div class="card"><h2>Tambah kasir</h2><form method="POST" action="{{ route('owner.cashiers.store') }}">@csrf
<label for="name">Nama</label><input id="name" name="name" value="{{ old('name') }}" required>
<label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" required>
<label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required>
<label for="branch_id">Cabang</label><select id="branch_id" name="branch_id" required><option value="">Pilih cabang</option>
@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</select>
<label for="password">Password</label><input id="password" name="password" type="password" required minlength="8">
<label for="password_confirmation">Ulangi password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="8">
@if($errors->any())<div class="error" style="margin-top:12px">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<button style="margin-top:20px">Simpan Kasir</button></form></div>
<div class="card"><h2>Daftar kasir</h2>
@if($cashiers->isEmpty())<p class="muted">Belum ada akun kasir.</p>
@else<div style="overflow:auto"><table><thead><tr><th>Nama</th><th>Username</th><th>Cabang</th></tr></thead>
<tbody>@foreach($cashiers as $cashier)<tr><td>{{ $cashier->name }}</td><td>{{ $cashier->username }}</td><td>{{ $cashier->branch?->name }}</td></tr>@endforeach</tbody></table></div>@endif
</div></div>
@endsection