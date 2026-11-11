# Kemajuan verifikasi otomatis Black Box Laundryku (lokal)

Tanggal pengujian: 11 November 2026. Lingkungan: PHP 8.3.25, MySQL 8.0.30, database khusus `laundryku_blackbox`, http://127.0.0.1:8086. Data hanya sintetis. Bukti rinci tersimpan lokal pada `storage/blackbox/evidence/` dan tidak dikomit.

PENTING: Ini adalah hasil pemeriksaan otomatis melalui HTTP dan pembacaan database khusus. **Tidak menggantikan 52 pengujian Black Box manual**. Status di `docs/blackbox/results-template.csv` tetap **Belum diuji manual**. Jangan menulis hasil manual Lulus/Gagal tanpa pengujian layar langsung dan screenshot.

## Skenario yang sudah diperiksa secara otomatis
- BB-01–BB-07: login benar/salah, role dan cabang, menambah pelanggan, validasi, menambah layanan. PASS untuk cakupan HTTP.
- BB-09: perpindahan status pesanan received → processing → ready. PASS.
- BB-10: pengambilan ketika pembayaran belum lunas ditolak. PASS.
- BB-13: transfer pending lalu dikonfirmasi pemilik, tidak masuk uang tunai shift. PASS untuk alur tersebut.
- BB-14: pembayaran kedua pada pesanan lunas ditolak. PASS.
- BB-15: halaman nota HTML untuk ukuran 58/80 mm dapat dibuka dan memuat nilai. PASS untuk HTML, bukan uji printer fisik.
- BB-16: Kasir A membuka shift baru setelah shift sebelumnya ditutup. PASS.
- BB-18: stok keluar 0,5 liter dari 2 liter menjadi 1,5 liter. PASS.
- BB-19: koreksi stok kasir memerlukan persetujuan pemilik sebelum jumlah menjadi 3 liter. PASS.
- BB-20: pengeluaran Rp12.000 kategori deterjen tersimpan di Cabang A. PASS.
- BB-26: kasir ditolak 403, tamu diarahkan ke login 302, pemilik diizinkan 200 untuk rekonsiliasi. PASS.
- BB-27: filter rekonsiliasi historis Cabang A/B memisahkan transaksi. PASS untuk tampilan yang diperiksa.
- BB-28: mencoba menghubungkan pembayaran historis Cabang A ke shift Cabang B ditolak. PASS untuk kasus salah cabang, kasus keamanan lain belum diuji.
- BB-35: transfer ditolak → retry pending → retry ganda ditolak → konfirmasi pemilik paid. PASS untuk alur tersebut.
- BB-36: QRIS ditolak → retry pending → penolakan pemilik. PASS untuk alur tersebut, siklus lanjut belum diperiksa.
- BB-41: 0,5 kg x Rp8.000 = Rp4.000, informasi minimum 2 kg tersedia. PASS.
- BB-42: 2,5 kg x Rp8.000 = Rp20.000. PASS.
- BB-43: 0,5 kg dan 2 item = Rp34.000. PASS.
- BB-44: kuantitas item pecahan dan kg >2 desimal ditolak. PASS.
- BB-45: harga layanan diubah sementara Rp99.000/minimum10; pesanan lama Rp4.000 dan nota tetap memakai tarif historis Rp8.000; setelan dikembalikan Rp8.000/minimum2. PASS.
- BB-46: saldo awal Rp100.000 + kas terhubung Rp40.000 menghasilkan estimasi Rp140.000; belum dihitung selisih final. PASS.
- BB-48: historis tunai tanpa shift dan belum ditinjau memblokir penutupan. PASS.
- BB-50: kas Rp140.000, kas fisik Rp138.000, selisih -Rp2.000; pemilik meninjau tanpa mengubah angka final. PASS.
- BB-51: shift baru Rp100.000 ditambah tunai Rp4.000, saldo final Rp104.000, selisih Rp0. PASS.

## Pemeriksaan parsial
- BB-21: indikator keterlambatan tampil; skenario lengkap belum diuji.
- BB-22: laporan dapat ditampilkan dengan pilihan Semua Cabang, A dan B; audit seluruh total/filter belum dilakukan.
- BB-23: PDF/CSV/XLSX dapat diunduh; konten lengkap file dan cetak belum diinspeksi.
- BB-24: ekspor laporan operasional XLSX dapat diunduh; validasi isi lengkap belum dilakukan.
- BB-25: aktivitas tampil pada riwayat; seluruh isi event belum diaudit.
- BB-37: pembatasan baca data lintas usaha/cabang teruji; mutasi lintas usaha belum dieksekusi.
- BB-47: kas shift tidak berubah karena pesanan belum dibayar dan transfer terkonfirmasi; seluruh variasi pending/rejected/antarshift/usaha belum diuji.
- BB-52: kasir A/B melihat shift masing-masing dan pemilik melihat gabungan; mobile dan perubahan konteks cabang belum diverifikasi.

## Kondisi database uji saat dokumen dibuat
- Profil advanced sudah diaktifkan sekali; usaha/cabang asing sintetis dan pembayaran historis disiapkan.
- Shift #1 telah ditutup dan ditinjau (selisih -Rp2.000). Shift #3 submitted (selisih Rp0). Shift lain sesuai status aktual.
- Pembayaran historis berlabel BB-H-* sengaja belum semua direkonsiliasi; jangan menutup shift aktif Cabang A secara paksa.
- Transaksi uji, stok, pengeluaran dan tarif sementara yang dikembalikan dapat memengaruhi data awal. Jangan memakai asumsi saldo/total fixture awal untuk skenario selanjutnya.
- Data utama `laundryku` dan PHPUnit `laundryku_testing` tidak dipakai dalam pemeriksaan HTTP ini.

## Masih perlu dilakukan
- Skenario rekonsiliasi positif (atribusi ke shift valid, unallocated, overlap dan perubahan keputusan), percobaan ulang QRIS penuh, perlindungan mutasi lintas usaha, akun/cabang nonaktif, serta beberapa skenario lain di `BLACKBOX_TEST_PLAN.md`.
- Pengujian manual 52 skenario dengan screenshot, browser desktop/mobile, nota printer fisik bila tersedia.
- Saat bukti manual dikumpulkan, catat tanggal, peran, input, hasil aktual dan screenshot. Jangan memindahkan PASS otomatis ke kolom hasil manual.

## Lokasi bukti (lokal, rahasia)
`C:\laragon\www\Laundryku\storage\blackbox\evidence\`
Semua artefak di folder ini mengandung data uji saja, tetapi tetap tidak perlu dikomit ke repository.
