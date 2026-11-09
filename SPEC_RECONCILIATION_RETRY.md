# Spesifikasi Rekonsiliasi Pembayaran Historis dan Pengajuan Ulang Digital

Tanggal: 2026-11-09
Status: rancangan untuk implementasi Codex, belum diimplementasikan.

## Tujuan
Menangani pembayaran tunai historis dengan cash_shift_id NULL tanpa mengarang atribusi shift, serta mengizinkan kasir mengajukan ulang pembayaran digital yang ditolak secara aman.

## Aturan rekonsiliasi pembayaran tunai lama
1. Hanya pemilik usaha terkait yang dapat mengakses daftar pembayaran tunai historis belum dialokasikan.
2. Daftar hanya memuat pembayaran method=cash, status=paid, cash_shift_id=NULL dalam business_id milik pemilik. Filter cabang, tanggal, dan nomor pesanan.
3. Pemilik dapat menghubungkan pembayaran ke shift yang benar hanya setelah memeriksa bukti. Pilihan shift wajib dari usaha dan cabang yang sama, dengan waktu pembayaran dalam periode shift yang relevan. Pertimbangkan shift terbuka/ditutup, dan jelaskan konsekuensi perhitungan ulang.
4. Jika shift tidak dapat dipastikan, pemilik dapat menandai pembayaran sebagai 'historis belum teralokasi' dengan alasan wajib. Penandaan tidak boleh otomatis mengubah nominal, metode, status lunas, atau mengaitkan shift sembarang.
5. Jangan menghapus pembayaran historis. Simpan aktor, waktu, alasan, status sebelum/sesudah, dan shift tujuan dalam audit log.
6. Untuk shift yang sudah ditutup/ditinjau, jangan mengubah saldo atau selisih diam-diam. Gunakan mekanisme koreksi terotorisasi dan audit log atau batasi pengaitan ke shift yang belum final, dengan penjelasan untuk kasus final.
7. Hindari pembayaran yang sama masuk dua shift. Validasi dan kunci baris dalam transaksi DB.
8. Penutupan shift tidak boleh memblokir selamanya akibat pembayaran lama yang secara eksplisit ditandai tidak dapat dialokasikan. Pastikan keputusan pengecualian terlihat dalam laporan rekonsiliasi dan tidak dihitung sebagai uang kas shift.
9. Jika masih ada pembayaran historis belum ditinjau dalam periode shift, tetap blokir penutupan untuk menghindari angka keliru.
10. Laporan pendapatan tetap menghitung pembayaran paid satu kali. Rekonsiliasi shift tidak boleh menggandakan pendapatan.

## Aturan pengajuan ulang digital ditolak
1. Kasir hanya dapat mengajukan ulang pembayaran rejected milik pesanan cabang yang ditugaskan dan usaha yang sama. Pemilik dapat mengajukan ulang untuk usaha miliknya.
2. Pengajuan ulang hanya untuk transfer atau QRIS. Metode dan referensi transaksi dapat diperbarui, tetapi jumlah tetap sama dengan total pesanan.
3. Status menjadi pending dan harus dikonfirmasi ulang oleh pemilik. Jangan langsung paid.
4. Cegah pengajuan ulang bila sudah paid/pending. Gunakan transaksi dan locking untuk mencegah duplikasi.
5. Jangan menghapus jejak penolakan sebelumnya. Buat audit log dan riwayat percobaan pembayaran (jika perlu model terpisah).
6. Pastikan tampilan menunjukkan alasan penolakan sebelumnya, opsi pengajuan ulang, dan status baru.

## Skenario tes wajib
- Kasir cabang A tidak bisa mengajukan ulang pembayaran cabang B.
- Kasir bisa mengajukan ulang rejected, tetapi tidak bisa mengonfirmasi sendiri.
- Pending/paid tidak bisa diajukan ulang; nominal tidak berubah; riwayat penolakan tetap dapat ditelusuri.
- Pemilik tidak bisa merekonsiliasi pembayaran dari usaha lain atau ke shift cabang lain.
- Pembayaran historis tak teralokasi yang sudah ditinjau tidak memblokir shift selamanya, tetapi tidak masuk perhitungan shift.
- Pembayaran historis belum ditinjau masih memblokir penutupan shift relevan.
- Pembayaran lama yang dialokasikan tidak dihitung dua kali dan tidak mengubah saldo shift final secara diam-diam.
- Pengujian regresi pembayaran tunai, shift tumpang tindih, laporan, dan audit log.

## Batasan
- Jangan mengubah/menghapus data nyata secara otomatis saat migration.
- Jangan memakai migrate:fresh pada database utama.
- Jalankan pengujian di laundryku_testing.
- Perbarui PROGRESS.md, CHANGELOG.md, dan BLACKBOX_TEST_PLAN.md setelah implementasi.
- Kerjakan pada branch baru, ajukan PR, jangan merge otomatis.
