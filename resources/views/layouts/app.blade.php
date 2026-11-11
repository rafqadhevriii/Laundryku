<!DOCTYPE html>
<html lang="id"><head><x-layout.head/></head>
<body class="app-body">
<a href="#main-content" class="skip-link">Lewati navigasi ke konten utama</a>
@auth
<div class="app-shell">
<aside class="sidebar sidebar-desktop" aria-label="Navigasi utama"><x-layout.sidebar-content/></aside>
<dialog id="mobile-navigation" class="mobile-navigation" aria-labelledby="mobile-nav-title">
<h2 id="mobile-nav-title" class="sr-only">Menu navigasi Laundryku</h2><button type="button" class="icon-button drawer-close" data-nav-close aria-label="Tutup menu navigasi"><x-ui.icon name="close"/></button>
<x-layout.sidebar-content/>
</dialog>
<div class="workspace">
<x-layout.topbar/>
<details class="navigation-fallback"><summary>Menu navigasi</summary><x-layout.sidebar-content/></details>
<main id="main-content" class="main" tabindex="-1" data-workspace-content>
@unless(request()->routeIs('dashboard','profile.edit'))<div class="page-heading"><div><p class="eyebrow">{{ auth()->user()->isOwner()?'Pantau & kelola usaha':'Operasional cabang' }}</p><h1>@yield('title','Laundryku')</h1></div>@yield('page-actions')</div>@endunless
@if(session('branch_context_warning'))<x-ui.alert type="error">{{ session('branch_context_warning') }}</x-ui.alert>@endif
@if(session('success'))<x-ui.alert>{{ session('success') }}</x-ui.alert>@endif
@yield('content')

</main></div></div>
<x-ui.confirm-dialog/>
@else
<main id="main-content" class="main" tabindex="-1">@yield('content')</main>
@endauth
</body></html>
