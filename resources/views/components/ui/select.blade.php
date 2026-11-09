@props(['name','label','id'=>null,'hint'=>null,'errorKey'=>null])
@php($fieldId=$id??$name)
@php($key=$errorKey??$name)
<div class="field"><label for="{{ $fieldId }}">{{ $label }}</label><select id="{{ $fieldId }}" name="{{ $name }}" @if($errors->has($key)) aria-invalid="true" @endif @if($hint || $errors->has($key)) aria-describedby="{{ $hint?$fieldId.'-hint ':'' }}{{ $errors->has($key)?$fieldId.'-error':'' }}" @endif {{ $attributes }}>{{ $slot }}</select>
@if($hint)<p id="{{ $fieldId }}-hint" class="field-hint">{{ $hint }}</p>@endif<x-ui.field-error :name="$key" :id="$fieldId.'-error'"/></div>
