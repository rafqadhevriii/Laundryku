# Catatan perubahan Laundryku

## 2026-11-09 (rekonsiliasi historis dan retry digital)
- Menambahkan halaman khusus Pemilik untuk pembayaran tunai historis: filter cabang/tanggal/nomor, pengaitan eksplisit ke shift terbuka yang sesuai, pengecualian belum teralokasi dengan alasan wajib, ringkasan, dan laporan keputusan.
- Setiap keputusan direkam dengan aktor/waktu/alasan/status sebelum-sesudah/shift tujuan. Pengaitan ganda dan pengaitan ke shift final ditolak; saldo final tidak dihitung ulang. Pengecualian ditinjau tidak lagi memblokir penutupan dan tetap tidak masuk kas shift. Pendapatan tetap menghitung pembayaran lunas sekali.
- Mengizinkan retry rejected oleh Kasir dalam cabangnya dan Pemilik dalam usahanya. Sesuai spesifikasi, hanya transfer/QRIS, selalu pending, nominal dari total pesanan; konfirmasi ulang Pemilik tetap wajib. Endpoint lama juga menerapkan aturan ini, termasuk menolak penggantian rejected menjadi tunai.
- Menambahkan riwayat payment_attempts untuk menjaga referensi, catatan awal, alasan penolakan, aktor dan waktu setiap percobaan. Snapshot pembayaran lama dibuat saat ditindaklanjuti tanpa menebak riwayat yang sudah tidak tersedia. Tampilan pesanan mempertahankan alasan penolakan dan riwayat setelah retry.
- Migrasi tambahan hanya disiapkan: kolom rekonsiliasi nullable dan tabel percobaan, tanpa backfill/perubahan/penghapusan data nyata. Tidak menjalankan migrasi manual atau mengakses database utama.
- File: controller PaymentReconciliation/Payment/CashShift/Order, model Payment/PaymentAttempt, migrasi 160000, routes, view payments/reconciliation dan orders/show/layout, PaymentReconciliationTest, DigitalPaymentRetryTest, CashPaymentShiftTest, PaymentFixtures, pengaman TestCase/phpunit, PROGRESS, AUDIT_PAYMENT_SHIFT, SPEC_RECONCILIATION_RETRY, dan BLACKBOX_TEST_PLAN.
- Hasil sebenarnya: **46 tes lulus, 401 assertions** pada laundryku_testing terisolasi (PHP 8.5.12/MariaDB 11), view:cache dan diff --check berhasil. Uji browser otomatis Playwright/Chromium berhasil untuk rekonsiliasi, validasi alasan, retry, riwayat, akses Kasir 403, dan konfirmasi ulang.
- Keterbatasan: belum diuji manual oleh pengguna/printer atau konkurensi multiproses; pengaitan/koreksi saldo shift final tidak tersedia. Lanjutkan review PR, pengujian pengguna, dan rencana penerapan; jangan merge otomatis.

## 2026-11-09 (perbaikan pembayaran tunai dan shift)
- Mengikat setiap pembayaran tunai baru ke satu `cash_shift_id`. Kasir harus memiliki shift aktif sendiri; Pemilik memilih shift aktif pada usaha/cabang pesanan.
- Penutupan menjumlah pembayaran berdasarkan ID shift, sehingga shift kasir tumpang tindih tidak menghitung uang yang sama. Penguncian baris shift menyelaraskan pembayaran dengan penutupan; perlindungan pembayaran ganda tetap berlaku.
- Migrasi tambahan menambah foreign key nullable tanpa mengubah/menghapus pembayaran historis atau menghitung ulang shift tertutup. Penutupan shift lama yang masih memiliki pembayaran tanpa kaitan ditolak untuk mencegah kekurangan perhitungan. Tidak menjalankan migrasi pada database utama.
- File: PaymentController, CashShiftController, OrderController, Payment, CashShift, orders/show.blade.php, migrasi add_cash_shift_id_to_payments_table, CashPaymentShiftTest, OperationalFlowTest, PROGRESS.md.
- Validasi sebenarnya: `php artisan test --compact` **29 lulus, 220 assertions** pada `laundryku_testing` terisolasi (PHP 8.5.12/MariaDB 11); `php artisan view:cache` dan `git diff --check` berhasil. Tes regresi terbukti gagal dengan perhitungan shift lama (148000 dibanding 124000 yang semestinya).
- Keterbatasan: belum diuji browser/printer atau konkurensi multiproses; data historis ambigu tidak dipasangkan otomatis. Pengeluaran belum diikat ke kas shift; keputusan pengajuan ulang digital oleh Kasir tidak diubah.
- Berikutnya: review PR, uji manual dua kasir/cabang dan skenario penerapan; tutup/review shift lama pada masa pemeliharaan sebelum migrasi. Detail penerapan dan rekonsiliasi ada di PROGRESS.md.

## 2026-11-09 (spesifikasi lanjutan)
- Menyusun SPEC_RECONCILIATION_RETRY.md untuk pekerjaan Codex berikutnya. Belum ada perubahan logika aplikasi.

## 2026-11-09 (audit lanjutan)
- Menjalankan tes otomatis: 20 lulus, 105 assertions.
- Menambahkan AUDIT_PAYMENT_SHIFT.md untuk mencatat risiko shift tumpang tindih dan pengajuan ulang pembayaran digital.
- Belum ada perubahan kode bisnis.

## 2026-11-09 (lanjutan)
- Menambahkan BLACKBOX_TEST_PLAN.md berisi 25 skenario pengujian manual dan format pencatatan hasil untuk TA.
- Memperbarui PROGRESS.md; pengujian manual belum dilakukan.

## 2026-11-09
- Menyiapkan panduan AGENTS.md dan PROGRESS.md untuk kerja bersama ChatGPT dan Codex.
- Menyiapkan repository Git lokal dan perlindungan file database lokal.
- Fitur sebelumnya: dashboard, laporan pendapatan PDF/Excel/CSV, laporan operasional Excel, pengujian otorisasi, cabang, pembayaran, shift, dan stok.

## Panduan pembaruan
Setiap pekerjaan Codex harus menambahkan tanggal, perubahan penting, file terkait, hasil pengujian, dan masalah yang belum selesai di sini.
