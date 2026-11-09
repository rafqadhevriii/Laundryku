# Status proyek Laundryku

Diperbarui: 2026-11-09. Sistem konteks cabang aktif.

## Fitur yang sudah dikerjakan
- Login dan hak akses Pemilik/Kasir, pemisahan usaha dan cabang.
- Manajemen Kasir, layanan, pelanggan, pesanan, status, pembayaran manual.
- Shift kasir, pengeluaran, inventaris, koreksi stok dengan persetujuan.
- Dashboard: pesanan terlambat, siap diambil, stok menipis.
- Nota cetak, audit log.
- Laporan pendapatan PDF, CSV, Excel.
- Laporan operasional dan ekspor Excel.

## Pembayaran tunai dan shift
- Pembayaran tunai baru menyimpan `payments.cash_shift_id`; kasir wajib mempunyai shift aktif sendiri pada usaha/cabang pesanan.
- Shift beberapa kasir boleh tumpang tindih. Pemilik tetap dapat menerima tunai dengan memilih shift aktif di cabang pesanan; `recorded_by` tetap menyimpan aktor pencatat.
- Penutupan hanya menjumlah pembayaran tunai lunas milik ID shift tersebut. Pembayaran dan penutupan mengunci baris shift yang sama untuk mencegah pembayaran masuk setelah shift ditutup.
- Unique `order_id`, penguncian pesanan, alur pembayaran digital, konfirmasi Pemilik, dan audit log dipertahankan. ID shift dicatat pada audit pembayaran.
- Pembayaran historis tetap utuh dengan ID shift NULL, tanpa backfill berdasarkan tebakan waktu. Total shift yang sudah ditutup tidak dihitung ulang.
- Shift terbuka tetap diblokir jika masih ada pembayaran tunai historis belum ditinjau dalam periode/cabangnya. Keputusan eksplisit Pemilik “historis belum teralokasi” membuka blokir tanpa memasukkan uang itu ke kas shift.

## Rekonsiliasi historis dan pengajuan ulang digital
- Halaman Pemilik: `/owner/payment-reconciliation`, dengan filter cabang, tanggal bayar, nomor pesanan, dan status peninjauan; daftar hanya memuat tunai lunas tanpa shift dari usaha Pemilik.
- Pengaitan memerlukan pilihan shift dan alasan/bukti. Hanya shift **open**, usaha/cabang sama, dan waktu pembayaran antara pembukaan shift dan waktu sekarang yang diterima. Tidak ada pilihan atau atribusi otomatis. Shift submitted/reviewed tidak diubah atau dihitung ulang.
- Pembayaran ambigu dapat ditandai `unallocated` dengan alasan wajib. Nominal, metode, status lunas, dan pendapatan tidak berubah. Pengecualian terlihat pada ringkasan dan laporan keputusan; tidak masuk kas shift. Bukti baru dapat mengubah pengecualian menjadi pengaitan valid selama shift masih terbuka.
- Pengaitan hanya sekali; tidak tersedia pindah/hapus kaitan. Transaksi dan lock shift/payment menyelaraskan keputusan dengan penutupan. Setiap keputusan menyimpan aktor, waktu, alasan, status sebelum/sesudah, dan tujuan dalam audit log. Keputusan terdahulu tidak ditimpa di audit.
- Kasir dapat mengajukan ulang rejected dalam usaha/cabangnya; Pemilik untuk seluruh cabang usahanya. Sesuai spesifikasi, pengajuan ulang hanya transfer/QRIS (termasuk melalui endpoint pencatatan lama), selalu pending, dan jumlah berasal dari total pesanan. Penggantian rejected menjadi tunai tidak lagi diizinkan. Pending/paid dan pengajuan ulang tanpa pembayaran ditolak tidak diterima.
- Tabel `payment_attempts` menyimpan nomor percobaan, pencatat/peninjau, referensi, catatan awal, alasan penolakan terpisah, jumlah, status, dan waktu. Konfirmasi/penolakan hanya memperbarui percobaan pending saat ini; percobaan sebelumnya dipertahankan. Pembayaran tetap satu baris per pesanan sehingga laporan pendapatan tidak mengganda.
- Pembayaran lama tanpa percobaan disalin sebagai snapshot saat ditindaklanjuti, bukan saat migrasi. Snapshot memakai data/waktu yang masih tersedia; riwayat lebih lama yang sudah tertimpa oleh versi lama tidak direkonstruksi. Audit log lama tetap utuh.
- Detail pesanan menampilkan alasan penolakan, formulir pengajuan ulang, status terkini, dan riwayat percobaan. Operasi pembayaran menolak akun/usaha/cabang Kasir yang tidak aktif.

