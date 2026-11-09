# Status proyek Laundryku

Diperbarui: 2026-11-09. Catatan awal untuk koordinasi ChatGPT dan Codex.

## Fitur yang sudah dikerjakan
- Login dan hak akses Pemilik/Kasir, pemisahan usaha dan cabang.
- Manajemen Kasir, layanan, pelanggan, pesanan, status, pembayaran manual.
- Shift kasir, pengeluaran, inventaris, koreksi stok dengan persetujuan.
- Dashboard: pesanan terlambat, siap diambil, stok menipis.
- Nota cetak, audit log.
- Laporan pendapatan PDF, CSV, Excel.
- Laporan operasional dan ekspor Excel.

## Verifikasi terakhir
- php artisan test --compact: 20 tests passed, 105 assertions (2026-11-09).
- Pengujian menggunakan MySQL laundryku_testing, terpisah dari database utama.
- Fitur tetap perlu diuji manual melalui browser dan perangkat printer jika digunakan.

## Prioritas berikutnya
- Rencana Black Box sudah disusun dalam BLACKBOX_TEST_PLAN.md (25 skenario). Pengujian manual dan bukti belum dilakukan.
- Pemeriksaan edge case shift kasir, stok, pembayaran, dan pembatasan akses.
- Penyempurnaan UI/UX dan validasi alur kerja bersama pengguna laundry.

## Sinkronisasi
- Codex Website bekerja melalui GitHub. Setelah perubahan Codex digabung di GitHub, tarik perubahan ke folder lokal dengan Git, lalu minta ChatGPT memeriksa file dan tes.
- Perubahan pada folder lokal tidak otomatis muncul di Codex Website sebelum di-commit dan di-push.
- Jangan menulis klaim 'lulus' sebelum benar-benar menjalankan pengujian.
