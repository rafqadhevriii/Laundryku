@extends('layouts.app')
@section('title','Pesanan Baru')
@section('content')
<div class="card" style="max-width:780px">
@if($customers->isEmpty() || $services->isEmpty())<p class="error">Pastikan sudah ada pelanggan dan layanan aktif sebelum membuat pesanan.</p>@endif
<form method="POST" action="{{ route('orders.store') }}">@csrf
@if(auth()->user()->isOwner())
<label for="branch_id">Cabang</label><select id="branch_id" name="branch_id" required><option value="">Pilih cabang</option>
@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</select>
@endif
<label for="customer_id">Pelanggan</label><select id="customer_id" name="customer_id" required><option value="">Pilih pelanggan</option>
@foreach($customers as $customer)<option value="{{ $customer->id }}" data-branch="{{ $customer->branch_id }}" @selected(old('customer_id')==$customer->id)>{{ $customer->name }} ({{ $customer->phone??'tanpa telepon' }})</option>@endforeach</select>
<p class="muted" style="font-size:13px">Pelanggan harus berasal dari cabang yang sama.</p>
<h3>Layanan pesanan</h3>
<div id="items">
@foreach(old('items',[['service_id'=>'','quantity'=>'1']]) as $i=>$item)
<div class="item-row" style="display:grid;grid-template-columns:2fr 1fr auto;gap:10px;align-items:end;margin-bottom:12px">
<div><label>Layanan</label><select name="items[{{ $i }}][service_id]" required><option value="">Pilih layanan</option>
@foreach($services as $service)<option value="{{ $service->id }}" @selected(($item['service_id']??'')==$service->id)>{{ $service->name }} | Rp {{ number_format($service->price,0,',','.') }}/{{ $service->unit }}</option>@endforeach</select></div>
<div><label>Berat / jumlah</label><input name="items[{{ $i }}][quantity]" type="number" min="0.01" step="0.01" required value="{{ $item['quantity']??1 }}"></div>
<button type="button" class="remove-item" style="background:#64748b">Hapus</button></div>
@endforeach
</div>
<button type="button" id="add-item" style="background:#0f766e;margin-top:8px">Tambah Layanan</button>
<label for="notes">Catatan pesanan</label><textarea id="notes" name="notes" rows="3" style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px">{{ old('notes') }}</textarea>
@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div style="display:flex;gap:10px;margin-top:20px"><button type="submit">Simpan Pesanan</button><a class="nav" href="{{ route('orders.index') }}">Batal</a></div>
</form></div>
<script>
const items=document.getElementById('items');let nextIndex=items.querySelectorAll('.item-row').length;
document.getElementById('add-item').addEventListener('click',()=>{
 if(items.querySelectorAll('.item-row').length>=20)return;
 const row=items.querySelector('.item-row').cloneNode(true);
 row.querySelector('select').name='items['+nextIndex+'][service_id]';
 row.querySelector('input').name='items['+nextIndex+'][quantity]';
 row.querySelector('select').value='';row.querySelector('input').value='1';nextIndex++;items.append(row);
});
items.addEventListener('click',e=>{if(e.target.classList.contains('remove-item')&&items.querySelectorAll('.item-row').length>1)e.target.closest('.item-row').remove();});
const branch=document.getElementById('branch_id'),customer=document.getElementById('customer_id');
if(branch){const sync=()=>{for(const option of customer.options){if(!option.value)continue;option.hidden=option.dataset.branch!==branch.value;}if(customer.selectedOptions[0]?.hidden)customer.value='';};branch.addEventListener('change',sync);sync();}
</script>
@endsection