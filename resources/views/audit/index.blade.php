@extends('layouts.app')
@section('title','Riwayat Aktivitas')
@section('content')
<div class="card"><h2>Riwayat Aktivitas Sistem</h2><p class="muted">Aktivitas penting tercatat bersama akun pelaku dan waktu perubahan.</p>
<div style="overflow:auto"><table><thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Referensi</th><th>Detail</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td>{{ $log->created_at->format('d/m/Y H:i') }}</td><td>{{ $log->user?->name }}</td><td>{{ $log->action }}</td><td>{{ $log->subject_type }} #{{ $log->subject_id }}</td><td><small>{{ json_encode($log->details,JSON_UNESCAPED_UNICODE) }}</small></td></tr>
@empty<tr><td colspan="5">Belum ada aktivitas.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:18px">{{ $logs->links() }}</div></div>
@endsection