<header class="topbar topbar-minimal" data-workspace-content>
 <div class="topbar-location">
  <button type="button" class="icon-button mobile-menu-button" data-nav-open aria-label="Buka menu navigasi" aria-controls="mobile-navigation" aria-expanded="false" hidden><x-ui.icon name="menu"/></button>
  <div class="topbar-page-title"><small>LAUNDRYKU</small><strong>@yield('title','Dashboard')</strong></div>
 </div>
 <div class="topbar-right">
  <x-layout.branch-switcher/>
  <details class="profile-menu" data-profile-menu>
   <summary class="profile-trigger" aria-label="Buka menu profil {{ auth()->user()->name }}">
    <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span>
    <span class="profile-trigger-label"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->isOwner()?'Pemilik':'Kasir' }}</small></span>
    <x-ui.icon name="chevron"/>
   </summary>
   <div class="profile-popover">
    <div class="profile-popover-heading"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
    <a href="{{ route('profile.edit') }}" class="profile-popover-link">Pengaturan profil</a>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="profile-popover-link profile-logout">Keluar akun</button></form>
   </div>
  </details>
 </div>
</header>
