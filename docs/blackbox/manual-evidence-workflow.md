# Pelaksanaan pengujian manual Black Box Laundryku

Dokumen ini menjelaskan lokasi berkas hasil manual yang sudah disiapkan pada komputer uji. Setiap pengujian dilakukan sendiri melalui browser, kemudian diberi bukti screenshot.

## Persiapan
- Aplikasi Black Box: `http://127.0.0.1:8086/login`
- Akun contoh tersedia pada `storage/blackbox/LOCAL-LOGIN.txt`, berisi password lokal yang tidak boleh dimasukkan ke GitHub atau screenshot
- Database Black Box: `laundryku_blackbox`. Jangan jalankan reset atau pengujian PHPUnit pada database ini
- Dokumen urutan skenario: `docs/blackbox/execution-order.md`
- Rekap kerja lokal: `storage/blackbox/evidence/manual/HASIL_MANUAL_KERJA.csv`
- Direktori screenshot: `storage/blackbox/evidence/manual/BB-01/` sampai `BB-52/`

## Cara menguji setiap kasus
1. Buka `docs/blackbox/execution-order.md`, cari skenario yang akan diuji.
2. Login menggunakan akun peran yang sesuai pada aplikasi Black Box.
3. Jalankan langkah sesuai baris pada `HASIL_MANUAL_KERJA.csv`. Periksa hasil nyata di layar, bukan hanya pesan sukses.
4. Ambil screenshot setelah tindakan. Simpan ke folder ID masing-masing. Contoh `BB-01/BB-01-01.png`.
5. Isi kolom penguji, waktu, browser/perangkat, langkah/input, hasil aktual, status, lokasi screenshot dan catatan pada CSV kerja lokal.
6. Gunakan status `Lulus` jika hasil nyata cocok, `Gagal` jika tidak, `Terhambat` jika belum bisa dijalankan, atau `Belum diuji manual` jika belum mulai. Jangan mengisi Lulus hanya dari hasil PHPUnit atau HTTP.
7. Jika gagal, catat langkah reproduksi dan simpan screenshot sebelum memperbaiki aplikasi. Setelah diperbaiki, ulangi skenarionya dan tambahkan bukti.
8. Setelah 52 kasus selesai, hitung Lulus/Gagal/Terhambat. Gunakan hasil nyata tersebut untuk tabel pengujian laporan TA.

## Prioritas saat ini
- Mulai pengujian manual dari BB-01 hingga BB-07 sebagai tahap login, otorisasi, pelanggan dan layanan.
- BB-30 dan BB-32: pengaitan historis dan penandaan belum teralokasi. Gunakan `docs/blackbox/manual-reconciliation-checklist.md`. Lakukan hanya sekali pada fixture yang telah disediakan, jangan manipulasi waktu.
- BB-39: keamanan akun/cabang nonaktif harus menggunakan fixture sintetis yang bisa dipulihkan. Jika belum ada prosedur aman, tandai Terhambat, bukan Lulus.
- Periksa cetakan nota 58/80mm menggunakan pratinjau cetak. Jika ada printer fisik, lakukan uji fisik sebagai bukti tambahan.

## Keamanan dan bukti
- Gunakan screenshot data contoh saja, bukan identitas nyata.
- Simpan password dan konfigurasi lokal di komputer sendiri. Jangan unggah `.env.blackbox` atau `LOCAL-LOGIN.txt`.
- Jangan mengubah `docs/blackbox/results-template.csv` sebagai laporan yang sudah selesai. Itu template kosong yang memang disimpan di repository.
- Berkas screenshot/hasil manual kerja bersifat lokal dan tidak diunggah otomatis ke GitHub. Tinjau isinya sebelum memilih bukti yang layak dicantumkan pada laporan TA.

## Status persiapan
52 folder `BB-01` sampai `BB-52` dan masing-masing berkas `CATATAN.md` telah disiapkan. Hasil manual belum dijalankan. Pengujian Laravel terakhir: 98 lulus, 1.118 assertions.
