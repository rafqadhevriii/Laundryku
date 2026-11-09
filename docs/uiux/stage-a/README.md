# Review UI/UX Laundryku — Tahap A

Tanggal: 2026-11-09. Basis: main `a04ef7d`. Branch: `feat/uiux-stage-a`.

## Lingkup

Identitas visual, token desain, shell aplikasi, navigasi sesuai peran, login dan setup. Halaman operasional menggunakan konten/alur yang sama dengan gaya dasar baru. Controller, route, model, middleware, skema, seed, kalkulasi dan validasi server tidak diubah. CSS nota standalone 58/80mm dipertahankan.

Logo dan favicon merupakan SVG orisinal: pakaian bergaris sederhana dengan gelembung, wordmark Laundryku. Set ikon garis dibuat lokal dalam komponen Blade; tidak mengambil merek, gambar, font, atau ikon dari CDN. Font memakai sans-serif sistem perangkat, sehingga tidak memerlukan unduhan font pihak ketiga.

## Sistem desain

- Navy `#142D4E`, teal identitas `#0F8B8D`, latar `#F5F7FA`, teks `#172B4D`, putih `#FFFFFF`.
- Teal gelap `#087174` untuk teks dan tombol: teal identitas pada putih hanya 4,12:1, sehingga tidak dipakai untuk teks kecil di atas putih. Teal gelap/putih 5,79:1; teks utama/putih 14,10:1; teks sekunder/putih 5,96:1; border input/putih 3,57:1; mint/navy 10,87:1.
- Base 15px; judul 25–32px; label 14px; pola spasi, border, radius, dan fokus konsisten. Warna status lama tetap disertai teks.
- Komponen Blade: head, sidebar-content, nav-item, topbar, brand, icon, button, alert dan field. Layout guest terpisah untuk login/setup. Komponen status/KPI/tabel/modul operasional lanjutan termasuk Tahap B/C.
- CSS/JS masuk melalui `@vite`. `npm run build` menghasilkan manifest dan aset berhash pada public/build tanpa dev server. package-lock.json ditambahkan untuk instalasi `npm ci` yang dapat direproduksi; public/build tetap mengikuti .gitignore proyek.
- Transisi 160ms; prefers-reduced-motion menonaktifkan gerakan efektif. Tidak ada animasi transaksi atau kalkulasi di klien.

## Nielsen Heuristics

| Prinsip | Penerapan Tahap A |
|---|---|
| Status sistem terlihat | Judul halaman, konteks cabang/peran, aria-current dan bentuk aktif, status submit login/setup, feedback success/error |
| Sesuai dunia pengguna | Istilah operasional Indonesia dan label menu yang tetap dikenali |
| Kendali pengguna | Drawer dapat ditutup tombol/Escape/backdrop; fokus dikembalikan; tampil/sembunyikan password |
| Konsistensi | Token warna/typografi dan komponen form/navigasi yang sama |
| Pencegahan kesalahan | Label dan petunjuk, native required/minlength yang sesuai aturan lama; validasi server dipertahankan |
| Pengenalan | Menu berlabel + ikon, dikelompokkan per tugas dan peran; identitas usaha/cabang selalu terlihat |
| Efisiensi | Skip link, keyboard, fokus dialog terkurung, area tabel bisa difokus dan digeser |
| Minimalisme | Tanpa chart baru, gambar stok, gradient berat, atau kontrol dekoratif yang menyerupai fitur |
| Pemulihan kesalahan | Pesan dekat field melalui aria-describedby/aria-invalid; nilai nonrahasia tetap ada, password tidak diisi ulang |
| Bantuan kontekstual | Petunjuk akun Pemilik/Kasir, bantuan akses, batas setup awal, dan minimum password |

## Verifikasi sebenarnya

| Pemeriksaan | Hasil |
|---|---|
| npm run build | Berhasil, Vite 7.3.7; CSS sekitar 47,97 kB / gzip 12,11 kB dan JS 62,37 kB / gzip 23,37 kB |
| php artisan test | 51 tes lulus, 456 assertions, PHP 8.5.12/MariaDB 11 pada laundryku_testing terisolasi |
| php artisan view:cache | Berhasil; ini perintah kompilasi view yang disebut dalam kriteria spesifikasi |
| git diff --check | Berhasil |
| UiShellTest (5 tes) | Aset lokal/login, validasi dan old input login/setup, menu role, indikator aktif, backend Kasir 403, serta nota 58/80mm tanpa shell/Vite |
| Playwright/Chromium | Login, toggle password, dialog via Enter, 24 perpindahan Tab tetap dalam dialog, Escape, fokus kembali, klik backdrop, aria-expanded, reduced motion, dan navigasi fallback tanpa JavaScript berhasil |
| Responsif | 13 URL operasional x lebar 375/768/1280 = 39 kombinasi, tanpa overflow root di luar scroll tabel; login/setup juga diperiksa desktop/mobile |
| axe-core | Tidak ada pelanggaran pada login, dashboard Pemilik, drawer mobile, serta setup desktop/mobile untuk tag WCAG 2 A/AA, 2.1 AA dan 2.2 AA yang diperiksa otomatis |
| Setup invalid di browser | Nilai nonrahasia bertahan; password kosong; email ditandai aria-invalid dan terhubung dengan pesan error |

