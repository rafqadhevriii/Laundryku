# Laundryku UI/UX Tahap C

Tanggal penyelesaian: 2026-11-10. Branch `feat/uiux-stage-c` dari main `895a31b8cb65cbc5d572db7dd33e5b56afd7c598` (spesifikasi Tahap C, setelah PR konteks cabang aktif). Panduan AGENTS dan kedua spesifikasi UI/UX dibaca sebelum implementasi.

## Hasil desain

- **Pelanggan:** pencarian dan pagination tetap GET; nama/kontak mudah dipindai, form tambah/edit menjaga informasi cabang, catatan dan old input, error dekat field.
- **Stok:** ringkasan dari data controller, kartu jumlah/satuan/status, form mutasi lewat details, label membedakan jumlah perubahan dan stok akhir koreksi. Kasir diberi penjelasan persetujuan. Pemilik mengonfirmasi tombol Setujui/Tolak sebelum POST.
- **Pengeluaran:** formulir kategori, nominal dan tanggal; tabel ringkas dan pagination, validasi terasosiasi. Cabang berasal dari konteks server.
- **Layanan/harga:** tarif/satuan, minimum, estimasi dan status; formulir berkelompok dan penjelasan minimum mengikuti perilaku lama. Tidak memperkenalkan aturan tagihan baru.
- **Akun Kasir:** persyaratan username/password, konfirmasi password, status serta penugasan cabang. Konfigurasi akun mencakup usaha, berbeda dari cabang kerja. Password tidak diisi kembali setelah validasi gagal.
- **Pendapatan/operasional:** komponen filter periode/cabang, KPI nyata, basis tanggal jelas, ekspor CSV/PDF/Excel ke endpoint yang sudah ada. Selisih penerimaan/pengeluaran tetap dijelaskan sebagai bukan laba bersih.
- **Rekonsiliasi:** ringkasan, panduan keputusan, bukti/alasan wajib, shift tidak dipilih otomatis, konfirmasi, error/old input khusus pembayaran sasaran dan histori keputusan tetap tersedia.
- **Riwayat aktivitas:** konteks seluruh usaha, waktu/pengguna/referensi, disclosure detail JSON escaped dan mudah dibaca. Nilai asli tidak diganti.

Identitas, tokens, logo, sidebar dan pengalih cabang Tahap A/PR #5 dipertahankan. KPI/badge/section-header/empty-state/form-feedback/select/pagination/confirmation Tahap B digunakan kembali. Komponen baru: `form-control`, `data-table`, `report-filter`, `workspace-context`. Ikon inline plus/search/download/tag orisinal ditambahkan; CSS/JS lokal melalui Vite, tanpa CDN atau dependensi produksi baru.

## Batas data dan keamanan

Seluruh controller, route, nama input bisnis, CSRF/method, middleware/service, model, validasi server dan hak akses tetap. Tidak mengubah migration/seeder/schema, database utama, perhitungan uang, alur pembayaran/shift, aturan cabang atau nota thermal 58/80mm. Tidak menjalankan migrasi manual. Suite Laravel menggunakan RefreshDatabase hanya setelah pengaman TestCase memverifikasi nama database tepat `laundryku_testing`.

Mode Semua Cabang mempertahankan daftar/agregat dan gate membuat data per cabang. Kasir tidak mendapat pengalih. Layanan, akun Kasir dan audit umum tetap konfigurasi seluruh usaha, diberi keterangan eksplisit. Filter cabang historis laporan dalam Semua Cabang tetap tersedia.

Tidak ada query model baru di view atau statistik rekaan. Jumlah stok terdaftar memakai total paginator; menipis dihitung dari collection **halaman yang sedang tampil**, dengan label batas cakupan; pending memakai collection controller. Histori mutasi stok individual belum tersedia dari controller, sehingga tidak dibuat. Aktivitas stok agregat tetap pada laporan operasional. Follow-up minimum_weight dan rincian kas masuk shift aktif tetap terpisah sesuai spesifikasi.

