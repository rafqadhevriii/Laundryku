# Panduan Uji Manual Rekonsiliasi dan Retry, Lanjutan Black Box

Diperbarui: 11 November 2026. Lingkungan khusus: http://127.0.0.1:8086/login. Database: laundryku_blackbox. Semua data sintetis. Dokumen ini adalah langkah pengujian, bukan bukti hasil manual.

## Sebelum memulai
- Pastikan login sebagai `bb.pemilik` dan cabang di header pada `Semua Cabang`. Password tersedia secara lokal di `storage/blackbox/LOCAL-LOGIN.txt`, jangan dibagikan atau dimasukkan screenshot.
- Buka `Rekonsiliasi Pembayaran Tunai Historis` dari menu pemilik. Data advanced sudah diaktifkan; jangan jalankan setup/advanced ulang.
- Simpan setiap screenshot nyata di `storage/blackbox/evidence/<run-id>/<ID>/`. Catat tanggal/jam, peran, ID pesanan, input, hasil aktual, status.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, `php artisan test` atau reset pada database blackbox. Data hasil uji lama sengaja dipertahankan.

## Urutan aman skenario rekonsiliasi
1. BB-26: Kasir A membuka rekonsiliasi, pastikan ditolak (403). Pemilik masuk berhasil. Bukti: halaman 403 dan halaman pemilik.
2. BB-27: Pada mode `Semua Cabang`, filter Cabang A dan Cabang B secara bergantian. Periksa `BB-H-ASSIGN` hanya pada A, `BB-H-B` hanya pada B. Bukti: kedua filter.
3. BB-28: Gunakan `BB-H-ASSIGN`, coba pilih shift Cabang B #2 dengan tindakan uji (jika option UI tidak tersedia, catat bahwa pilihan disaring dan uji penolakan melalui request pengujian khusus). Pesan validasi harus menyatakan cabang tidak sama; tidak boleh ada perubahan pengaitan.
4. BB-30: Pada `BB-H-ASSIGN`, pilih `Hubungkan ke shift berdasarkan bukti`, pilih shift Cabang A #4, berikan alasan yang menjelaskan bukti sintetis. Pastikan pembayaran Rp15.000 hanya masuk ke shift #4, tidak ke #5, dan pendapatan tidak bertambah dua kali. Kemudian ulangi keputusan untuk record yang sama. Sistem harus menolak keputusan kedua. Hanya lakukan ini sekali.
5. BB-32: Pada `BB-H-UNALLOC`, pilih `Tandai historis belum teralokasi`, isi alasan, simpan. Pastikan tetap masuk total pendapatan tetapi tidak masuk kas shift. Jangan menganggap seluruh peringatan penutupan shift hilang, karena masih ada `BB-H-BLOCK`, `BB-H-OVERLAP`, dan `BB-H-LATER` yang belum ditinjau.
6. BB-33/48: Dalam akun Kasir A, coba tutup shift aktif #4 ketika masih ada pembayaran historis belum ditinjau. Harus muncul pesan penolakan, status tetap `open` dan kas tidak dihitung otomatis.
7. BB-34: Kasir `bb.kasir.overlap` juga memiliki shift A #5. `BB-H-OVERLAP` hanya boleh ditautkan ke satu shift yang sesuai; periksa total shift lain tetap. Lanjutkan sesuai ketergantungan di `execution-order.md`.
8. BB-40/49: Ikuti prosedur khusus pada `execution-order.md`, perhatikan kelayakan waktu pembayaran dan shift. Jangan mengubah cap waktu pesanan lama agar terlihat valid.

## Pengujian pembayaran digital
1. BB-35 sudah diverifikasi otomatis untuk `BB-RETRY-TRANSFER`: percobaan pertama ditolak, percobaan kedua menjadi lunas setelah pemilik mengonfirmasi. Verifikasi manual bahwa riwayat dua percobaan terlihat, tanpa duplikasi pembayaran.
2. BB-36: `BB-RETRY-QRIS` saat ini berstatus `rejected` dengan dua attempts `rejected`. Ajukan retry berikutnya melalui UI (referensi uji baru), periksa `pending`, lalu Pemilik mengonfirmasi atau menolak. Catat seluruh nomor attempt dan perubahan status. Jangan mengulang pada pesanan `BB-RETRY-TRANSFER` yang sudah lunas.
3. BB-38: Gunakan `BB-RETRY-LOOP` khusus untuk siklus berulang agar tidak mengubah hasil BB-35/BB-36.

## Keamanan
- BB-37: Pastikan pemilik usaha utama tidak bisa mengakses pesanan `BB-FOREIGN-RETRY` (#20) milik usaha asing. Kasir A tidak bisa melihat pesanan Cabang B. Catat hasil GET dan uji POST tidak sah hanya dengan akun sintetis dan mengikuti instruksi penguji.
- BB-39: Akun/cabang nonaktif perlu pengaturan fixture sintetis dan pengembalian status. Jangan ubah akun nyata dan jangan ubah database utama. Jika tidak dapat dilakukan, tandai `Terhambat`, bukan `Lulus`.
- BB-52: Uji tampilan 375px dan keyboard untuk menu cabang, shift, dan notifikasi kesalahan.

## Kondisi yang telah diperiksa secara otomatis
- `BB-RETRY-TRANSFER`: pembayaran `paid`, attempt #1 `rejected`, attempt #2 `paid`.
- `BB-RETRY-QRIS`: pembayaran `rejected`, attempt #1 dan #2 `rejected`.
- `BB-RETRY-LOOP`: `rejected`, belum dipakai untuk siklus tambahan.
- `BB-H-ASSIGN` (payment #6), `BB-H-UNALLOC` (#7), `BB-H-BLOCK` (#8), `BB-H-OVERLAP` (#9), `BB-H-LATER` (#10): belum dialokasikan atau ditinjau pada pemeriksaan terakhir.
- Rekonsiliasi salah cabang telah ditolak pada pemeriksaan HTTP otomatis. Pengaitan positif belum dilakukan karena tindakan otomatis dibatasi.

**Hasil manual BB-01 sampai BB-52 tetap berstatus Belum diuji manual sampai bukti screenshot dan hasil aktual dicatat.**
