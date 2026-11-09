<header class="topbar" data-workspace-content>
<div class="topbar-location"><button type="button" class="icon-button mobile-menu-button" data-nav-open aria-label="Buka menu navigasi" aria-controls="mobile-navigation" aria-expanded="false" hidden><x-ui.icon name="menu"/></button><span class="topbar-label">Ruang kerja</span><x-ui.icon name="chevron"/><span class="current-page">@yield('title','Dashboard')</span></div>
<x-layout.branch-switcher/>
<div class="topbar-account"><span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span><div class="account-name"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->isOwner()?'Pemilik':'Kasir' }}</span></div><form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button variant="ghost" icon="logout" class="logout-button">Keluar</x-ui.button></form></div>
</header>
