<!DOCTYPE html>
<html lang="id"><head><x-layout.head/></head>
<body class="guest-body">
<a href="#main-content" class="skip-link">Lewati ke formulir</a>
<div class="auth-shell">
<aside class="auth-story" aria-label="Tentang Laundryku">
<x-ui.brand light/>
<div class="auth-story-content"><p class="eyebrow">Ruang kerja laundry Anda</p><h2>Operasional rapi.<br>Usaha terkendali.</h2><p>Kelola pesanan, pantau setiap cabang, dan lihat perjalanan usaha dalam satu tempat.</p>
<div class="laundry-illustration" aria-hidden="true"><div class="illustration-ring"></div><x-ui.icon name="shirt"/><span class="bubble bubble-one"></span><span class="bubble bubble-two"></span><span class="illustration-caption">Bersih dalam layanan.<br>Rapi dalam pengelolaan.</span></div>
<div class="auth-feature"><x-ui.icon name="bag"/><div><strong>Dari diterima hingga diambil</strong><span>Alur pesanan yang mudah diikuti.</span></div></div>
<div class="auth-feature"><x-ui.icon name="chart"/><div><strong>Gambaran usaha lebih jelas</strong><span>Operasional dan pendapatan dalam satu ruang.</span></div></div>
</div><p class="auth-story-foot">Dibuat untuk ritme kerja laundry sehari-hari.</p>
</aside>
<main id="main-content" class="auth-main" tabindex="-1"><div class="auth-mobile-brand"><x-ui.brand/></div><div class="auth-card">@yield('content')</div><p class="auth-footer"><x-ui.icon name="lock"/>Akses sesuai peran Pemilik dan Kasir.</p></main>
</div></body></html>
