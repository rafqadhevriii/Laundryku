@props(['name' => 'grid'])
@php
$paths = [
 'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
 'bag'=>'<path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
 'users'=>'<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v3"/>',
 'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
 'box'=>'<path d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4 9-4M12 11v10M7 5l10 4"/>',
 'wallet'=>'<path d="M20 7V4H6a3 3 0 0 0 0 6h15v10H6a3 3 0 0 1-3-3V7m18 6h-5v4h5"/>',
 'chart'=>'<path d="M4 3v18h17M8 16v-4m5 4V7m5 9v-6"/>',
 'report'=>'<path d="M14 3H5v18h14V8l-5-5Zm0 0v5h5M8 12h8m-8 4h5"/>',
 'reconcile'=>'<path d="M4 7h14l-3-3m3 3-3 3M20 17H6l3 3m-3-3 3-3"/>',
 'history'=>'<path d="M3 10a9 9 0 1 1 2 8M3 4v6h6m3-3v5l4 2"/>',
 'shirt'=>'<path d="m8 3-6 5 4 5 2-2v10h8V11l2 2 4-5-6-5a4 4 0 0 1-8 0Z"/>',
 'badge'=>'<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M9 5V2h6v3m-7 12a4 4 0 0 1 8 0"/><circle cx="12" cy="11" r="2"/>',
 'building'=>'<path d="M4 21V4h11v17M15 10h5v11M2 21h20M8 8h3m-3 4h3m-3 4h3"/>',
 'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
 'close'=>'<path d="m6 6 12 12M18 6 6 18"/>',
 'logout'=>'<path d="M10 4H4v16h6m4-12 4 4-4 4m-6-4h13"/>',
 'arrow'=>'<path d="M4 12h16m-6-6 6 6-6 6"/>',
 'check'=>'<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
 'alert'=>'<path d="m12 3 10 18H2L12 3Zm0 6v5m0 3v.1"/>',
 'eye'=>'<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
 'lock'=>'<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/>',
 'chevron'=>'<path d="m9 5 7 7-7 7"/>',
];
@endphp
<svg {{ $attributes->class(['icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? $paths['grid'] !!}</svg>
