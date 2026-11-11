@extends('layouts.app')
@section('title','Riwayat Aktivitas')
@section('content')
<div class="module-stack">


<section class="card"><x-ui.section-header title="Riwayat aktivitas"/>
@if($logs->isEmpty())<x-ui.empty-state title="Belum ada aktivitas." description="Tindakan penting akan muncul setelah dilakukan oleh pengguna." icon="clock"/>
@else
<x-ui.data-table label="Riwayat aktivitas sistem"><thead><tr><th scope="col">Waktu & pengguna</th><th scope="col">Aktivitas</th><th scope="col">Referensi</th><th scope="col">Detail tercatat</th></tr></thead><tbody>
@foreach($logs as $log)<tr><td>{{ $log->created_at->format('d/m/Y H:i') }}<span class="cell-subtext">{{ $log->user?->name??'Pengguna tidak tersedia' }}</span></td><th scope="row"><code>{{ $log->action }}</code></th><td>{{ $log->subject_type }} #{{ $log->subject_id }}</td><td><details class="module-details"><summary>Lihat detail<span class="sr-only"> aktivitas #{{ $log->id }}</span></summary><pre class="audit-payload">{{ json_encode($log->details,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre></details></td></tr>@endforeach
</tbody></x-ui.data-table>
@endif
{{ $logs->links('components.ui.pagination') }}
</section></div>
@endsection
