# Status proyek Laundryku

Diperbarui: 2026-11-09. Rekonsiliasi historis dan pengajuan ulang digital.

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

## Verifikasi terakhir
- `php artisan test --compact`: **46 tes lulus, 401 assertions**, dijalankan 2026-11-09 pada PHP 8.5.12/MariaDB 11, driver mysql, database `laundryku_testing` di container terisolasi.
- `php artisan view:cache`: berhasil. `git diff --check`: berhasil.
- Tambahan: PaymentReconciliationTest (10 tes), DigitalPaymentRetryTest (7 tes), serta fixture bersama. Tes mencakup otorisasi, isolasi usaha/cabang, filter, alasan wajib, periode shift, shift final, duplikasi, audit, pengecualian dan blokir penutupan, riwayat penolakan berulang, pending/paid, nominal, pendapatan, dan pengguna nonaktif. Tes lama tunai/shift/laporan/ekspor tetap dijalankan; skenario retry Pemilik disesuaikan dengan aturan digital-only.
- Browser otomatis Playwright + Chromium berhasil: halaman Pemilik tanpa shift terpilih otomatis, validasi alasan wajib, pengecualian dan laporan, retry QRIS oleh Kasir dengan riwayat lama terlihat, akses halaman Pemilik ditolak 403 untuk Kasir, serta konfirmasi ulang Pemilik menjadi lunas. Tidak ada error JavaScript.
- Pengujian hanya menggunakan data sintetis. Database utama tidak diakses. Tidak menjalankan perintah migrasi manual; persiapan skema oleh RefreshDatabase hanya pada database pengujian. `tests/TestCase.php` memeriksa environment dan nama database sebelum trait tersebut berjalan; phpunit memaksa APP_ENV=testing dan DB_URL kosong.
- Pengujian manual pengguna/printer dan uji konkurensi multiproses belum dilakukan. Dokumentasi skenario dan hasil otomatis ada pada BLACKBOX_TEST_PLAN.md; hasil otomatis tidak menggantikan pengujian manual TA.

## Penerapan setelah PR ditinjau
- Migrasi tambahan `2026_11_09_160000_add_reconciliation_and_payment_attempts.php` hanya menambah kolom nullable dan tabel riwayat. Tidak ada backfill, UPDATE, atau DELETE data nyata pada up(). Migrasi tidak dijalankan pada database utama.
- Sebelum penerapan terjadwal, lakukan backup dan review skema sesuai prosedur setempat. Hentikan sementara transaksi selama penerapan kode/skema bersama; jangan menggunakan kode baru sebelum skema yang dibutuhkan tersedia.
- Pemilik memeriksa pembayaran lama melalui halaman rekonsiliasi. Pengaitan ke shift final sengaja tidak tersedia; pengecualian eksplisit menyimpan alasan dan tidak mengubah saldo final. Koreksi saldo final terpisah belum diimplementasikan.
- File utama: PaymentReconciliationController, PaymentController, CashShiftController, OrderController, Payment/PaymentAttempt, routes/web.php, view rekonsiliasi/detail pesanan/layout, migrasi tambahan, tests/Feature, tests/Support/PaymentFixtures, phpunit.xml, dan tests/TestCase.php.

## Prioritas berikutnya
- Implementasi SPEC_RECONCILIATION_RETRY.md selesai pada branch feat/reconciliation-payment-retry; menunggu review PR, belum digabung otomatis.
- Rencana Black Box sudah disusun dalam BLACKBOX_TEST_PLAN.md (25 skenario). Pengujian manual dan bukti belum dilakukan.
- Audit awal pembayaran dan shift didokumentasikan di AUDIT_PAYMENT_SHIFT.md. Pengaitan pembayaran tunai, rekonsiliasi historis, dan retry digital sudah ditangani; status terbaru ditambahkan pada dokumen audit.
- Perluasan pengujian akses lintas cabang dan pengguna tidak aktif.
- Penyempurnaan UI/UX dan validasi alur kerja bersama pengguna laundry.

## Sinkronisasi
- Codex Website bekerja melalui GitHub. Setelah perubahan Codex digabung di GitHub, tarik perubahan ke folder lokal dengan Git, lalu minta ChatGPT memeriksa file dan tes.
- Perubahan pada folder lokal tidak otomatis muncul di Codex Website sebelum di-commit dan di-push.
- Jangan menulis klaim 'lulus' sebelum benar-benar menjalankan pengujian.