Marker `_form_context` pada mutasi/rekonsiliasi hanya membantu old input kembali ke form sasaran, bukan memilih cabang/record atau memutuskan izin; backend mengabaikannya dan tetap mengotorisasi ID route. Semua keputusan tetap dari validasi/transaksi server.

## Pengujian sebenarnya

| Pemeriksaan | Hasil |
| --- | --- |
| `npm ci` | Berhasil, 90 packages |
| `npm run build` | Berhasil, Vite 7.3.7; aset lokal produksi |
| `php artisan test --compact` | **74 passed, 740 assertions**, PHP 8.5.12 / MariaDB 11 pada laundryku_testing |
| `php artisan view:cache` | Berhasil |
| `git diff --check` | Berhasil |
| Chromium, 375/768/1280px | 73 kondisi halaman/peran/data, nol overflow root |
| Axe WCAG 2 A/AA, 2.1 A/AA, 2.2 AA | 58 kondisi, nol pelanggaran |
| JavaScript | Nol pageerror |

Lima tes `StageCPresentationTest` menambah pemeriksaan pagination (17 pelanggan/21 barang), filter/query, validasi yang menjaga nilai, password kosong, old input stok/rekonsiliasi hanya pada form yang benar, empty states, dan akses Kasir ke menu Pemilik tetap 403. Suite finansial/otorisasi/cabang lama tetap berjalan seluruhnya.

Browser memeriksa seluruh 11 layar Pemilik (9 modul dan 2 form) serta 4 layar Kasir pada tiga lebar; halaman kosong juga dibuka dengan usaha sintetis terpisah. Pemeriksaan interaksi mencakup gate Semua Cabang, konteks Selatan/Utama, server validation pelanggan/layanan/Kasir/pengeluaran/stok/rekonsiliasi, cancel tanpa POST, review tepat satu POST dengan submitter keputusan benar, histori rekonsiliasi, tabel geser via keyboard, 403 menu Pemilik, dan ekspor laporan dalam cabang terpilih serta filter historis Semua Cabang. Unduhan memakai session yang sama dan harus HTTP 200.

Satu overflow pada halaman audit mobile ditemukan pada pemeriksaan awal: posisi teks sr-only di dalam tabel geser. Penempatan relatif pada region/summary memperbaikinya; pemeriksaan lengkap kemudian diulang. Hasil final, bukan hasil awal, tersimpan di [browser-results.json](browser-results.json). Angka/tanggal pada screenshot berasal dari fixture sintetis dan waktu eksekusi; pengujian interaksi dapat menambah histori sintetis sesudah screenshot.

## Screenshot sebelum dan sesudah

18 screenshot sebelum + 24 sesudah = **42 screenshot**, semuanya data sintetis pada database pengujian. Before diambil sebelum perubahan presentasi dari baseline main; after diambil pada implementasi final. Tidak menggunakan data pelanggan asli.

| Modul | Desktop sebelum | Desktop sesudah | Mobile sebelum | Mobile sesudah |
| --- | --- | --- | --- | --- |
| Pelanggan | [Lihat](screenshots/before-desktop-customers.jpg) | [Lihat](screenshots/after-desktop-customers.jpg) | [Lihat](screenshots/before-mobile-customers.jpg) | [Lihat](screenshots/after-mobile-customers.jpg) |
| Stok bahan | [Lihat](screenshots/before-desktop-inventory.jpg) | [Lihat](screenshots/after-desktop-inventory.jpg) | [Lihat](screenshots/before-mobile-inventory.jpg) | [Lihat](screenshots/after-mobile-inventory.jpg) |
| Pengeluaran | [Lihat](screenshots/before-desktop-expenses.jpg) | [Lihat](screenshots/after-desktop-expenses.jpg) | [Lihat](screenshots/before-mobile-expenses.jpg) | [Lihat](screenshots/after-mobile-expenses.jpg) |
| Layanan dan harga | [Lihat](screenshots/before-desktop-services.jpg) | [Lihat](screenshots/after-desktop-services.jpg) | [Lihat](screenshots/before-mobile-services.jpg) | [Lihat](screenshots/after-mobile-services.jpg) |
| Akun Kasir | [Lihat](screenshots/before-desktop-cashiers.jpg) | [Lihat](screenshots/after-desktop-cashiers.jpg) | [Lihat](screenshots/before-mobile-cashiers.jpg) | [Lihat](screenshots/after-mobile-cashiers.jpg) |
| Pendapatan | [Lihat](screenshots/before-desktop-revenue.jpg) | [Lihat](screenshots/after-desktop-revenue.jpg) | [Lihat](screenshots/before-mobile-revenue.jpg) | [Lihat](screenshots/after-mobile-revenue.jpg) |
| Operasional | [Lihat](screenshots/before-desktop-operational.jpg) | [Lihat](screenshots/after-desktop-operational.jpg) | [Lihat](screenshots/before-mobile-operational.jpg) | [Lihat](screenshots/after-mobile-operational.jpg) |
| Rekonsiliasi | [Lihat](screenshots/before-desktop-reconciliation.jpg) | [Lihat](screenshots/after-desktop-reconciliation.jpg) | [Lihat](screenshots/before-mobile-reconciliation.jpg) | [Lihat](screenshots/after-mobile-reconciliation.jpg) |
| Riwayat aktivitas | [Lihat](screenshots/before-desktop-audit.jpg) | [Lihat](screenshots/after-desktop-audit.jpg) | [Lihat](screenshots/before-mobile-audit.jpg) | [Lihat](screenshots/after-mobile-audit.jpg) |

