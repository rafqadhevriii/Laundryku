@extends('layouts.app')
@section('title','Pelanggan')
@section('page-actions')
@if(auth()->user()->isOwner() && $activeBranchId===null)
<x-ui.button type="button" icon="plus" class="studio-page-primary" data-open-branch data-create-route="customers.create">Tambah pelanggan</x-ui.button>
@else
<x-ui.button :href="route('customers.create')" icon="plus" class="studio-page-primary">Tambah pelanggan</x-ui.button>
@endif
@endsection
@section('content')
<div class="module-stack studio-customers">
 <section class="card studio-customer-panel">
  <form method="GET" action="{{ route('customers.index') }}" class="module-search">
   <x-ui.form-control name="search" label="Cari pelanggan" :value="request('search')" placeholder="Nama atau nomor telepon"/>
   <div class="form-actions"><x-ui.button icon="search">Cari</x-ui.button>@if(request()->filled('search'))<x-ui.button variant="ghost" :href="route('customers.index')">Reset</x-ui.button>@endif</div>
  </form>
  @if($customers->isEmpty())
   <x-ui.empty-state title="Belum ada pelanggan." description="Mulai dengan menambahkan pelanggan." icon="users"/>
  @else
  <div class="studio-customer-table">
  <x-ui.data-table label="Daftar pelanggan"><thead><tr><th scope="col">Nama & kontak</th><th scope="col">Alamat</th><th scope="col">Aksi</th></tr></thead><tbody>
   @foreach($customers as $customer)
   <tr><th scope="row"><strong>{{ $customer->name }}</strong><span class="cell-subtext">{{ $customer->phone??'Telepon belum diisi' }}</span></th><td>{{ $customer->address??'Alamat belum diisi' }}</td><td><x-ui.button variant="ghost" :href="route('customers.edit',$customer->id)" :aria-label="'Ubah pelanggan '.$customer->name">Ubah</x-ui.button></td></tr>
   @endforeach
  </tbody></x-ui.data-table>
  </div>
  <div class="studio-customer-cards" aria-label="Daftar pelanggan">
   @foreach($customers as $customer)
   <article class="studio-customer-card">
    <div class="studio-customer-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($customer->name,0,1)) }}</div>
    <div class="studio-customer-info"><strong>{{ $customer->name }}</strong><span>{{ $customer->phone??'Telepon belum diisi' }}</span>
    @if($customer->address)<small>{{ $customer->address }}</small>@endif
    </div>
    <a class="studio-customer-edit" href="{{ route('customers.edit',$customer->id) }}" aria-label="Ubah pelanggan {{ $customer->name }}"><x-ui.icon name="arrow"/></a>
   </article>
   @endforeach
  </div>
  @endif
  {{ $customers->links('components.ui.pagination') }}
 </section>
</div>
@endsection
