# Status proyek Laundryku

Diperbarui: 2026-11-09. Perbaikan pengaitan pembayaran tunai ke shift.

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
- Shift terbuka dengan pembayaran tunai lama tanpa ID shift dalam periode/cabangnya ditolak penutupannya agar tidak melaporkan total yang kurang. Data ambigu perlu rekonsiliasi manual yang ditinjau Pemilik; belum tersedia alat rekonsiliasi pada UI.

## Verifikasi terakhir
- `php artisan test --compact`: **29 tes lulus, 220 assertions**, dijalankan 2026-11-09.
- `php artisan view:cache`: berhasil.
- `git diff --check`: berhasil.
- Lingkungan: PHP 8.5.12, MariaDB 11 melalui driver mysql; database `laundryku_testing` pada container terisolasi. Tidak ada koneksi ke database utama.
- Bukti regresi: mengembalikan sementara perhitungan penutupan lama membuat tes shift tumpang tindih gagal (ekspektasi 124000, aktual 148000). Kode perbaikan dipulihkan dan seluruh tes kemudian lulus.
- Sembilan tes baru pada `CashPaymentShiftTest`: shift tumpang tindih dua kasir, penolakan tanpa shift sendiri, shift berurutan pada waktu sama, pemilihan shift Pemilik lintas usaha/cabang dan status tertutup, isolasi cabang, pembayaran digital, data historis, pilihan UI, dan penggantian pembayaran ditolak oleh Pemilik. Tes lama disesuaikan untuk membuka shift sebelum menerima tunai.
- Tes tumpang tindih menggunakan permintaan berurutan dengan waktu dibekukan; belum merupakan uji beban/konkurensi multiproses. Pengujian browser dan printer belum dilakukan.

## Penerapan setelah PR ditinjau
- Migrasi baru hanya disiapkan dalam kode; tidak dijalankan pada database utama.
- Saat penerapan terjadwal, hentikan sementara pencatatan pembayaran dan tutup/review shift lama melalui versi aplikasi lama sebelum menjalankan migrasi tambahan sesuai prosedur backup dan review setempat.
- Migrasi menambah foreign key nullable, tanpa UPDATE/DELETE pembayaran historis. Jika masih ada shift lama terbuka, jangan menebak hubungan berdasarkan waktu; rekonsiliasi lebih dahulu.
- File terkait: PaymentController, CashShiftController, OrderController, model Payment/CashShift, view orders/show, migrasi `2026_11_09_150000_add_cash_shift_id_to_payments_table.php`, CashPaymentShiftTest, dan OperationalFlowTest.

## Prioritas berikutnya
- Rencana Black Box sudah disusun dalam BLACKBOX_TEST_PLAN.md (25 skenario). Pengujian manual dan bukti belum dilakukan.
- Audit awal pembayaran dan shift didokumentasikan di AUDIT_PAYMENT_SHIFT.md. Pengaitan pembayaran tunai ke shift sudah diperbaiki pada branch ini; keputusan perluasan hak Kasir untuk pengajuan ulang pembayaran digital ditolak tetap belum diubah.
- Perluasan pengujian akses lintas cabang dan pengguna tidak aktif.
- Penyempurnaan UI/UX dan validasi alur kerja bersama pengguna laundry.

## Sinkronisasi
- Codex Website bekerja melalui GitHub. Setelah perubahan Codex digabung di GitHub, tarik perubahan ke folder lokal dengan Git, lalu minta ChatGPT memeriksa file dan tes.
- Perubahan pada folder lokal tidak otomatis muncul di Codex Website sebelum di-commit dan di-push.
- Jangan menulis klaim 'lulus' sebelum benar-benar menjalankan pengujian.
