@extends('layouts.app')
@section('title','Pengeluaran Operasional')
@section('content')
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr))">
<div class="card"><h2>Catat pengeluaran</h2><form method="POST" action="{{ route('expenses.store') }}">@csrf
@if(auth()->user()->isOwner())<label for="branch_id">Cabang</label><select id="branch_id" name="branch_id" required><option value="">Pilih cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</select>@endif
<label for="category">Kategori</label><select id="category" name="category" required>@foreach(['Listrik dan air','Deterjen dan bahan pencuci','Perawatan mesin','Sewa tempat','Operasional lain'] as $category)<option value="{{ $category }}" @selected(old('category')===$category)>{{ $category }}</option>@endforeach</select>
<label for="description">Keterangan</label><input id="description" name="description" maxlength="255" required value="{{ old('description') }}">
<label for="amount">Nominal (Rp)</label><input id="amount" name="amount" type="number" step="0.01" min="0.01" required value="{{ old('amount') }}">
<label for="expense_date">Tanggal</label><input id="expense_date" name="expense_date" type="date" required value="{{ old('expense_date',now()->format('Y-m-d')) }}">
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<button style="margin-top:18px">Simpan Pengeluaran</button></form></div>
<div class="card"><h2>Daftar pengeluaran</h2><div style="overflow:auto"><table><thead><tr><th>Tanggal</th><th>Cabang</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th></tr></thead><tbody>
@forelse($expenses as $expense)<tr><td>{{ $expense->expense_date->format('d/m/Y') }}</td><td>{{ $expense->branch?->name }}</td><td>{{ $expense->category }}</td><td>{{ $expense->description }}</td><td>Rp {{ number_format($expense->amount,0,',','.') }}</td></tr>
@empty<tr><td colspan="5">Belum ada pengeluaran.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:15px">{{ $expenses->links() }}</div></div></div>
@endsection