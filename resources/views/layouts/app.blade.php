<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title','Laundryku') | Laundryku</title>
<style>
*{box-sizing:border-box}body{margin:0;font:15px system-ui,Arial;background:#f8fafc;color:#0f172a}
a{color:#2563eb;text-decoration:none}.shell{display:flex;min-height:100vh}.sidebar{width:240px;background:white;border-right:1px solid #e2e8f0;padding:28px 20px}.brand{font-size:24px;font-weight:800;color:#2563eb;margin-bottom:38px}
.nav{display:block;padding:12px 14px;border-radius:9px;margin:7px 0;color:#334155}.nav:hover,.nav.active{background:#eff6ff;color:#1d4ed8}
.main{flex:1;min-width:0;padding:30px;max-width:1400px}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;gap:15px}
.card{background:white;border:1px solid #e2e8f0;border-radius:14px;padding:24px;box-shadow:0 2px 5px #0f172a06}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:18px}
label{display:block;font-weight:600;margin:14px 0 6px}input,select{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit}
button,.btn{background:#2563eb;color:white;border:0;padding:12px 18px;border-radius:9px;cursor:pointer;font:inherit;display:inline-block}
button:hover,.btn:hover{background:#1d4ed8;color:white}.muted{color:#64748b}.alert{padding:14px;background:#ecfdf5;border-radius:8px;margin:15px 0;color:#065f46}.error{color:#b91c1c;font-size:13px}
table{border-collapse:collapse;width:100%}th,td{text-align:left;padding:12px;border-bottom:1px solid #e2e8f0}
@media(max-width:750px){.shell{display:block}.sidebar{width:auto;padding:15px}.brand{margin-bottom:8px}.main{padding:16px}.top{align-items:start;flex-direction:column}}
</style></head><body>
<div class="shell">
@if(auth()->check())
<aside class="sidebar"><div class="brand">Laundryku</div>
<a class="nav {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Dashboard</a>
<a class="nav {{ request()->routeIs('customers.*')?'active':'' }}" href="{{ route('customers.index') }}">Data Pelanggan</a>
<a class="nav {{ request()->routeIs('orders.*')?'active':'' }}" href="{{ route('orders.index') }}">Pesanan Laundry</a>
<a class="nav {{ request()->routeIs('shifts.*')?'active':'' }}" href="{{ route('shifts.index') }}">Shift Kasir</a>
<a class="nav {{ request()->routeIs('expenses.*')?'active':'' }}" href="{{ route('expenses.index') }}">Pengeluaran</a>
<a class="nav {{ request()->routeIs('inventory.*')?'active':'' }}" href="{{ route('inventory.index') }}">Stok Bahan</a>
@if(auth()->user()->isOwner())
<a class="nav {{ request()->routeIs('owner.reports.*')?'active':'' }}" href="{{ route('owner.reports.index') }}">Laporan Pendapatan</a>
<a class="nav {{ request()->routeIs('owner.operational.*')?'active':'' }}" href="{{ route('owner.operational.index') }}">Laporan Operasional</a>
<a class="nav {{ request()->routeIs('owner.audit.*')?'active':'' }}" href="{{ route('owner.audit.index') }}">Riwayat Aktivitas</a>
<a class="nav {{ request()->routeIs('owner.services.*')?'active':'' }}" href="{{ route('owner.services.index') }}">Layanan dan Harga</a>
<a class="nav {{ request()->routeIs('owner.cashiers.*')?'active':'' }}" href="{{ route('owner.cashiers.index') }}">Akun Kasir</a>
@endif
<div class="muted" style="margin-top:35px;font-size:13px">{{ auth()->user()->business?->name }}</div>
</aside>
@endif
<main class="main"><header class="top"><div><h1 style="margin:0">@yield('title','Laundryku')</h1></div>
@if(auth()->check())<div><span class="muted">{{ auth()->user()->name }} ({{ auth()->user()->isOwner()?'Pemilik':'Kasir' }})</span>
<form method="POST" action="{{ route('logout') }}" style="display:inline;margin-left:12px">@csrf<button type="submit">Keluar</button></form></div>@endif
</header>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@yield('content')
</main></div></body></html>