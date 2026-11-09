<div class="topbar-branch">
@if(auth()->user()->isOwner())
<form method="POST" action="{{ route('active-branch.update') }}" class="branch-switcher">@csrf
<input type="hidden" name="return_route" value="{{ $branchReturnRoute }}">
<label for="active-branch">Cabang aktif</label>
<select id="active-branch" name="active_branch" @if($errors->has('active_branch')) aria-invalid="true" aria-describedby="active-branch-error" @endif>
<option value="all" @selected($activeBranchId===null)>Semua Cabang · Ringkasan gabungan</option>
@foreach($switchableBranches as $branch)<option value="{{ $branch->id }}" @selected($activeBranchId===$branch->id)>{{ $branch->name }}</option>@endforeach
</select><x-ui.button class="branch-apply">Terapkan</x-ui.button>
@error('active_branch')<p class="error branch-switch-error" id="active-branch-error">{{ $message }}</p>@enderror
</form>
@else
<div class="assigned-branch"><x-ui.icon name="building"/><span><small>Cabang penugasan</small><strong>{{ $activeBranchLabel }}</strong></span></div>
@endif
</div>
