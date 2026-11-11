<header class="topbar topbar-minimal studio-topbar" data-workspace-content>
 <div class="topbar-location">
  <button type="button" class="icon-button mobile-menu-button" data-nav-open aria-label="Buka menu navigasi" aria-controls="mobile-navigation" aria-expanded="false" hidden><x-ui.icon name="menu"/></button>
  @unless(request()->routeIs('orders.index'))
  <form action="{{ route('orders.index') }}" method="GET" class="studio-quick-search" role="search">
   <label for="studio-global-search" class="sr-only">Cari pesanan atau pelanggan</label>
   <x-ui.icon name="search"/>
   <input id="studio-global-search" name="search" type="search" placeholder="Cari pesanan atau pelanggan..." value="{{ request()->routeIs('orders.index')?request('search'):'' }}" autocomplete="off" aria-label="Cari pesanan atau pelanggan">
   <kbd aria-hidden="true">/</kbd>
  </form>
  @endunless
  <strong class="studio-mobile-wordmark">Laundryku</strong>
 </div>
 <div class="topbar-right">

  <details class="profile-menu" data-profile-menu>
   <summary class="profile-trigger" aria-label="Buka menu profil {{ auth()->user()->name }}">
    <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span>
    <span class="profile-trigger-label"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->isOwner()?'Pemilik':'Kasir' }}</small></span>
    <x-ui.icon name="chevron"/>
   </summary>
   <div class="profile-popover">
    <div class="profile-popover-heading"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email ?: auth()->user()->username }}</small></div>
    <a href="{{ route('profile.edit') }}" class="profile-popover-link"><x-ui.icon name="badge"/> Profil & akun</a>
    @if(auth()->user()->isOwner())
    <details class="studio-location-details">
     <summary class="profile-popover-link"><x-ui.icon name="building"/> Cabang kerja <x-ui.icon name="chevron"/></summary>
     <div class="studio-location-form"><x-layout.branch-switcher/></div>
    </details>
    @endif
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="profile-popover-link profile-logout"><x-ui.icon name="logout"/> Keluar akun</button></form>
   </div>
  </details>
 </div>
</header>
