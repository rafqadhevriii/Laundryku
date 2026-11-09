# Review UI/UX Laundryku — Tahap B

Tanggal: 2026-11-09. Basis main `cca5d66`. Branch `feat/uiux-stage-b`.

## Lingkup perubahan

- Dashboard Pemilik/Kasir: KPI reusable dari `$stats`, konteks periode/cabang, CTA menuju route yang sudah berfungsi, prioritas pesanan terlambat/siap diambil/stok menipis, serta empty state dan daftar terbaru. Tidak ada angka, grafik, persentase atau tren buatan. Angka panel berlabel “ditampilkan” berasal dari koleksi terbatas yang diberikan controller, bukan diklaim sebagai total seluruh pesanan.
- Daftar pesanan: GET `search`/`status` dipertahankan, reset filter, jumlah hasil dari paginator, badge teks pengerjaan/pembayaran, tabel desktop, kartu mobile dari record yang sama, detail dan pagination Indonesia dengan nomor halaman, sebelumnya/berikutnya dan query tetap dibawa.
- Tambah pesanan: kelompok pelanggan/cabang, layanan dan catatan; field error terasosiasi; old input termasuk indeks baris yang tidak berurutan dipertahankan. Tambah/hapus sampai 20 layanan, ID unik, label kg/barang dan angka bulat mengikuti aturan server yang sudah ada. Filter pelanggan hanya memakai pilihan yang diberikan controller.
- Ringkasan pilihan menampilkan pelanggan, kuantitas, tarif yang sudah diformat server dan durasi layanan terlama. **Tidak menghitung total uang di JavaScript.** Total dan waktu selesai resmi ditampilkan setelah server menyimpan pesanan. Penjumlahan durasi tidak digunakan: server memakai durasi terlama, bukan menjumlah seluruh durasi.
- Detail: identitas, timeline berdasarkan status saat ini, rincian harga snapshot, total tersimpan, aksi berikutnya, pembayaran dan riwayat percobaan. Tidak menciptakan timestamp proses/siap; hanya waktu dibuat/diambil yang tersedia ditampilkan. Aksi pelunasan/pickup/retry disesuaikan status dan peran yang sudah berlaku.
- Nota 58/80mm membuka route receipt dengan query `paper` yang sama; file/CSS nota tetap independen dari shell.
- Shift: buka/tutup/review, saldo tersimpan, status, periode, catatan, serta label selisih Kurang/Lebih/Sesuai. Tidak menghitung ulang saldo di view dan tidak mengambil agregasi keuangan melalui query tambahan.
- Konfirmasi dialog lokal untuk pencatatan/keputusan pembayaran, pickup, tutup/review shift. Escape, backdrop dan Batal tidak mengirim transaksi; menerima konfirmasi mengirim formulir asli dengan CSRF/method/nama field yang sama. Tanpa JS, endpoint asli dan validasi server tetap berlaku. Aksi biasa seperti pencarian, tambah pesanan, mulai proses dan tandai siap tidak dipaksa melewati modal.
- Komponen Blade baru: KPI, status-badge (order/payment/shift), empty-state, section-header, form-feedback, field-error, select, pagination, service-row dan confirm-dialog. CSS terpusat dan JS lokal melalui Vite; tidak ada library chart, CDN, atau API baru.

Tidak mengubah controller, route, middleware, model, validasi server, isolasi usaha/cabang, perhitungan keuangan, database/migration atau seeder.

## Ketidaksesuaian data yang dicatat untuk PR lanjutan

1. `Service.minimum_weight` disimpan tetapi `OrderController::store` belum menggunakan atribut tersebut untuk batas/tagihan minimum. Tahap B tidak menambah batas atau minimum tagihan di browser. Tes campuran sengaja membuktikan perilaku lama tetap berlaku untuk 2,5 kg pada layanan dengan minimum tersimpan 3 kg. Keputusan bisnis dan perbaikannya perlu PR terpisah.
2. `CashShiftController::index` hanya memberikan paginator shift dan model shift terbuka, tanpa nominal penerimaan tunai langsung. Tahap B tidak menambahkan query/sum atau mengarang angka nol di view. Kas masuk ditandai “Rincian belum tersedia”; saldo seharusnya/selisih shift terbuka dijelaskan akan tersedia setelah penutupan. Riwayat memakai `expected_cash`, `closing_cash`, dan `difference` yang telah disimpan server. Penyediaan rincian live membutuhkan PR data/controller terpisah.
3. Controller dashboard belum menyediakan filter daftar berdasarkan “belum lunas” atau “terlambat”. CTA mengarah ke daftar pesanan yang memang ada, bukan menambahkan parameter filter yang tidak didukung. Tidak ada filter cabang baru yang mengubah scope.

