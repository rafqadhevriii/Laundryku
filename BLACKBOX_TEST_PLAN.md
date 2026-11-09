# Rencana Pengujian Black Box Laundryku

Tanggal penyusunan: 2026-11-09
Status: SKENARIO DISIAPKAN, BELUM DIUJI MANUAL.
Metode: Black Box Testing, menguji input, tindakan pengguna, dan keluaran tanpa memeriksa implementasi internal.

## Prasyarat
- Gunakan data uji, bukan data pelanggan asli.
- Siapkan satu akun Pemilik, dua akun Kasir berbeda cabang, minimal dua cabang, pelanggan uji, layanan, dan stok uji.
- Jalankan pada lingkungan uji atau cadangan data aman. Jangan menghapus database operasional.
- Catat tanggal, penguji, browser, bukti screenshot, hasil aktual, dan status Lulus/Gagal untuk setiap kasus.

## Matriks skenario

| ID | Modul | Peran | Langkah / data uji | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|---|---|
| BB-01 | Login | Pemilik | Masukkan username dan password benar | Masuk ke dashboard sesuai peran | Belum diuji | Belum diuji |
| BB-02 | Login | Pengunjung | Masukkan password salah | Login ditolak, pesan kesalahan muncul | Belum diuji | Belum diuji |
| BB-03 | Otorisasi | Kasir | Akses URL laporan pemilik | Akses ditolak (403) | Belum diuji | Belum diuji |
| BB-04 | Cabang | Kasir A | Coba buka pesanan milik cabang B melalui URL | Data cabang B tidak terbuka | Belum diuji | Belum diuji |
| BB-05 | Pelanggan | Kasir | Tambah pelanggan dengan data valid | Pelanggan tersimpan di cabang yang sesuai | Belum diuji | Belum diuji |
| BB-06 | Pelanggan | Kasir | Kosongkan kolom wajib | Validasi tampil, data tidak tersimpan | Belum diuji | Belum diuji |
| BB-07 | Layanan | Pemilik | Tambah layanan dan tarif valid | Layanan tersedia untuk transaksi | Belum diuji | Belum diuji |
| BB-08 | Pesanan | Kasir | Buat pesanan dengan beberapa layanan | Nomor pesanan dan total dihitung sesuai rincian | Belum diuji | Belum diuji |
| BB-09 | Pesanan | Kasir | Ubah diterima menjadi diproses, lalu siap diambil | Status mengikuti tahapan yang diizinkan | Belum diuji | Belum diuji |
| BB-10 | Pesanan | Kasir | Coba tandai sudah diambil sebelum lunas | Sistem menolak perubahan status | Belum diuji | Belum diuji |
| BB-11 | Pembayaran | Kasir | Bayar tunai penuh | Pembayaran tercatat lunas sesuai jumlah | Belum diuji | Belum diuji |
| BB-12 | Pembayaran | Kasir | Masukkan nominal pembayaran tidak sesuai | Pembayaran tidak diterima | Belum diuji | Belum diuji |
| BB-13 | Pembayaran digital | Kasir/Pemilik | Ajukan transfer/QRIS, lalu konfirmasi oleh pemilik | Menunggu konfirmasi lalu lunas setelah disetujui | Belum diuji | Belum diuji |
| BB-14 | Pembayaran | Kasir | Coba membuat pembayaran kedua pada pesanan lunas | Pembayaran ganda dicegah | Belum diuji | Belum diuji |
| BB-15 | Nota | Kasir | Buka nota pesanan | Nomor, pelanggan, layanan, total, dan status tampil benar | Belum diuji | Belum diuji |
| BB-16 | Shift | Kasir | Buka shift dengan saldo awal valid | Shift aktif tercatat | Belum diuji | Belum diuji |
| BB-17 | Shift | Kasir/Pemilik | Tutup shift dengan saldo akhir berbeda, pemilik review | Selisih kas dihitung dan dapat ditinjau pemilik | Belum diuji | Belum diuji |
| BB-18 | Stok | Kasir | Catat stok keluar dengan jumlah valid | Riwayat mutasi dan stok berubah sesuai aturan | Belum diuji | Belum diuji |
| BB-19 | Stok | Kasir/Pemilik | Ajukan koreksi stok lalu tinjau oleh pemilik | Koreksi mengikuti alur persetujuan | Belum diuji | Belum diuji |
| BB-20 | Pengeluaran | Kasir | Tambah pengeluaran dengan kategori dan nominal valid | Pengeluaran tercatat di cabang terkait | Belum diuji | Belum diuji |
| BB-21 | Dashboard | Pemilik | Buat pesanan melewati estimasi selesai | Pesanan terlambat ditandai pada dashboard | Belum diuji | Belum diuji |
| BB-22 | Laporan | Pemilik | Filter pendapatan berdasarkan tanggal/cabang | Hanya data periode dan cabang terpilih tampil | Belum diuji | Belum diuji |
| BB-23 | Ekspor | Pemilik | Unduh laporan PDF, CSV, dan Excel | File dapat dibuka, isi sesuai filter | Belum diuji | Belum diuji |
| BB-24 | Laporan operasional | Pemilik | Ekspor Excel operasional | Sheet ringkasan, keterlambatan, stok, shift, dan aktivitas tersedia | Belum diuji | Belum diuji |
| BB-25 | Audit log | Pemilik | Ubah status pesanan atau pembayaran | Aktivitas sensitif tercatat dalam audit log | Belum diuji | Belum diuji |

## Cara mengisi hasil
Untuk setiap BB-XX, ganti 'Belum diuji' pada kolom Hasil aktual dengan perilaku yang benar-benar terlihat. Isi Status dengan Lulus jika hasil aktual sesuai harapan, Gagal jika berbeda. Simpan bukti pengujian dengan nama BB-XX.

## Catatan untuk laporan TA
Tabel ini adalah rencana pengujian, bukan hasil penelitian. Jangan menyebut seluruh fitur berhasil diuji sebelum pengujian manual dilaksanakan. Pengujian otomatis Laravel terpisah dari pengujian Black Box manual.
