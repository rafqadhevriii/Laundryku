@props(['name','label','type'=>'text','value'=>null,'hint'=>null,'id'=>null,'errorKey'=>null])
@php($fieldId=$id??$name)
@php($key=$errorKey??$name)
<div class="field">
<label for="{{ $fieldId }}">{{ $label }}</label>
@if($type==='textarea')
<textarea id="{{ $fieldId }}" name="{{ $name }}" @if($errors->has($key)) aria-invalid="true" @endif @if($hint || $errors->has($key)) aria-describedby="{{ $hint?$fieldId.'-hint ':'' }}{{ $errors->has($key)?$fieldId.'-error':'' }}" @endif {{ $attributes }}>{{ old($name,$value) }}</textarea>
@else
<input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" @if($type!=='password') value="{{ old($name,$value) }}" @endif @if($errors->has($key)) aria-invalid="true" @endif @if($hint || $errors->has($key)) aria-describedby="{{ $hint?$fieldId.'-hint ':'' }}{{ $errors->has($key)?$fieldId.'-error':'' }}" @endif {{ $attributes }}>
@endif
@if($hint)<p id="{{ $fieldId }}-hint" class="field-hint">{{ $hint }}</p>@endif
<x-ui.field-error :name="$key" :id="$fieldId.'-error'"/>
</div>