Tambahan sesudah: [form pelanggan desktop](screenshots/after-desktop-customerForm.jpg), [mobile](screenshots/after-mobile-customerForm.jpg), [form layanan desktop](screenshots/after-desktop-serviceForm.jpg), [mobile](screenshots/after-mobile-serviceForm.jpg), [stok Kasir desktop](screenshots/after-desktop-cashier-inventory.jpg), [mobile](screenshots/after-mobile-cashier-inventory.jpg).

## Reproduksi dan batas pemeriksaan

1. Gunakan lingkungan terpisah dengan APP_ENV testing, MySQL/MariaDB database **laundryku_testing** dan kredensial pengujian. Jangan menunjuk database utama. Periksa phpunit.xml dan TestCase sebelum suite.
2. Jalankan `npm ci`, `npm run build`, `php artisan test --compact`, `php artisan view:cache`, `git diff --check`.
3. Setelah suite selesai, siapkan fixture sintetis: Pemilik/Kasir pada usaha dengan cabang Utama dan Selatan; pelanggan Selatan; beberapa pelanggan/pesanan/pembayaran termasuk tunai historis belum ditinjau tanpa shift; shift Kasir terbuka yang mencakup waktu pembayaran; tiga barang (dua menipis), satu pengajuan koreksi pending, satu pengeluaran, layanan kg/item/nonaktif, audit. Siapkan juga Pemilik/Kasir usaha kosong terpisah. Skrip browser menguji perubahan atas fixture ini; tidak boleh memakai data nyata.
4. Jalankan web pengujian dengan session file, build Vite dan Chromium `/usr/bin/chromium`. Jangan menjalankan suite RefreshDatabase bersamaan dengan browser karena fixture pengujian akan berubah.
5. Instal `playwright` dan `@axe-core/playwright` pada direktori scratch terpisah. Arahkan NODE_PATH ke node_modules tersebut. Set UIUX_BASE_URL (default localhost:8085), UIUX_OWNER, UIUX_CASHIER, UIUX_EMPTY_OWNER, UIUX_EMPTY_CASHIER, UIUX_TEST_PASSWORD dari akun sintetis; lalu `node docs/uiux/stage-c/browser-check.cjs` dari root repo. Skrip menulis hasil mesin dan screenshot after; before disimpan dari baseline.

Axe otomatis bukan sertifikasi WCAG menyeluruh. Pemeriksaan ini belum mencakup UAT, pembaca layar, perangkat fisik, browser selain Chromium, printer atau konkurensi multiproses. Reduced motion memakai aturan global A/B (160ms, dinonaktifkan sesuai preferensi); details tetap bisa digunakan tanpa JavaScript. Berikutnya review PR, UAT dan Black Box manual memakai BLACKBOX_TEST_PLAN.md. Jangan merge otomatis.
