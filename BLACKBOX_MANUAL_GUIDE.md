# Panduan Pelaksanaan Black Box Manual Laundryku

Disiapkan: 2026-11-10. Mengacu BLACKBOX_TEST_PLAN.md versi 52 skenario.
Status: PERSIAPAN. Pengujian manual belum dilaksanakan dan belum ada status Lulus/Gagal.

## Prinsip penting
- Jangan jalankan reset database (migrate:fresh, RefreshDatabase, db:wipe) pada database utama laundryku.
- Jangan memakai database laundryku_testing untuk pengujian manual karena php artisan test dapat mereset isinya.
- Buat lingkungan dan database MANUAL khusus bernama laundryku_blackbox, dengan file konfigurasi terpisah yang tidak dikomit. Periksa nama DB sebelum menjalankan setup atau migrasi.
- Gunakan pelanggan, nomor kontak, pembayaran, akun, dan nota sintetis; jangan memakai identitas pelanggan nyata.
- Ambil screenshot nyata setelah setiap tindakan. Hasil aktual/status hanya diisi setelah diuji langsung.

## Persiapan data
1. Satu Pemilik usaha uji, dua cabang aktif A dan B, dua Kasir masing-masing terikat satu cabang.
2. Dua pelanggan contoh di masing-masing cabang.
3. Dua layanan contoh: Cuci Kering kg Rp8.000/kg minimum berat 2kg; Setrika per item Rp15.000/item.
4. Stok deterjen uji dan kategori pengeluaran.
5. Shift Kasir A dengan saldo awal Rp100.000, shift Kasir B terpisah.
6. Pesanan contoh 0,5 kg di Cabang A untuk memeriksa total Rp4.000 (minimum tidak menjadi tarif minimum).
7. Pembayaran tunai contoh terhubung Rp25.000 dan Rp15.000 untuk memeriksa saldo sementara Rp140.000; data lain dipisahkan agar tidak mencemari skenario.
8. Data rekonsiliasi historis dibuat hanya jika penguji dapat menghasilkan fixture aman di lingkungan manual khusus.

## Pelaksanaan bertahap

Daftar berikut mengelompokkan modul. Urutan eksekusi yang menjaga saldo baseline dan ketergantungan fixture ada di [urutan52](docs/blackbox/execution-order.md); ikuti urutan tersebut saat mulai menguji.
- Tahap 1, BB-01 sampai BB-07: login, hak akses, cabang, pelanggan, layanan.
- Tahap 2, BB-08 sampai BB-15: pesanan, status, pembayaran, nota.
- Tahap 3, BB-16 sampai BB-20: shift, stok, pengeluaran.
- Tahap 4, BB-21 sampai BB-25: dashboard, laporan, ekspor, audit.
- Tahap 5, BB-26 sampai BB-40: rekonsiliasi historis, percobaan ulang digital, perlindungan akses.
- Tahap 6, BB-41 sampai BB-52: minimum berat, kas sementara shift aktif dan regresi.
- Setelah setiap tahap, verifikasi bahwa hasil aktual sesuai harapan, lalu simpan gambar sebagai BB-XX-deskripsi.png pada folder dokumentasi bukti (hindari unggah screenshot dengan data nyata).

## Pencatatan hasil
Kolom untuk tiap kasus: ID, tanggal/jam, penguji, peran, browser/perangkat, langkah, input uji, hasil yang diharapkan, hasil aktual, status (Lulus/Gagal/Terhambat), lokasi screenshot, catatan.
Contoh yang MASIH HIPOTETIS: BB-41, minimum 2 kg, berat aktual 0,5 kg, tarif Rp8.000/kg, harapan Rp4.000. Status 'Belum diuji' hingga tindakan nyata dilakukan.
Jangan mencampur hasil 82 tes Laravel otomatis dengan 52 skenario Black Box manual.

## Bukti TA
- Screenshot login, pilihan cabang, form pesanan, bukti nominal tagihan, status, shift, cetak nota, halaman stok, laporan PDF/Excel, dan hasil error validasi.
- Gunakan nama file BB-01.png dst; beberapa screenshot per ID dapat diberi akhiran a, b.
- Di laporan TA, tampilkan tabel terpilih hasil aktual yang benar-benar diuji, total Lulus/Gagal, dan pembahasan temuan.
- Uji printer thermal fisik 58mm/80mm terpisah bila perangkat tersedia.
- Jika menemukan kegagalan, buat issue/fix PR terpisah dan ulangi skenario yang gagal beserta regresinya.

## Menjalankan lingkungan yang sudah disiapkan

Mekanisme sekarang tersedia di [docs/blackbox/README.md](docs/blackbox/README.md). Gunakan **APP_ENV=blackbox**, connection blackbox, database laundryku_blackbox, akun DB terbatas hanya schema literal tersebut. Profil testing tetap khusus PHPUnit dan sengaja ditolak launcher manual.

- Salin .env.blackbox.example ke .env.blackbox, isi kredensial DB khusus dan BLACKBOX_ACCOUNT_PASSWORD lokal (minimal12 karakter). Tidak ada password akun tetap di Git.
- `php scripts/blackbox.php status` memeriksa nama DB aktual dan grants sebelum query data.
- `php scripts/blackbox.php setup` hanya pada schema kosong; menjalankan migrasi forward dan fixture sintetis, tanpa reset/wipe. Setup ulang ditolak tanpa menimpa hasil penguji.
- `php scripts/blackbox.php serve --port=8086` untuk aplikasi manual. Session/key/cache/log/bukti berada di storage/blackbox dan cookie sendiri. Jangan memakai raw artisan --env=blackbox, composer setup, atau kredensial utama.
- Akun awal: bb.pemilik, bb.kasir.a (A), bb.kasir.b (B); password semuanya nilai BLACKBOX_ACCOUNT_PASSWORD lokal. Nilai acuan cashA40000/expected140000, min2/tarif8000 dan order0,5kg/4000 tersedia.
- [Urutan52](docs/blackbox/execution-order.md) sengaja menempatkan saldo baseline sebelum pembayaran baru. Setelah BB-25, `php scripts/blackbox.php advanced` menambah fixture historis/retry/overlap/usaha asing sekali tanpa reset data lama.
- Salin [template hasil52](docs/blackbox/results-template.csv) ke storage/blackbox/evidence/<run-id>/hasil.csv; semua status awal Belum diuji manual dan hasil aktual kosong. Nama gambar: BB-41-01-kasir-a-desktop-input.png, lalu BB-41-02-kasir-a-desktop-total.png; ulangi dengan nomor percobaan/run baru, jangan timpa bukti lama.

Pemeriksaan teknis otomatis setup/grant/konfigurasi, PHPUnit dan login browser dicatat terpisah di docs/blackbox. Tidak ada hasil Lulus/Gagal manual diisi. Beberapa prasyarat negatif/nonaktif memerlukan persiapan lewat DB GUI dengan akun manual terbatas; catat Terhambat bila belum tersedia. Baca catatan dependensi setiap kasus, terutama historis dan shift final.
