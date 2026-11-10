# Urutan 52 skenario (belum dijalankan manual)

Urutan ini sengaja mengikuti ketergantungan fixture dan saldo. Bukan daftar hasil pengujian. Gunakan results-template.csv untuk pencatatan; jangan isi Lulus/Gagal sebelum tindakan nyata.

| Urutan | Kasus | Persiapan / fixture / hal yang dicatat |
| --- | --- | --- |
| 1 | BB-01 | Akun dasar; pelanggan/layanan dibuat hanya pada cabang uji. |
| 2 | BB-02 | Akun dasar; pelanggan/layanan dibuat hanya pada cabang uji. |
| 3 | BB-03 | Akun dasar; pelanggan/layanan dibuat hanya pada cabang uji. |
| 4 | BB-04 | Akun dasar; pelanggan/layanan dibuat hanya pada cabang uji. |
| 5 | BB-05 | Akun dasar; pelanggan/layanan dibuat hanya pada cabang uji. |
| 6 | BB-06 | Akun dasar; pelanggan/layanan dibuat hanya pada cabang uji. |
| 7 | BB-07 | Akun dasar; pelanggan/layanan dibuat hanya pada cabang uji. |
| 8 | BB-41 | Kg min2/tarif8000 dan item15000. Untuk BB-45, ubah lalu kembalikan konfigurasi kg ke 8000/min2; snapshot lama tidak boleh berubah. |
| 9 | BB-42 | Kg min2/tarif8000 dan item15000. Untuk BB-45, ubah lalu kembalikan konfigurasi kg ke 8000/min2; snapshot lama tidak boleh berubah. |
| 10 | BB-43 | Kg min2/tarif8000 dan item15000. Untuk BB-45, ubah lalu kembalikan konfigurasi kg ke 8000/min2; snapshot lama tidak boleh berubah. |
| 11 | BB-44 | Kg min2/tarif8000 dan item15000. Untuk BB-45, ubah lalu kembalikan konfigurasi kg ke 8000/min2; snapshot lama tidak boleh berubah. |
| 12 | BB-45 | Kg min2/tarif8000 dan item15000. Untuk BB-45, ubah lalu kembalikan konfigurasi kg ke 8000/min2; snapshot lama tidak boleh berubah. |
| 13 | BB-46 | Gunakan shift awal A: opening100000, linked25000+15000. Jangan menerima tunai baru pada shift ini sebelum BB-50. |
| 14 | BB-47 | Gunakan shift awal A: opening100000, linked25000+15000. Jangan menerima tunai baru pada shift ini sebelum BB-50. |
| 15 | BB-52 | Gunakan shift awal A: opening100000, linked25000+15000. Jangan menerima tunai baru pada shift ini sebelum BB-50. |
| 16 | BB-50 | Gunakan shift awal A: opening100000, linked25000+15000. Jangan menerima tunai baru pada shift ini sebelum BB-50. |
| 17 | BB-16 | BB-50 sudah menutup/review shift awal A. Buka shift baru sendiri100000. |
| 18 | BB-08 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 19 | BB-09 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 20 | BB-10 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 21 | BB-11 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 22 | BB-12 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 23 | BB-13 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 24 | BB-14 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 25 | BB-15 | Gunakan pesanan baru terpisah; catat ID baru. BB-A-MIN belum dibayar dapat menjadi contoh; hindari membayar ulang BB-A-CASH25/15. |
| 26 | BB-17 | Tutup/review shift setelah BB-11/12; saldo mengikuti penerimaan aktual, jangan mengharapkan140000 lagi. |
| 27 | BB-51 | Buka shift baru100000 sebagai prasyarat, lihat halaman, terima satu tunai baru, muat ulang/tutup; catat saldo aktual. |
| 28 | BB-18 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 29 | BB-19 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 30 | BB-20 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 31 | BB-21 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 32 | BB-22 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 33 | BB-23 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 34 | BB-24 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 35 | BB-25 | Stok A mulai2liter/min5; gunakan jumlah kecil. Pengeluaran/report sesuai periode aktual dan data yang sudah ditambahkan. |
| 36 | BB-26 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 37 | BB-27 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 38 | BB-28 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 39 | BB-29 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 40 | BB-31 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 41 | BB-33 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 42 | BB-48 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 43 | BB-30 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 44 | BB-32 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 45 | BB-34 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 46 | BB-35 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 47 | BB-36 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 48 | BB-37 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 49 | BB-38 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 50 | BB-39 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 51 | BB-40 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |
| 52 | BB-49 | Aktifkan advanced sesudah BB-25; gunakan fixture/ID yang dicatat status. Selesaikan semua historis relevan sebelum mengharapkan close berhasil. |

