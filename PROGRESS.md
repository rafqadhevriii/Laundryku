# Status proyek Laundryku

Diperbarui: 2026-11-09. Redesign UI/UX Tahap A.

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

## Verifikasi terakhir
- `npm run build`: berhasil (Vite 7.3.7); public/build dihasilkan dan tetap diabaikan Git sesuai konfigurasi proyek. package-lock.json ditambahkan agar npm ci dapat direproduksi.
- `php artisan test`: **51 tes lulus, 456 assertions**, pada PHP 8.5.12/MariaDB 11, database laundryku_testing terisolasi. Lima tes UiShellTest menambah pemeriksaan aset lokal, old input/error login/setup, menu role, indikator aktif, akses Kasir, dan nota 58/80mm.
- `php artisan view:cache` dan `git diff --check`: berhasil.
- Playwright/Chromium: keyboard drawer (24 Tab), Escape/backdrop/fokus kembali, aria-expanded, toggle password, reduced motion, navigasi tanpa JS, dan validasi setup berhasil. Tidak ada error JavaScript pada pemeriksaan.
- Responsif: 13 URL x 375/768/1280px tanpa overflow root yang tidak disengaja. Login/setup diperiksa desktop dan mobile. Area tabel besar tetap dapat digeser.
- axe-core: 0 pelanggaran untuk login, dashboard Pemilik, dialog mobile, dan setup desktop/mobile pada tag WCAG A/AA/2.1AA/2.2AA yang diperiksa. Ini bukan sertifikasi seluruh aplikasi; pembaca layar, perangkat/browser lain, pengguna laundry, dan printer fisik belum diuji.
- 34 screenshot sebelum/sesudah (login, setup, dashboard Pemilik/Kasir, pesanan, pembayaran, stok, laporan, mobile/drawer/validasi) dan hasil mesin ada di docs/uiux/stage-a. Semuanya memakai fixture sintetis pada database pengujian; database utama tidak diakses/diubah, tidak menjalankan migrasi manual atau mengubah seeder.
- Detail desain, pemetaan 10 Nielsen Heuristics, kontras, keterbatasan, dan tautan screenshot: docs/uiux/stage-a/README.md.

## Catatan penerapan fitur data sebelumnya (bukan perubahan Tahap A)
- Migrasi tambahan `2026_11_09_160000_add_reconciliation_and_payment_attempts.php` hanya menambah kolom nullable dan tabel riwayat. Tidak ada backfill, UPDATE, atau DELETE data nyata pada up(). Migrasi tidak dijalankan pada database utama.
- Sebelum penerapan terjadwal, lakukan backup dan review skema sesuai prosedur setempat. Hentikan sementara transaksi selama penerapan kode/skema bersama; jangan menggunakan kode baru sebelum skema yang dibutuhkan tersedia.
- Pemilik memeriksa pembayaran lama melalui halaman rekonsiliasi. Pengaitan ke shift final sengaja tidak tersedia; pengecualian eksplisit menyimpan alasan dan tidak mengubah saldo final. Koreksi saldo final terpisah belum diimplementasikan.
- File utama: PaymentReconciliationController, PaymentController, CashShiftController, OrderController, Payment/PaymentAttempt, routes/web.php, view rekonsiliasi/detail pesanan/layout, migrasi tambahan, tests/Feature, tests/Support/PaymentFixtures, phpunit.xml, dan tests/TestCase.php.

## Prioritas berikutnya
- Review PR Tahap A tanpa merge otomatis. Instal dependensi dengan npm ci, lalu npm run build untuk aset produksi; tidak memerlukan perubahan database untuk Tahap A.
- Tahap B: desain isi dashboard/pesanan/detail/pembayaran/shift tanpa mengubah aturan bisnis. Tahap C: modul lain, audit aksesibilitas menyeluruh dan regresi.
- Pengujian manual TA tetap mengikuti BLACKBOX_TEST_PLAN.md; pengujian otomatis/screenshot bukan bukti seluruh skenario manual.
- Rekonsiliasi/retry digital sudah tersedia pada main terbaru; catatan fitur dan batas penerapan data sebelumnya dipertahankan di atas.

## Sinkronisasi
- Codex Website bekerja melalui GitHub. Setelah perubahan Codex digabung di GitHub, tarik perubahan ke folder lokal dengan Git, lalu minta ChatGPT memeriksa file dan tes.
- Perubahan pada folder lokal tidak otomatis muncul di Codex Website sebelum di-commit dan di-push.
- Jangan menulis klaim 'lulus' sebelum benar-benar menjalankan pengujian.
