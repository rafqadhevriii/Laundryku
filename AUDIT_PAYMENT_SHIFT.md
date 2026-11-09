# Temuan Audit Alur Pembayaran dan Shift

Tanggal: 2026-11-09
Sumber: pemeriksaan kode Laravel dan hasil tes otomatis.

## Pemeriksaan
- php artisan test --compact: 20 tes lulus, 105 assertions.
- File diperiksa: PaymentController.php, CashShiftController.php, OrderController.php, CustomerController.php, BranchIsolationTest.php.
- Temuan ini belum diperbaiki. Tes yang lulus tidak menutup seluruh kasus di bawah.

## Prioritas tinggi: perhitungan kas shift tumpang tindih
Lokasi: app/Http/Controllers/CashShiftController.php, method close.
Saat penutupan, sistem menjumlah seluruh pembayaran tunai di cabang selama waktu shift, tanpa mengikat pembayaran ke shift/kasir tertentu.
Risiko: dua kasir dengan shift yang waktunya tumpang tindih dapat menghitung transaksi yang sama ke dua shift.
Kriteria perbaikan:
- Pembayaran tunai terhubung secara jelas ke shift kasir yang menerima uang.
- Jika satu cabang hanya boleh satu shift aktif, terapkan aturan itu secara eksplisit dan uji konkurensi.
- Penutupan shift tidak menghitung pembayaran milik shift lain.
- Tambahkan tes untuk dua kasir dan rentang shift tumpang tindih.
- Tetapkan aturan jika transaksi tunai dilakukan saat shift belum dibuka.

## Prioritas sedang: pembayaran digital ditolak
Lokasi: app/Http/Controllers/PaymentController.php, method store.
Jika pembayaran sudah berstatus rejected, kode saat ini hanya mengizinkan pemilik mencatat ulang.
Keputusan bisnis yang perlu dipastikan: apakah kasir boleh mengajukan ulang setelah penolakan? Jika iya, batasi pengajuan ulang hanya untuk pembayaran rejected, tetap memerlukan konfirmasi pemilik, dan catat audit log.
Tambahkan tes pengajuan ulang, penolakan berulang, dan pencegahan pembayaran ganda.

## Pemeriksaan akses
- OrderController, PaymentController, CustomerController menggunakan filter business_id dan branch_id untuk kasir.
- BranchIsolationTest mencakup akses pesanan/nota, pelanggan, dan stok lintas cabang.
- Perlu memperluas tes pembayaran lintas cabang dan pengguna tidak aktif.

## Arahan untuk Codex
Baca AGENTS.md dan dokumen ini. Kerjakan perbaikan secara bertahap dalam branch terpisah dan ajukan pull request. Jangan mengubah database utama. Jalankan seluruh tes pada laundryku_testing dan perbarui PROGRESS.md serta CHANGELOG.md.
