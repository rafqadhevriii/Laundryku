@extends('layouts.app')
@section('title','Pengeluaran Operasional')
@section('content')
<div class="module-stack">


<x-ui.form-feedback/>
<div class="module-split">
@if($activeBranchId===null)<x-ui.branch-required/>
@else
<section class="card"><h2>Catat pengeluaran</h2>
<form method="POST" action="{{ route('expenses.store') }}" class="module-form">@csrf
<x-ui.select name="category" label="Kategori (wajib)" required>@foreach(['Listrik dan air','Deterjen dan bahan pencuci','Perawatan mesin','Sewa tempat','Operasional lain'] as $category)<option value="{{ $category }}" @selected(old('category')===$category)>{{ $category }}</option>@endforeach</x-ui.select>
<x-ui.form-control name="description" label="Keterangan (wajib)" maxlength="255" required hint="Contoh: pembelian kemasan atau tagihan listrik."/>
<x-ui.form-control name="amount" label="Nominal (Rp, wajib)" type="number" step="0.01" min="0.01" required inputmode="decimal"/>
<x-ui.form-control name="expense_date" label="Tanggal (wajib)" type="date" :value="now()->format('Y-m-d')" required/>
<div class="form-actions"><x-ui.button icon="check">Simpan Pengeluaran</x-ui.button></div>
</form></section>
@endif
<section class="card"><x-ui.section-header title="Riwayat pengeluaran"/>
@if($expenses->isEmpty())<x-ui.empty-state title="Belum ada pengeluaran." description="Pengeluaran yang disimpan akan tampil di sini." icon="wallet"/>
@else
<x-ui.data-table label="Daftar pengeluaran operasional"><thead><tr><th scope="col">Tanggal & cabang</th><th scope="col">Kategori & keterangan</th><th scope="col" class="number-cell">Nominal</th></tr></thead><tbody>
@foreach($expenses as $expense)<tr><td>{{ $expense->expense_date->format('d/m/Y') }}<span class="cell-subtext">{{ $expense->branch?->name }}</span></td><th scope="row">{{ $expense->category }}<span class="cell-subtext">{{ $expense->description }}</span></th><td class="number-cell">Rp {{ number_format($expense->amount,0,',','.') }}</td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
{{ $expenses->links('components.ui.pagination') }}
</section></div></div>
@endsection
