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
| BB-11 | Pembayaran | Kasir | Buka shift sendiri, lalu bayar tunai penuh | Pembayaran lunas dan terhubung ke shift penerima | Belum diuji | Belum diuji |
| BB-12 | Pembayaran | Kasir | Manipulasi nominal request menjadi lebih kecil | Nominal tersimpan tetap total pesanan dari server, tidak menerima pembayaran sebagian | Belum diuji | Belum diuji |
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

## Tambahan skenario rekonsiliasi dan retry

Tetap gunakan data sintetis pada database pengujian. Tambahkan usaha kedua, shift tumpang tindih, satu shift final, pembayaran cash/paid tanpa ID shift, dan transfer rejected sebagai fixture. Jangan membuat fixture pada data utama. Semua baris berikut adalah rencana pengujian manual, belum hasil manual.

| ID | Modul | Peran | Langkah / data uji | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|---|---|
| BB-26 | Rekonsiliasi | Kasir/tamu | Buka halaman dan kirim keputusan rekonsiliasi | Kasir 403; tamu diarahkan login; data tidak berubah | Belum diuji manual | Belum diuji manual |
| BB-27 | Rekonsiliasi | Pemilik | Filter cabang, tanggal bayar, nomor pesanan | Hanya tunai paid tanpa shift milik usaha sendiri; tidak mengaitkan otomatis | Belum diuji manual | Belum diuji manual |
| BB-28 | Rekonsiliasi | Pemilik | Kirim pengaitan/pengecualian tanpa alasan atau hanya spasi | Validasi menolak; tidak ada keputusan/perubahan | Belum diuji manual | Belum diuji manual |
| BB-29 | Rekonsiliasi | Pemilik | Pilih shift usaha/cabang lain atau periode salah | Pengaitan ditolak; saldo dan pembayaran tetap | Belum diuji manual | Belum diuji manual |
| BB-30 | Rekonsiliasi | Pemilik | Hubungkan historis ke shift terbuka yang sesuai lalu ulangi/pilih shift lain | Hanya satu kaitan/keputusan; audit aktor/waktu/alasan/sebelum-sesudah tersedia | Belum diuji manual | Belum diuji manual |
| BB-31 | Rekonsiliasi | Pemilik | Coba hubungkan ke shift submitted/reviewed | Ditolak dengan penjelasan; expected_cash/difference final tidak berubah | Belum diuji manual | Belum diuji manual |
| BB-32 | Rekonsiliasi/shift | Pemilik/Kasir | Tandai historis belum teralokasi dengan alasan, lalu tutup shift | Tidak lagi memblokir; tidak masuk kas shift; tetap ada pada ringkasan/laporan keputusan dan pendapatan | Belum diuji manual | Belum diuji manual |
| BB-33 | Rekonsiliasi/shift | Kasir | Tutup shift saat ada historis relevan belum ditinjau | Tetap diblokir sampai setiap historis ditinjau | Belum diuji manual | Belum diuji manual |
| BB-34 | Rekonsiliasi/shift | Dua Kasir/Pemilik | Alokasikan satu historis ke satu dari dua shift tumpang tindih, tutup keduanya | Hanya shift tujuan menerima nominal; pendapatan tidak bertambah dua kali | Belum diuji manual | Belum diuji manual |
| BB-35 | Retry digital | Kasir | Buka rejected, lihat alasan, ajukan ulang transfer/QRIS dengan referensi baru | Pending; jumlah tetap total; alasan/referensi lama tetap terlihat; kasir tidak dapat mengonfirmasi | Belum diuji manual | Belum diuji manual |
| BB-36 | Retry digital | Kasir/Pemilik | Kirim ulang retry ketika pending/paid atau coba metode tunai | Ditolak tanpa pembayaran/percobaan tambahan; endpoint lama mengikuti aturan sama | Belum diuji manual | Belum diuji manual |
| BB-37 | Retry digital | Kasir A/Pemilik | Coba retry cabang B oleh Kasir A; coba usaha lain oleh Pemilik | Ditolak; Pemilik masih bisa retry cabang lain dalam usaha sendiri | Belum diuji manual | Belum diuji manual |
| BB-38 | Retry/riwayat | Kasir/Pemilik | Tolak → retry → tolak → retry → konfirmasi | Semua percobaan/referensi/alasan tetap tersimpan; hanya percobaan terakhir lunas; pendapatan sekali | Belum diuji manual | Belum diuji manual |
| BB-39 | Otorisasi | Akun nonaktif | Kirim keputusan/retry setelah akun/usaha/cabang dinonaktifkan | Operasi ditolak tanpa perubahan data | Belum diuji manual | Belum diuji manual |
| BB-40 | Rekonsiliasi | Pemilik | Tandai belum teralokasi, lalu dengan bukti baru alokasikan ke shift valid yang masih terbuka | Dua keputusan tercatat, alasan awal tetap ada, kas dihitung sekali | Belum diuji manual | Belum diuji manual |

