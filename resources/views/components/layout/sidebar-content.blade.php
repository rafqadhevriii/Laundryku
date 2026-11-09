<div class="sidebar-brand"><a href="{{ route('dashboard') }}" aria-label="Laundryku — Dashboard"><x-ui.brand light/></a></div>
<div class="workspace-context"><span class="workspace-icon"><x-ui.icon name="building"/></span><div><strong>{{ auth()->user()->business?->name }}</strong><span>{{ $activeBranchLabel }}</span></div></div>
<nav class="sidebar-nav" aria-label="Menu utama">
<p class="nav-heading">Operasional</p>
<x-layout.nav-item route="dashboard" pattern="dashboard" icon="grid">Dashboard</x-layout.nav-item>
<x-layout.nav-item route="orders.index" pattern="orders.*" icon="bag">Pesanan Laundry</x-layout.nav-item>
<x-layout.nav-item route="customers.index" pattern="customers.*" icon="users">Data Pelanggan</x-layout.nav-item>
<x-layout.nav-item route="shifts.index" pattern="shifts.*" icon="clock">Shift Kasir</x-layout.nav-item>
<x-layout.nav-item route="inventory.index" pattern="inventory.*" icon="box">Stok Bahan</x-layout.nav-item>
<x-layout.nav-item route="expenses.index" pattern="expenses.*" icon="wallet">Pengeluaran</x-layout.nav-item>
@if(auth()->user()->isOwner())
<p class="nav-heading">Laporan & keuangan</p>
<x-layout.nav-item route="owner.reports.index" pattern="owner.reports.*" icon="chart">Laporan Pendapatan</x-layout.nav-item>
<x-layout.nav-item route="owner.operational.index" pattern="owner.operational.*" icon="report">Laporan Operasional</x-layout.nav-item>
<x-layout.nav-item route="owner.reconciliation.index" pattern="owner.reconciliation.*" icon="reconcile">Rekonsiliasi Tunai</x-layout.nav-item>
<p class="nav-heading">Kelola usaha</p>
<x-layout.nav-item route="owner.services.index" pattern="owner.services.*" icon="shirt">Layanan dan Harga</x-layout.nav-item>
<x-layout.nav-item route="owner.cashiers.index" pattern="owner.cashiers.*" icon="badge">Akun Kasir</x-layout.nav-item>
<x-layout.nav-item route="owner.audit.index" pattern="owner.audit.*" icon="history">Riwayat Aktivitas</x-layout.nav-item>
@endif
</nav>
<div class="sidebar-foot"><span class="live-dot" aria-hidden="true"></span><span>Ruang kerja {{ auth()->user()->isOwner()?'Pemilik':'Kasir' }}</span><small>Laundryku · Operasional lebih rapi</small></div>