## UX dan aksesibilitas

- Visibility: KPI dengan konteks, badge teks, hasil pencarian, timeline, informasi kas dan feedback; bukan warna saja.
- Bahasa operasional: label Indonesia, bantuan kg/barang dan informasi pemisahan tunai/digital.
- Kendali/pencegahan kesalahan: konfirmasi hanya untuk tindakan penting, cancel tidak submit, fokus kembali, input lama tetap tersedia, dan summary tanpa kalkulasi berbeda.
- Konsistensi/recognition: tokens dan ikon Tahap A, field/section/badge reusable, kelompok informasi yang dapat dipindai.
- Efisiensi/minimalisme: kartu pesanan mobile, pagination tetap pada query, tanpa chart dekoratif atau tombol tanpa endpoint.
- Pemulihan/bantuan: pesan dekat field dan summary error, placeholder kosong yang bermakna, petunjuk pelunasan/shift, serta riwayat penolakan tetap terlihat.
- Fokus terlihat, semantik heading/fieldset/label/caption/dl/ol, dialog berlabel dengan fokus terkurung, tautan bantuan bergaris bawah, target kontrol memadai. Transisi maksimal 160ms, reduced motion meniadakan animasi dialog. Dialog tidak memakai fade opacity agar teks terbaca sejak awal.

## Hasil verifikasi sebenarnya

| Pemeriksaan | Hasil |
|---|---|
| `npm ci` | Berhasil, dependensi memakai lockfile main; tidak menambah dependensi |
| `npm run build` | Berhasil, Vite 7.3.7; CSS sekitar 63,47 kB (gzip 14,91 kB), JS 67,28 kB (gzip 24,85 kB) |
| `php artisan test` | **58 lulus, 517 assertions** pada PHP 8.5.12/MariaDB 11, `laundryku_testing` terisolasi |
| `php artisan view:cache` | Berhasil; perintah kompilasi view sesuai spesifikasi |
| `git diff --check` | Berhasil |
| StageBPresentationTest | 7 tes: angka dashboard/controller dan isolasi cabang; filter/pagination/record mobile; old input indeks layanan; harga/durasi server untuk kg+barang; aksi state/role/nota; saldo tersimpan tanpa agregasi view; pesanan dibatalkan tanpa aksi pembayaran baru |
| Regresi | Seluruh tes sebelumnya tetap dijalankan: pembayaran/shift/retry/rekonsiliasi, otorisasi/cabang, laporan/ekspor, login/setup dan nota |
| Browser responsif | 6 URL x 2 peran x 375/768/1280px + 3 kasus perpindahan halaman = **39 kombinasi** tanpa overflow root tak disengaja; tabel lebar memakai area geser |
| axe-core | **21 keadaan** halaman/dialog diperiksa untuk WCAG 2 A/AA, 2.1 AA dan 2.2 AA, **0 pelanggaran** pada hasil akhir |
| JavaScript | Tidak ada error pada rangkaian browser yang dijalankan |

Rincian mesin: [browser-results.json](browser-results.json).

### Alur browser yang dijalankan (Playwright/Chromium)

1. Login Pemilik/Kasir; perubahan cabang Pemilik membersihkan pelanggan yang tidak sesuai tanpa mengambil data di luar controller.
2. Pencarian + status GET dan reset, kartu mobile dan empty state.
3. Tambah/hapus layanan, indeks 0/2/3 tidak bertabrakan, batas 20 baris.
4. Form invalid dengan layanan duplikat: pelanggan, jumlah 2,5/1,25 dan catatan tetap tersedia; field layanan ditandai error.
5. Perbaikan menjadi 2,5 kg × Rp8.000 + 2 barang × Rp15.000; server menyimpan Rp50.000 dan durasi terlama 72 jam. Ringkasan browser tidak menghitung total uang.
6. Pencatatan tunai: focus trap, Escape/Batal tanpa request; konfirmasi mengirim tepat satu request.
7. Proses → siap → pickup melalui endpoint asli; pickup hanya ditawarkan setelah lunas dan dikonfirmasi.
8. Receipt 58/80mm: emulasi print menyembunyikan aksi, lebar fisik sesuai, tanpa shell.
9. Retry QRIS oleh Kasir tetap pending, alasan lama/referensi terlihat, tidak ada aksi konfirmasi Pemilik pada layar Kasir.
10. Pemilik menolak dengan alasan/konfirmasi (cancel tidak mengubah pending); Kasir mengajukan transfer ulang; Pemilik mengonfirmasi. Semua alasan/referensi percobaan terdahulu tetap ada.
11. Fixture cash historis ditinjau eksplisit melalui halaman rekonsiliasi lama agar guard yang ada tetap dihormati.
12. Tutup shift: cancel mempertahankan tunai aktual; konfirmasi menghasilkan saldo seharusnya Rp150.000 (saldo awal Rp100.000 + tunai baru Rp50.000), selisih nol. Digital tidak masuk kas. Pemilik melakukan review tanpa perubahan nominal.
13. Reduced motion menonaktifkan animasi konfirmasi.
14. Nomor halaman langsung tetap tersedia bersama tombol sebelumnya/berikutnya. Pindah ke halaman 2 mempertahankan search/status dan tidak meluber pada 375/768/1280px; current page berlabel aksesibel.