## Hasil pengujian otomatis 2026-11-09

Pengujian berikut benar-benar dijalankan pada branch `feat/reconciliation-payment-retry`; berbeda dari hasil manual pada matriks di atas.

| Pemeriksaan | Hasil aktual |
|---|---|
| `php artisan test --compact` | 46 lulus, 401 assertions; PHP 8.5.12, MariaDB 11 melalui driver mysql, database laundryku_testing terisolasi |
| `php artisan view:cache` | Berhasil |
| `git diff --check` | Berhasil |
| PaymentReconciliationTest | 10 tes: otorisasi, filter tanpa atribusi otomatis, alasan wajib, isolasi usaha/cabang, waktu/shift final, satu kaitan, pengecualian vs blokir, keputusan bukti baru, dan penolakan data tidak memenuhi syarat |
| DigitalPaymentRetryTest | 7 tes: retry Kasir dengan arsip legacy, konfirmasi ulang, pending/paid/replay, isolasi usaha/cabang, penolakan berulang, digital-only, akun/usaha/cabang nonaktif, dan kas shift tetap terpisah |
| Regresi yang tetap dijalankan | Tunai dengan shift, shift tumpang tindih, status pengambilan, otorisasi/cabang, dashboard, laporan operasional, dan ekspor laporan |
| Browser Playwright/Chromium — Pemilik | Halaman rekonsiliasi terbuka, shift tidak dipilih otomatis, alasan kosong tidak valid, pengecualian tersimpan dan muncul dalam laporan |
| Browser Playwright/Chromium — Kasir | Rejected menampilkan alasan; retry QRIS menghasilkan pending; REF-OLD, REF-BROWSER-NEW, dan alasan penolakan tetap terlihat; halaman Pemilik menghasilkan 403 |
| Browser Playwright/Chromium — konfirmasi | Pemilik mengonfirmasi ulang menjadi lunas, riwayat penolakan tetap terlihat; tidak ada error JavaScript |

Fixture browser adalah usaha/pelanggan/pembayaran sintetis di laundryku_testing. Pengujian akhir Laravel dijalankan kembali setelah pemeriksaan browser. Tidak mengakses database utama, tidak menjalankan perintah migrasi manual; RefreshDatabase menyiapkan skema hanya pada database uji. TestCase memeriksa APP_ENV, driver dan nama database sebelum persiapan skema; phpunit memaksa DB_URL kosong.

Batas hasil: belum ada pengujian manual oleh pengguna laundry, printer, atau konkurensi multiproses. Browser tidak menguji seluruh BB-01 sampai BB-40 dan tidak menggantikan bukti manual TA. Pengaitan/koreksi saldo shift final sengaja tidak disediakan; riwayat legacy hanya memuat data yang masih tersedia, tanpa rekonstruksi spekulatif.

## Cara mengisi hasil
Untuk setiap BB-XX, ganti 'Belum diuji' pada kolom Hasil aktual dengan perilaku yang benar-benar terlihat. Isi Status dengan Lulus jika hasil aktual sesuai harapan, Gagal jika berbeda. Simpan bukti pengujian dengan nama BB-XX.

## Catatan untuk laporan TA
Tabel ini adalah rencana pengujian, bukan hasil penelitian. Jangan menyebut seluruh fitur berhasil diuji sebelum pengujian manual dilaksanakan. Pengujian otomatis Laravel terpisah dari pengujian Black Box manual.
