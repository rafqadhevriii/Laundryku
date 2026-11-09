# Konteks cabang aktif — laporan implementasi

Tanggal: 2026-11-09. Basis main: `a431c9f56b195d0685e4974189cdc51ba0c76177`.

Pemilik memilih cabang melalui header (pilih lalu **Terapkan**). Semua Cabang menampilkan gabungan; pembuatan pesanan, pelanggan, stok dan pengeluaran meminta cabang tertentu. Kasir hanya menggunakan penugasan akun. Pengalih dapat dipakai dengan keyboard dan tetap terlihat di mobile. Form pesanan menampilkan informasi cabang read-only, tanpa dropdown kedua.

## Keamanan dan kompatibilitas

- Service `ActiveBranchContext` menjadi sumber konteks per request. Middleware memuat ulang akun, usaha dan cabang aktif, bukan mempercayai body/query/session tanpa pemeriksaan.
- Namespace sesi per akun/usaha, reset saat login/logout/setup. POST pengalih memakai autentikasi Pemilik + CSRF dan whitelist nilai/redirect. Perubahan menghapus old input/errors dan redirect GET, tidak mengulang POST lama.
- Sesi stale/asing/nonaktif/malformed dibersihkan. GET memberi peringatan dan agregat; mutasi ditolak dengan error sebelum menulis. Kasir tanpa penugasan aktif ditolak, tidak pernah menjadi Semua Cabang.
- Scope usaha/cabang diterapkan sebelum pagination/agregasi pada data operasional, pembayaran, nota, shift, laporan dan rekonsiliasi. URL laporan tidak dapat menimpa cabang tertentu; filter laporan historis masih tersedia dalam Semua Cabang.
- Cabang untuk pesanan/pelanggan/stok/pengeluaran baru berasal dari konteks server, dikonfirmasi ulang dengan row lock saat transaksi. Customer pesanan harus cocok. Kiriman branch_id tidak memindahkan tujuan atau identitas historis.
- Semua Cabang mempertahankan izin Pemilik lama untuk record tertentu dalam usahanya, termasuk histori cabang nonaktif. Halaman administrasi usaha (layanan, akun Kasir, audit umum) tetap mencakup usaha; header tidak membatasi konfigurasi usaha tersebut.
- Tidak mengubah migration/model/skema atau rumus keuangan. Tidak mengakses database utama atau menjalankan migrasi manual. RefreshDatabase dan fixture hanya pada database pengujian, dengan pengaman TestCase sebelum menyentuh schema.

## Hasil pengujian sebenarnya

| Pemeriksaan | Hasil |
| --- | --- |
| `php artisan test --compact` | 69 passed, 671 assertions; PHP 8.5.12 / MariaDB 11, laundryku_testing |
| `npm run build` | Berhasil, Vite 7.3.7, 63 modul |
| `php artisan view:cache` | Berhasil |
| `git diff --check` | Berhasil |
| Browser Chromium | Desktop 1440×1000, mobile 390×844, reduced motion |
| Interaksi | Pemilik Semua/A/B, persistensi navigasi, form terfilter, keyboard, logout/login Kasir berhasil |
| CSRF browser | POST tanpa token ditolak HTTP 419 |
| Axe WCAG 2 A/AA, 2.1 A/AA, 2.2 AA | Nol pelanggaran pada 10 tampilan teruji |
| Layout/JS | Nol overflow horizontal, nol pageerror |

`tests/Feature/ActiveBranchContextTest.php` menambah 11 kasus yang mencakup daftar/agregat, manipulasi request/sesi, stale/transfer/nonaktif, akun bergantian, pelanggan lintas cabang, pembuatan/akses stok, laporan/ekspor, serta kesetaraan snapshot pembayaran dan saldo shift setelah berpindah konteks. Suite pembayaran/rekonsiliasi/otorisasi lama tetap dijalankan.

Browser memakai fixture **sintetis**, bukan pelanggan asli. [Hasil mesin](browser-results.json) mencatat audit dan interaksi. Screenshot dibuat setelah tes, menggunakan fixture testing terpisah; tes berikutnya dapat mengganti fixture testing tersebut.

## Bukti tampilan

| Kondisi | Desktop | Mobile |
| --- | --- | --- |
| Semua Cabang, dashboard gabungan | [Lihat](screenshots/desktop-all-dashboard.jpg) | [Lihat](screenshots/mobile-all-dashboard.jpg) |
| Semua Cabang, pembuatan dibatasi | [Lihat](screenshots/desktop-all-order-gate.jpg) | [Lihat](screenshots/mobile-all-order-gate.jpg) |
| Cabang A (Utama), dashboard | [Lihat](screenshots/desktop-branch-a-dashboard.jpg) | [Lihat](screenshots/mobile-branch-a-dashboard.jpg) |
| Cabang B (Selatan), form pesanan | [Lihat](screenshots/desktop-branch-b-order.jpg) | [Lihat](screenshots/mobile-branch-b-order.jpg) |
| Kasir, cabang penugasan | [Lihat](screenshots/desktop-cashier-dashboard.jpg) | [Lihat](screenshots/mobile-cashier-dashboard.jpg) |

## Reproduksi

1. Siapkan environment pengujian yang menunjuk **hanya** laundryku_testing. TestCase menolak database lain sebelum RefreshDatabase. Jangan memakai kredensial/database utama.
2. Jalankan `npm ci`, `npm run build`, `php artisan test --compact`, `php artisan view:cache`, `git diff --check`.
3. Untuk browser, sesudah suite selesai, siapkan akun Pemilik/Kasir sintetis dengan password khusus tes, cabang aktif bernama Utama dan Selatan, pelanggan Selatan, serta data demo. Jalankan aplikasi testing dengan session file dan aset build, misalnya di port 8085. Jangan jalankan suite RefreshDatabase saat browser masih menggunakan fixture.
4. Instal Playwright dan @axe-core/playwright di direktori scratch terpisah, gunakan NODE_PATH direktori node_modules tersebut. Chromium tersedia pada `/usr/bin/chromium` dalam environment pengujian ini.
5. Set `BRANCH_BASE_URL`, `BRANCH_OWNER`, `BRANCH_CASHIER`, `BRANCH_TEST_PASSWORD` dari akun sintetis, lalu jalankan `node docs/branch-context/browser-check.cjs` dari root repo. Skrip menulis ulang screenshot/hasil audit; jangan gunakan pada data produksi.

Axe otomatis tidak membuktikan kepatuhan menyeluruh. Belum dilakukan UAT, uji pembaca layar/perangkat fisik, atau race beberapa tab. Pilihan cabang dibagi seluruh tab dalam satu sesi login; server selalu memeriksa konteks terbaru. Review dan UAT menjadi langkah berikutnya, tanpa merge otomatis.
