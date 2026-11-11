<!DOCTYPE html>
<html lang="id"><head><x-layout.head/></head>
<body class="guest-body coachpro-guest">
<a href="#main-content" class="skip-link">Lewati ke formulir</a>
<div class="auth-shell coachpro-auth-shell">
 <aside class="auth-story coachpro-auth-story" aria-label="Laundryku">
  <x-ui.brand light/>
  <div class="coachpro-auth-visual">
   <span class="coachpro-auth-eyebrow">SATU TEMPAT UNTUK OPERASIONAL LAUNDRY</span>
   <h2>Kerja lebih rapi.<br>Hari lebih ringan.</h2>
   <p>Pesanan, pembayaran, dan perkembangan usaha tertata dalam satu aplikasi.</p>
   <div class="coachpro-preview" aria-hidden="true">
    <div class="coachpro-preview-top"><span class="coachpro-preview-dot"></span><span class="coachpro-preview-dot"></span><span class="coachpro-preview-dot"></span><span class="coachpro-preview-label">Ringkasan Laundryku</span></div>
    <div class="coachpro-preview-grid"><div><small>Pesanan</small><strong>24</strong><span class="coachpro-preview-bar"></span></div><div><small>Selesai</small><strong>18</strong><span class="coachpro-preview-bar"></span></div><div><small>Pendapatan</small><strong>Rp 480rb</strong><span class="coachpro-preview-bar"></span></div></div>
    <div class="coachpro-preview-lines"><span></span><span></span><span></span></div>
   </div>
  </div>
  <p class="coachpro-auth-footer">Laundryku</p>
 </aside>
 <main id="main-content" class="auth-main coachpro-auth-main" tabindex="-1">
  <div class="auth-mobile-brand"><x-ui.brand/></div>
  <div class="auth-card coachpro-auth-card">@yield('content')</div>
 </main>
</div>
</body></html>