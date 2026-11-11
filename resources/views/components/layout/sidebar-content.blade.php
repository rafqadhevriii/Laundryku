<div class="sidebar-brand"><a href="{{ route('dashboard') }}" aria-label="Beranda Laundryku"><x-ui.brand/></a></div>
<nav class="sidebar-nav sidebar-navigation-minimal" aria-label="Menu utama">
 <p class="nav-heading">RUANG KERJA</p>
 <x-layout.nav-item route="dashboard" pattern="dashboard" icon="grid">Beranda</x-layout.nav-item>
 <x-layout.nav-item route="orders.index" pattern="orders.*" icon="bag">Pesanan</x-layout.nav-item>
 <x-layout.nav-item route="customers.index" pattern="customers.*" icon="users">Pelanggan</x-layout.nav-item>
 <details class="nav-group" @if(request()->routeIs('shifts.*','inventory.*','expenses.*')) open @endif>
  <summary class="nav-group-title"><x-ui.icon name="box"/><span>Operasional</span><x-ui.icon name="chevron"/></summary>
  <div class="nav-group-links">
   <x-layout.nav-item route="shifts.index" pattern="shifts.*" icon="clock">Shift kasir</x-layout.nav-item>
   <x-layout.nav-item route="inventory.index" pattern="inventory.*" icon="box">Stok bahan</x-layout.nav-item>
   <x-layout.nav-item route="expenses.index" pattern="expenses.*" icon="wallet">Pengeluaran</x-layout.nav-item>
  </div>
 </details>
 @if(auth()->user()->isOwner())
 <details class="nav-group" @if(request()->routeIs('owner.reports.*','owner.operational.*','owner.reconciliation.*')) open @endif>
  <summary class="nav-group-title"><x-ui.icon name="chart"/><span>Laporan</span><x-ui.icon name="chevron"/></summary>
  <div class="nav-group-links">
   <x-layout.nav-item route="owner.reports.index" pattern="owner.reports.*" icon="chart">Pendapatan</x-layout.nav-item>
   <x-layout.nav-item route="owner.operational.index" pattern="owner.operational.*" icon="report">Operasional</x-layout.nav-item>
   <x-layout.nav-item route="owner.reconciliation.index" pattern="owner.reconciliation.*" icon="reconcile">Rekonsiliasi</x-layout.nav-item>
  </div>
 </details>
 <details class="nav-group" @if(request()->routeIs('owner.services.*','owner.cashiers.*','owner.audit.*')) open @endif>
  <summary class="nav-group-title"><x-ui.icon name="badge"/><span>Pengaturan</span><x-ui.icon name="chevron"/></summary>
  <div class="nav-group-links">
   <x-layout.nav-item route="owner.services.index" pattern="owner.services.*" icon="shirt">Layanan & harga</x-layout.nav-item>
   <x-layout.nav-item route="owner.cashiers.index" pattern="owner.cashiers.*" icon="users">Akun kasir</x-layout.nav-item>
   <x-layout.nav-item route="owner.audit.index" pattern="owner.audit.*" icon="history">Aktivitas</x-layout.nav-item>
  </div>
 </details>
 @endif
</nav>