## UI/UX Tahap A
- Branch `feat/uiux-stage-a` dari main a04ef7d: identitas orisinal pakaian/gelembung, logo SVG terang/gelap, simbol dan favicon lokal.
- Design tokens navy/teal/putih, font sistem lokal, hirarki teks, fokus dan kontrol konsisten. Teal gelap dipakai untuk teks/tombol agar kontras memadai.
- Layout menggunakan komponen Blade untuk head, brand, ikon, tombol, alert, field, sidebar/menu dan topbar. Menu dikelompokkan sesuai peran dan tetap memakai route lama; indikator aktif memiliki bentuk/teks/aria-current selain warna.
- Sidebar mobile berbasis dialog: Enter/Tab, fokus terkurung, Escape/backdrop/tombol tutup, pengembalian fokus, dan fallback details tanpa JS. Identitas usaha/cabang/peran serta logout dipertahankan.
- Login/setup selaras identitas visual: label dan pesan error terasosiasi, old input nonrahasia, petunjuk kontekstual, toggle password, dan feedback submit. Validasi server tidak diubah.
- CSS/JS lokal dibundel Vite, transisi 160ms menghormati prefers-reduced-motion; tabel lama diberi area geser berlabel saat meluber. Grup aksi filter membungkus agar tidak meluber.
- Tidak mengubah controller, route, middleware, model, kalkulasi, migration, seeder, atau CSS nota standalone. Tahap B/C untuk konten/modul lebih lanjut belum dikerjakan.

## UI/UX Tahap B
- Branch `feat/uiux-stage-b` dari main cca5d66. Dashboard memakai KPI, konteks periode/cabang dan CTA dari data controller, tanpa angka/tren/grafik buatan.
- Daftar pesanan mempertahankan GET search/status/query pagination, badge teks pengerjaan/pembayaran, tabel desktop, kartu mobile, pencarian kosong dan tautan detail.
- Form baru mengelompokkan pelanggan/cabang, layanan dan catatan. Old input/error terasosiasi dipertahankan, baris dinamis berindeks unik sampai 20, kg/barang mengikuti aturan yang ada. Ringkasan hanya menampilkan tarif/jumlah/durasi; total uang dan estimasi resmi tetap dihitung server setelah simpan.
- Detail menampilkan timeline status tanpa timestamp tahap buatan, rincian snapshot, pembayaran dan riwayat percobaan. Aksi mengikuti state/peran; pickup hanya ditawarkan setelah lunas. Nota 58/80mm tetap memakai route/print standalone.
- Shift menampilkan saldo tersimpan, status, catatan dan selisih Kurang/Lebih/Sesuai; tutup/review dikonfirmasi tanpa menghitung ulang uang di view.
- Komponen reusable KPI/badge/empty state/section/form/select/pagination/service-row/confirmation; CSS dan JS lokal melalui Vite. Dialog mendukung keyboard, Escape/backdrop/cancel, fokus kembali, dan reduced motion.
- Tidak mengubah controller, route, model, middleware, validasi, database/migration/seeder, isolasi usaha/cabang, atau perhitungan keuangan.
- Batas data untuk PR lanjutan: minimum_weight tersimpan belum diterapkan OrderController; kas masuk langsung belum disediakan controller halaman shift. Tidak menambah aturan minimum, agregasi/query view atau angka nol fiktif. UI shift menyatakan rincian belum tersedia sampai informasi penutupan tersedia. Rincian di docs/uiux/stage-b/README.md.

## Verifikasi terakhir
- npm ci dan npm run build berhasil (Vite 7.3.7, CSS 63,47 kB / gzip 14,91 kB; JS 67,28 kB / gzip 24,85 kB).
- `php artisan test`: **58 tes lulus, 517 assertions**, PHP 8.5.12/MariaDB 11 pada laundryku_testing terisolasi. Tujuh StageBPresentationTest ditambahkan; seluruh regresi lama tetap dijalankan.
- `php artisan view:cache` dan `git diff --check`: berhasil.
- Playwright/Chromium: 14 kelompok pemeriksaan alur termasuk form invalid/multi-layanan, pembayaran/cancel tepat satu request, progres status, retry/penolakan berulang, konfirmasi Pemilik, penutupan/review kas dan nota 58/80mm.
- Responsif 39 kombinasi (36 halaman/peran/lebar dan 3 kasus pagination) pada 375/768/1280 tanpa overflow root tak disengaja. axe-core pada 21 keadaan Tahap B/dialog: 0 pelanggaran pada tag WCAG A/AA/2.1AA/2.2AA yang diperiksa. Tidak ada error JS.
- 32 screenshot sebelum/sesudah dan hasil browser ada di docs/uiux/stage-b. Semuanya data sintetis dari controller pada database pengujian. Database utama tidak diakses/diubah, tidak menjalankan migrasi manual atau mengubah seeder.
- Belum diuji manual pengguna/pembaca layar/perangkat selain Chromium/printer fisik atau konkurensi multiproses. Hasil otomatis bukan sertifikasi seluruh aplikasi.

