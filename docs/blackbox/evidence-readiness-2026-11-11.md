# Rekap Bukti Otomatis dan Kesiapan Black Box Laundryku

Tanggal: 11 November 2026
Lingkungan aplikasi uji: http://127.0.0.1:8086/login
Database: laundryku_blackbox (bukan laundryku dan bukan laundryku_testing)

## Pemeriksaan teknis terbaru
Tes Laravel khusus keamanan, rekonsiliasi, retry digital, dan guard database: **38 tes lulus, 503 assertions**.
Perintah yang dijalankan:

```powershell
php artisan test --compact tests/Feature/PaymentReconciliationTest.php tests/Feature/DigitalPaymentRetryTest.php tests/Feature/BranchIsolationTest.php tests/Feature/BlackboxFixtureGuardTest.php tests/Unit/BlackboxSafetyTest.php
```

## Indeks bukti uji HTTP
Indeks CSV otomatis lokal di `storage/blackbox/evidence/automatic-evidence-index.csv` (44 catatan dari 12 berkas JSON; 28 ID skenario dua digit memiliki hasil pemeriksaan dengan kode ID tepat; 15 catatan ditandai sebagai cakupan sebagian oleh pengenal nama/isi). Angka tersebut **bukan** jumlah pengujian manual yang selesai dan tidak semuanya mewakili pemeriksaan menyeluruh tiap skenario. Beberapa pengujian memiliki ID tambahan untuk subkasus.

Semua catatan yang berhasil terindeks berstatus PASS untuk pemeriksaan yang dilakukannya. Ini bukan bukti bahwa semua kebutuhan skenario sudah terverifikasi.

## Yang masih memerlukan tindakan
- Pengaitan positif pembayaran tunai historis ke shift yang valid (BB-30), penandaan unallocated (BB-32), overlap (BB-34), rekonsiliasi lanjutan (BB-40, BB-49).
- Pengujian mutasi lintas usaha, sesi akun atau cabang yang dinonaktifkan (BB-37, BB-39).
- Pengajuan ulang QRIS penuh dan siklus penolakan lebih dari sekali (BB-36, BB-38).
- Pengujian desktop/mobile dengan screenshot, pengamatan validasi, dan pemeriksaan isi laporan serta nota fisik.
- Semua 52 skenario dalam `docs/blackbox/results-template.csv` masih bertuliskan `Belum diuji manual`. Jangan mengubah status otomatis menjadi hasil manual.

## Cara memulai pengujian manual
1. Buka http://127.0.0.1:8086/login. Gunakan akun uji yang informasinya ada di `storage/blackbox/LOCAL-LOGIN.txt`.
2. Ikuti urutan pada `docs/blackbox/execution-order.md` dan rincian langkah pada `docs/blackbox/manual-reconciliation-checklist.md`.
3. Simpan screenshot baru ke `storage/blackbox/evidence/manual/<ID>/`. Catat waktu/peran/hasil aktual dan bukti.
4. Ubah status baris CSV hanya setelah pemeriksaan manusia selesai dan screenshot tersedia. Bila terhalang, isi Terhambat beserta alasan, jangan Lulus.
5. Jangan me-reset database blackbox karena status fixture sudah berubah akibat pengujian otomatis. Jangan menjalankan migrasi destruktif pada database utama.

## Lokasi berkas
- Hasil HTTP mentah: `storage/blackbox/evidence/automated-http-*.json` (lokal)
- Indeks: `storage/blackbox/evidence/automatic-evidence-index.csv` (lokal)
- Template hasil manual: `docs/blackbox/results-template.csv` (repository, belum diisi)
- Panduan rekonsiliasi: `docs/blackbox/manual-reconciliation-checklist.md` (repository)

Catatan ini adalah rekap pemeriksaan yang benar-benar dilakukan, bukan laporan bahwa aplikasi sudah lulus seluruh pengujian manual.
