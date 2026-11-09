# Panduan kerja Codex untuk Laundryku

## Konteks
Laundryku adalah proyek Tugas Akhir D3 Manajemen Informatika: sistem informasi monitoring operasional dan pendapatan laundry berbasis web, studi kasus Khayalaba Laundry.
Stack: Laravel 12, Blade, MySQL/MariaDB, Laragon Windows, PHP, Composer, Vite.
Folder lokal: C:\laragon\www\Laundryku. Antarmuka berbahasa Indonesia.

## Aturan bisnis yang wajib dijaga
- Peran Pemilik dan Kasir. Pemilik mengelola beberapa cabang; Kasir hanya mengakses cabang yang ditugaskan. Petugas laundry dan pelanggan tidak perlu login.
- Setiap query dan perubahan data harus dibatasi business_id dan branch_id sesuai hak akses.
- Alur pesanan: diterima, diproses, siap diambil, sudah diambil. Pesanan tidak boleh diambil sebelum lunas.
- Pembayaran penuh sekali bayar, tunai/transfer/QRIS. Pembayaran digital manual perlu konfirmasi Pemilik; cegah pembayaran ganda.
- Koreksi stok oleh Kasir menunggu persetujuan Pemilik.
- Shift kasir mencatat saldo awal, saldo akhir, selisih, dan review Pemilik.
- Catat tindakan sensitif ke audit log. Pertahankan format nota dan laporan.

## Aturan perubahan kode
1. Baca file terkait dan tes yang ada sebelum mengubahnya. Jangan menghapus fitur yang sudah berfungsi.
2. Jangan jalankan migrate:fresh, db:wipe, seed destruktif, atau RefreshDatabase pada database utama.
3. Pengujian otomatis memakai database terpisah laundryku_testing; periksa phpunit.xml sebelum menjalankan tes.
4. Jangan commit .env, database lokal, kredensial, file backup, vendor, node_modules, atau data pelanggan asli.
5. Untuk pekerjaan besar gunakan branch Git terpisah, lakukan perubahan kecil yang bisa ditinjau, dan jalankan php artisan test --compact serta php artisan view:cache.
6. Setelah pekerjaan selesai, perbarui PROGRESS.md dan CHANGELOG.md: perubahan, file, hasil pengujian, keterbatasan, dan langkah selanjutnya.
7. Jika kebutuhan tidak jelas atau berisiko merusak data, tanyakan dulu.