## Catatan penerapan fitur data sebelumnya (bukan perubahan Tahap A)
- Migrasi tambahan `2026_11_09_160000_add_reconciliation_and_payment_attempts.php` hanya menambah kolom nullable dan tabel riwayat. Tidak ada backfill, UPDATE, atau DELETE data nyata pada up(). Migrasi tidak dijalankan pada database utama.
- Sebelum penerapan terjadwal, lakukan backup dan review skema sesuai prosedur setempat. Hentikan sementara transaksi selama penerapan kode/skema bersama; jangan menggunakan kode baru sebelum skema yang dibutuhkan tersedia.
- Pemilik memeriksa pembayaran lama melalui halaman rekonsiliasi. Pengaitan ke shift final sengaja tidak tersedia; pengecualian eksplisit menyimpan alasan dan tidak mengubah saldo final. Koreksi saldo final terpisah belum diimplementasikan.
- File utama: PaymentReconciliationController, PaymentController, CashShiftController, OrderController, Payment/PaymentAttempt, routes/web.php, view rekonsiliasi/detail pesanan/layout, migrasi tambahan, tests/Feature, tests/Support/PaymentFixtures, phpunit.xml, dan tests/TestCase.php.

## Prioritas berikutnya
- Review PR Tahap B tanpa merge otomatis. Build aset melalui npm ci dan npm run build; tidak ada perubahan database untuk Tahap B.
- Tahap C: modul lain (stok/pelanggan/layanan/kasir/pengeluaran/laporan/audit), konsistensi form/tabel, pemeriksaan aksesibilitas menyeluruh dan pengujian pengguna.
- PR data/controller terpisah untuk keputusan minimum berat dan penyediaan rincian kas masuk langsung; jangan mencampur perubahan aturan bisnis ke PR desain.
- Pengujian manual TA tetap mengikuti BLACKBOX_TEST_PLAN.md; screenshot dan tes otomatis bukan bukti seluruh skenario manual.

## Sinkronisasi
- Codex Website bekerja melalui GitHub. Setelah perubahan Codex digabung di GitHub, tarik perubahan ke folder lokal dengan Git, lalu minta ChatGPT memeriksa file dan tes.
- Perubahan pada folder lokal tidak otomatis muncul di Codex Website sebelum di-commit dan di-push.
- Jangan menulis klaim 'lulus' sebelum benar-benar menjalankan pengujian.

## Konteks cabang aktif
- Branch `feat/active-branch-context` dari main `a431c9f`. Pemilik memilih Semua Cabang atau cabang aktif milik usahanya melalui header POST + CSRF; Kasir hanya melihat cabang penugasan.
- `ActiveBranchContext` dan middleware `ResolveActiveBranch` memvalidasi akun/usaha/penugasan serta sesi setiap request. Sesi dinamespace per akun/usaha dan direset login/logout/setup. Pilihan kedaluwarsa dibersihkan: GET kembali ke gabungan dengan peringatan, POST mutasi gagal tanpa menulis. Pengalih memakai whitelist redirect GET dan membuang old input saat cabang berubah.
- Query dashboard, pesanan/pembayaran/nota, pelanggan, stok/pengajuan koreksi, pengeluaran, shift, rekonsiliasi, laporan/ekspor mengikuti cabang sebelum pagination/agregasi. Semua Cabang mempertahankan agregat serta filter laporan historis; filter URL tidak dapat mengganti cabang aktif tertentu.
- Form Pesanan Baru tidak lagi memiliki dropdown cabang. Backend mengabaikan branch_id kiriman dan memakai konteks sah; pelanggan wajib satu cabang. Semua Cabang meminta pemilihan header sebelum pembuatan pesanan/pelanggan/stok/pengeluaran. Update historis tidak memindahkan identitas cabang.
- File utama: service/middleware/controller pengalih, controller operasional dan auth/setup, routes/bootstrap, komponen header/branch-required, view formulir/laporan/layout, CSS dan order-composer JS, `ActiveBranchContextTest`, `docs/branch-context`.
- Hasil akhir: **69 tes / 671 assertion lulus**, database terisolasi `laundryku_testing`; Vite build, view:cache dan diff --check berhasil. Browser Chromium desktop 1440×1000/mobile 390×844: 10 screenshot, CSRF 419 tanpa token, navigasi/keyboard/pemisahan peran berhasil, nol error JS/overflow horizontal. Axe WCAG 2.x A/AA tidak menemukan pelanggaran pada 10 tampilan teruji.
- Tidak mengubah migration/model/skema, tidak menjalankan migrasi manual, dan tidak mengakses database utama. Perhitungan pembayaran/shift tidak diubah; tes snapshot memastikan nilai historis tetap sama.
- Batasan: halaman administrasi usaha (layanan/akun Kasir/audit umum) tetap lingkup usaha; bukan data operasional per cabang. Mode Semua Cabang tetap dapat menindak record historis tertentu sesuai izin Pemilik lama. Belum uji pembaca layar/perangkat fisik atau konkurensi beberapa tab; konteks berbagi dalam satu sesi browser. Berikutnya review PR dan UAT, tanpa merge otomatis.
