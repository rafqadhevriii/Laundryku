@props(['status'=>null,'domain'=>'order'])
@php
$maps=[
 'order'=>['received'=>['Diterima','info'],'processing'=>['Diproses','warning'],'ready'=>['Siap diambil','success'],'picked_up'=>['Sudah diambil','neutral'],'cancelled'=>['Dibatalkan','danger']],
 'payment'=>['unpaid'=>['Belum dibayar','neutral'],'pending'=>['Menunggu konfirmasi','warning'],'paid'=>['Lunas','success'],'rejected'=>['Ditolak','danger']],
 'shift'=>['open'=>['Berjalan','info'],'submitted'=>['Menunggu tinjauan','warning'],'reviewed'=>['Ditinjau','success']],
];
[$label,$tone]=$maps[$domain][$status??($domain==='payment'?'unpaid':'')]??[$status??'Belum tersedia','neutral'];
@endphp
<span {{ $attributes->class(['status-badge','status-'.$tone]) }}><span class="status-dot" aria-hidden="true"></span>{{ $label }}</span>
