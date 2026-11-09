@props(['type' => 'success'])
<div {{ $attributes->class(['feedback','feedback-'.$type]) }} role="{{ $type==='error'?'alert':'status' }}" @if($type!=='error') aria-live="polite" @endif><x-ui.icon :name="$type==='error'?'alert':'check'"/><div>{{ $slot }}</div></div>
