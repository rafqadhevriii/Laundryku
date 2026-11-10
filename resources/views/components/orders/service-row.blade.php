@props(['services','index','item'=>[]])
@php($serviceKey='items.'.$index.'.service_id')
@php($quantityKey='items.'.$index.'.quantity')
<fieldset class="order-line" data-order-line data-index="{{ $index }}"><legend>Layanan <span data-row-number>{{ is_numeric($index)?$index+1:'' }}</span></legend>
<div class="service-row-grid">
<x-ui.select :name="'items['.$index.'][service_id]'" :id="'service-'.$index" label="Layanan" :error-key="$serviceKey" data-service-select required>
<option value="">Pilih layanan</option>@foreach($services as $service)<option value="{{ $service->id }}" data-label="{{ $service->name }}" data-price-label="Rp {{ number_format($service->price,2,',','.') }}" data-unit="{{ $service->unit }}" data-minimum="{{ $service->unit==='kg'?$service->minimum_weight:'' }}" data-hours="{{ $service->estimated_hours }}" @selected(($item['service_id']??'')==$service->id)>{{ $service->name }} · Rp {{ number_format($service->price,2,',','.') }}/{{ $service->unit }}@if($service->unit==='kg' && $service->minimum_weight) · Minimum {{ number_format($service->minimum_weight,2,',','.') }} kg (tetap berat asli)@endif</option>@endforeach
</x-ui.select>
<div class="field"><label for="quantity-{{ $index }}" data-quantity-label>Berat / jumlah</label><input id="quantity-{{ $index }}" name="items[{{ $index }}][quantity]" data-service-quantity type="number" inputmode="decimal" min="0.01" max="999999" step="0.01" required value="{{ $item['quantity']??1 }}" @if($errors->has($quantityKey)) aria-invalid="true" @endif aria-describedby="quantity-{{ $index }}-hint quantity-{{ $index }}-minimum {{ $errors->has($quantityKey)?'quantity-'.$index.'-error':'' }}"><p id="quantity-{{ $index }}-hint" class="field-hint" data-quantity-hint>Kg atau jumlah barang sesuai layanan.</p><x-ui.field-error :name="$quantityKey" :id="'quantity-'.$index.'-error'"/></div>
<button type="button" class="btn btn-ghost remove-service" data-remove-service aria-label="Hapus layanan baris {{ is_numeric($index)?$index+1:'' }}" hidden><x-ui.icon name="close"/><span>Hapus</span></button>
</div><p class="service-rate field-hint" data-service-info>Pilih layanan untuk melihat tarif dan durasinya.</p>
<p id="quantity-{{ $index }}-minimum" class="notice" data-minimum-info role="status" aria-live="polite" hidden></p>
</fieldset>