## Titik transisi dan pemeriksaan yang perlu dilengkapi

- BB-47 memeriksa digital/shift B dan data negatif. Untuk subkasus usaha asing, lengkapi bukti setelah `advanced` tersedia pada tahap5 (sebelum menyimpulkan hasil akhir BB-47). Cash pending/rejected tidak dibuat oleh UI normal: persiapkan hanya sebagai fixture negatif melalui DB GUI dengan akun terbatas laundryku_blackbox; jangan mengubah status transaksi nyata. Jika prasyarat belum dibuat, catat Terhambat/Belum diuji, jangan mengklaim lulus.
- Sesudah BB-25 jalankan `php scripts/blackbox.php advanced` sekali. Ini hanya menambah data, tidak mereset hasil atau saldo lama. Simpan hasil `status` agar ID route fixture diketahui. Ada akun/usahacabang tambahan semata untuk isolasi dan overlap, di luar profil awal1Pemilik/2Kasir.
- BB-26/27: Kasir A/tamu ditolak, Pemilik memilih Semua Cabang/A/B lalu filter fixture `BB-H-*`. BB-28/29/31 memakai `BB-H-ASSIGN` tanpa mengubahnya ketika penolakan benar. Gunakan ID shift F untuk usaha lain, B untuk cabang lain, dan shift reviewed untuk final.
- BB-33 dan BB-48 dilakukan sebelum ada keputusan: `BB-H-BLOCK` harus memblokir close dan tidak menambah saldo.
- BB-30: alokasikan `BB-H-ASSIGN` ke shift A terbuka, coba ulangi. BB-32: tandai `BB-H-BLOCK` dan `BB-H-UNALLOC` unallocated dengan alasan. Historis lain yang sengaja belum dipakai tetap memblokir close; jangan mengira pengecualian satu record menyelesaikan seluruh cabang.
- BB-34: `BB-H-OVERLAP` hanya ke salah satu shift A (kasir.a atau kasir.overlap); untuk menutup kedua shift, terlebih dahulu tinjau seluruh `BB-H-*` A yang tersisa. Jika `BB-H-LATER` perlu dipertahankan untuk kasus berikutnya, tandai unallocated, bukan mengaitkan. Login setiap pemilik shift untuk close; Pemilik tidak menutup shift Kasir.
- BB-35/36: `BB-RETRY-TRANSFER` dan `BB-RETRY-QRIS`. Pending/paid pengujian ulang tidak boleh menghasilkan percobaan baru. Pakai `BB-DIGITAL-PENDING` bila perlu. Untuk BB-37, Kasir B mencoba order A, Pemilik utama mencoba `BB-FOREIGN-RETRY`.
- BB-38: `BB-RETRY-LOOP` untuk siklus penolakan berulang; catat referensi/alasannya, bukan menimpa hasil kasus lain.
- BB-39 memerlukan persiapan nonaktif: fitur UI saat ini tidak menyediakan toggle akun/usaha/cabang. Penguji boleh memakai DB GUI **hanya** dengan akun terbatas manual untuk mengubah is_active fixture lalu mengembalikannya. Uji session yang sudah login terhadap operasi sensitif. Catat prasyarat dan screenshot; jangan menyebut kasus selesai bila belum dilakukan. Jangan gunakan akun root atau database utama/PHPUnit.
- BB-40/49: gunakan `BB-H-LATER` yang sudah unallocated; buka shift A baru sebelum bukti/pembayaran historis dibuat bila waktu historis tidak lagi tercakup. Shift baru setelah waktu bayar lama tidak dapat menerima pengaitan lama. Untuk kasus ini siapkan pembayaran sintetis baru setelah shift dibuka, lalu unallocated→assign; jangan memalsukan waktu pembayaran yang sudah diuji. Tetap pertahankan satu kaitan dan audit kedua keputusan.
- BB-50 sudah memakai baseline bersih pada awal urutan sehingga expected140000/difference-2000 dapat diperiksa tanpa pengaruh tunai dari kasus lain. BB-51 selalu memakai shift baru agar prasyarat jelas.

Urutan dapat diulang dengan bukti baru pada ID sama; jangan menghapus catatan percobaan sebelumnya. Setelah data berubah, nilai laporan harus diturunkan dari transaksi yang benar-benar dilakukan, bukan angka baseline yang dipaksakan.