Screenshot dan alur browser memakai data sintetis pada database uji, bukan angka hardcoded di produk. Database utama tidak diakses, tidak menjalankan perintah migrasi manual, dan tidak mengubah seeder. RefreshDatabase menyiapkan skema/fixture hanya pada laundryku_testing yang dilindungi TestCase.

Ini bukan sertifikasi seluruh aplikasi. Pembaca layar, pengguna laundry, printer fisik, browser/perangkat selain Chromium, dan konkurensi multiproses belum diuji. Scope otomatis tercantum dalam JSON; modul di luar Tahap B tetap pekerjaan Tahap C.

## Screenshot sebelum/sesudah

JPEG asli hasil browser, desktop 1280x900 dan mobile 375x812, full-page kecuali dialog. Semua fixture adalah demo. ID/waktu dapat berbeda akibat reset pengujian, tetapi sumber data pada layar tetap controller yang sama.

| Halaman | Sebelum desktop | Sesudah desktop | Sebelum mobile | Sesudah mobile |
|---|---|---|---|---|
| Dashboard Pemilik | [Lihat](screenshots/before/dashboard-owner-desktop.jpg) | [Lihat](screenshots/after/dashboard-owner-desktop.jpg) | [Lihat](screenshots/before/dashboard-owner-mobile.jpg) | [Lihat](screenshots/after/dashboard-owner-mobile.jpg) |
| Dashboard Kasir | [Lihat](screenshots/before/dashboard-cashier-desktop.jpg) | [Lihat](screenshots/after/dashboard-cashier-desktop.jpg) | [Lihat](screenshots/before/dashboard-cashier-mobile.jpg) | [Lihat](screenshots/after/dashboard-cashier-mobile.jpg) |
| Pesanan berisi | [Lihat](screenshots/before/orders-desktop.jpg) | [Lihat](screenshots/after/orders-desktop.jpg) | [Lihat](screenshots/before/orders-mobile.jpg) | [Lihat](screenshots/after/orders-mobile.jpg) |
| Pencarian kosong | [Lihat](screenshots/before/orders-empty-desktop.jpg) | [Lihat](screenshots/after/orders-empty-desktop.jpg) | [Lihat](screenshots/before/orders-empty-mobile.jpg) | [Lihat](screenshots/after/orders-empty-mobile.jpg) |
| Tambah pesanan | [Lihat](screenshots/before/order-create-desktop.jpg) | [Lihat](screenshots/after/order-create-desktop.jpg) | [Lihat](screenshots/before/order-create-mobile.jpg) | [Lihat](screenshots/after/order-create-mobile.jpg) |
| Detail/pembayaran | [Lihat](screenshots/before/order-detail-desktop.jpg) | [Lihat](screenshots/after/order-detail-desktop.jpg) | [Lihat](screenshots/before/order-detail-mobile.jpg) | [Lihat](screenshots/after/order-detail-mobile.jpg) |
| Shift Kasir | [Lihat](screenshots/before/shifts-desktop.jpg) | [Lihat](screenshots/after/shifts-desktop.jpg) | [Lihat](screenshots/before/shifts-mobile.jpg) | [Lihat](screenshots/after/shifts-mobile.jpg) |

Keadaan tambahan: [validasi form](screenshots/after/create-validation-mobile.jpg), [konfirmasi](screenshots/after/confirmation-mobile.jpg), [riwayat pembayaran](screenshots/after/payment-history-mobile.jpg), [shift ditutup](screenshots/after/shift-closed-mobile.jpg). Total **32 screenshot**.
