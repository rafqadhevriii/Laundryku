@props(['name','label','type'=>'text','hint'=>null])
<div class="field">
<label for="{{ $name }}">{{ $label }}</label>
<div class="input-wrap @if($type==='password') input-password @endif">
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type!=='password') value="{{ old($name) }}" @endif
 @if($errors->has($name)) aria-invalid="true" @endif
 @if($hint || $errors->has($name)) aria-describedby="{{ $hint?$name.'-hint ':'' }}{{ $errors->has($name)?$name.'-error':'' }}" @endif {{ $attributes }}>
@if($type==='password')<button type="button" class="password-toggle icon-button" data-password-toggle="{{ $name }}" aria-label="Tampilkan {{ strtolower($label) }}" aria-pressed="false" hidden><x-ui.icon name="eye"/></button>@endif
</div>
@if($hint)<p id="{{ $name }}-hint" class="field-hint">{{ $hint }}</p>@endif
@error($name)<p id="{{ $name }}-error" class="error"><x-ui.icon name="alert"/>{{ $message }}</p>@enderror
</div>
