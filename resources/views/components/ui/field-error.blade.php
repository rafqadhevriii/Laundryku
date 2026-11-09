@props(['name','id'=>null])
@error($name)<p id="{{ $id??$name.'-error' }}" class="error"><x-ui.icon name="alert"/>{{ $message }}</p>@enderror