Hasil mesin: [browser-results.json](browser-results.json) dan [setup-results.json](setup-results.json). Semua data layar adalah fixture sintetis dari database pengujian. Tidak mengakses database utama, menjalankan migrasi manual, atau mengubah seeder. Pembuatan skema/fixture melalui rangkaian tes hanya pada laundryku_testing yang dilindungi TestCase.

Pemeriksaan otomatis ini bukan sertifikasi kepatuhan WCAG seluruh aplikasi. Uji pembaca layar, pengguna laundry, browser/perangkat lain, dan printer fisik belum dilakukan. Label/komponen khusus formulir modul lama serta penyempurnaan konten dashboard/pesanan/pembayaran tetap lingkup Tahap B/C. Scope axe di atas tidak mencakup semua keadaan semua modul lama.

## Screenshot sebelum/sesudah

PNG asli dari browser, bukan mockup. Desktop 1440x1000 dan mobile 375x812, full-page kecuali drawer yang memakai viewport mobile. Angka/ID fixture dapat berbeda akibat reset pengujian; data sintetis dan alur yang sama dipakai untuk review visual.

| Halaman | Sebelum desktop | Sesudah desktop | Sebelum mobile | Sesudah mobile |
|---|---|---|---|---|
| Login | [Lihat](screenshots/before/login-desktop.png) | [Lihat](screenshots/after/login-desktop.png) | [Lihat](screenshots/before/login-mobile.png) | [Lihat](screenshots/after/login-mobile.png) |
| Setup | [Lihat](screenshots/before/setup-desktop.png) | [Lihat](screenshots/after/setup-desktop.png) | [Lihat](screenshots/before/setup-mobile.png) | [Lihat](screenshots/after/setup-mobile.png) |
| Dashboard Pemilik | [Lihat](screenshots/before/dashboard-owner-desktop.png) | [Lihat](screenshots/after/dashboard-owner-desktop.png) | [Lihat](screenshots/before/dashboard-owner-mobile.png) | [Lihat](screenshots/after/dashboard-owner-mobile.png) |
| Dashboard Kasir | [Lihat](screenshots/before/dashboard-cashier-desktop.png) | [Lihat](screenshots/after/dashboard-cashier-desktop.png) | [Lihat](screenshots/before/dashboard-cashier-mobile.png) | [Lihat](screenshots/after/dashboard-cashier-mobile.png) |
| Pesanan | [Lihat](screenshots/before/orders-desktop.png) | [Lihat](screenshots/after/orders-desktop.png) | [Lihat](screenshots/before/orders-mobile.png) | [Lihat](screenshots/after/orders-mobile.png) |
| Detail/pembayaran | [Lihat](screenshots/before/payment-desktop.png) | [Lihat](screenshots/after/payment-desktop.png) | [Lihat](screenshots/before/payment-mobile.png) | [Lihat](screenshots/after/payment-mobile.png) |
| Stok | [Lihat](screenshots/before/inventory-desktop.png) | [Lihat](screenshots/after/inventory-desktop.png) | [Lihat](screenshots/before/inventory-mobile.png) | [Lihat](screenshots/after/inventory-mobile.png) |
| Laporan | [Lihat](screenshots/before/reports-desktop.png) | [Lihat](screenshots/after/reports-desktop.png) | [Lihat](screenshots/before/reports-mobile.png) | [Lihat](screenshots/after/reports-mobile.png) |

Tambahan: [drawer mobile](screenshots/after/mobile-navigation.png), [validasi setup mobile](screenshots/after/setup-validation-mobile.png).

## Langkah berikutnya

Review identitas dan navigasi pada PR Tahap A. Tahap B melanjutkan desain isi dashboard, daftar/detail pesanan, pembayaran dan shift; Tahap C merapikan modul lain dan memperluas pemeriksaan aksesibilitas serta regresi. Tidak melakukan merge otomatis.
