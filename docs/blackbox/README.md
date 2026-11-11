# Lingkungan Black Box manual Laundryku

Disiapkan 2026-11-10. **Persiapan, bukan hasil 52 pengujian manual.** Database manual: `laundryku_blackbox`. Database utama `laundryku` dan PHPUnit `laundryku_testing` tidak dipakai oleh launcher ini.

## Cara menjalankan (Laragon/Linux)

Gunakan checkout terpisah bila aplikasi utama sedang berjalan. Instal dependency dengan `composer install` dan `npm ci && npm run build`. **Jangan memakai `composer setup`** karena script bawaan menjalankan migrasi pada default environment. Jangan menjalankan `php artisan migrate`, `db:seed`, `migrate:fresh`, `db:wipe` atau `php artisan test` dalam profil manual.

Administrator DB menyiapkan schema kosong dan akun khusus. SQL berikut hanya provisioning awal manual; ganti password placeholder secara lokal, jangan commit kredensial dan jangan memakai user root untuk aplikasi:

```sql
CREATE DATABASE laundryku_blackbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laundryku_blackbox_user'@'localhost' IDENTIFIED BY '<PASSWORD_DB_LOKAL_UNIK>';
GRANT ALL PRIVILEGES ON `laundryku\_blackbox`.* TO 'laundryku_blackbox_user'@'localhost';
```

Backslash sebelum underscore wajib: schema grant MySQL/MariaDB dapat memperlakukan underscore sebagai wildcard. Jangan berikan global privilege, role/proxy, akses schema lain, atau GRANT OPTION. Untuk Docker gunakan host account yang sesuai (misalnya `%` hanya pada network Docker yang terisolasi, DB tidak dipublikasikan), tetap schema grant literal di atas. Setup memeriksa grants dan menolak akun yang terlalu luas, termasuk root.

1. Salin `.env.blackbox.example` menjadi `.env.blackbox` (diabaikan Git). Isi BLACKBOX_DB_HOST/PORT/USERNAME/PASSWORD, opsional SOCKET, serta BLACKBOX_ACCOUNT_PASSWORD minimal12 karakter. APP_URL harus loopback localhost/127.0.0.1 (default port8086). Jangan salin .env utama atau kredensial utama.
2. Jalankan `php scripts/blackbox.php status`. Environment/database harus blackbox/laundryku_blackbox, prepared=false sebelum setup. Bila koneksi ditolak, perbaiki konfigurasi/hak akun; jangan melemahkan guard.
3. Jalankan **sekali** `php scripts/blackbox.php setup`. Launcher memigrasi schema kosong memakai migrasi forward yang ada dan membuat fixture dalam transaksi. Tidak menjalankan DatabaseSeeder umum. Simpan JSON status lokal untuk ID order/shift.
4. Jalankan `php scripts/blackbox.php serve --port=8086`, buka `http://127.0.0.1:8086`. Hentikan dengan Ctrl+C. Server pengembangan ini untuk pengujian lokal, bukan deployment produksi.
5. Untuk container, `--host=0.0.0.0` tersedia secara eksplisit; publikasikan port host hanya `127.0.0.1:8086:8086`. Default tetap loopback. CLI hanya mengizinkan setup/advanced/status/serve serta port/host terbatas; tidak ada penerusan command Artisan sembarang.
6. Ikuti [urutan52](execution-order.md) dan salin [template CSV](results-template.csv) ke `storage/blackbox/evidence/<run-id>/hasil.csv`. Folder ini diabaikan Git. Isi hasil aktual hanya setelah tindakan nyata.

Password ketiga akun adalah **nilai BLACKBOX_ACCOUNT_PASSWORD lokal yang Anda pilih**, tidak dicetak oleh setup/status. Kunci APP_KEY dibuat acak sekali di `storage/blackbox/app.key`, bukan kunci aplikasi utama. Tidak ada password default yang harus disebarkan di repository.

| Akun contoh awal | Peran | Cabang |
| --- | --- | --- |
| bb.pemilik | Pemilik | Semua Cabang / A / B melalui header |
| bb.kasir.a | Kasir | Cabang Uji A |
| bb.kasir.b | Kasir | Cabang Uji B |

## Fixture awal dan nilai acuan

- Satu usaha sintetis `BBOX-MANUAL-V1`, satu Pemilik, dua cabang, dua Kasir, empat pelanggan tanpa kontak nyata.
- Cuci Kering Uji: kg8000/min2; Setrika Item Uji: item15000; Paket Tunai25 Uji: item25000 agar contoh penerimaan25000 tidak memakai quantity pecahan ilegal.
- Deterjen A2liter/min5 dan B20liter/min5. Tidak ada koreksi pending awal; penguji membuatnya pada BB-19.
- Shift A/B open100000. A mempunyai tepat dua cash/paid linked25000+15000: **cash40000, expected saat ini140000**. B tidak memiliki cash linked pada baseline.
- Enam pesanan: `BB-A-MIN`0,5kg/4000 belum dibayar; `BB-A-CASH25`25000 dan `BB-A-CASH15`15000 sudah cash linked; `BB-A-LATE`2,5kg/20000 terlambat; `BB-B-UNPAID`8000; `BB-B-DIGITAL`transfer/paid30000 tanpa kaitan tunai.
- Pendapatan awal70000, pengeluaran0; sesudah operator bertransaksi, laporan tentu berubah. Bayar ganda diuji pada order yang sudah lunas tanpa membuat pembayaran kedua.

Fixture dibuat langsung untuk menyediakan prasyarat sintetis, bukan bukti bahwa tindakan manual/alur pembayaran sudah diuji. Audit menyebut persiapan fixture, bukan hasil Lulus.

## Fixture lanjutan untuk BB-26–BB-40/48–49

Sesudah tahap dasar/BB-25, jalankan sekali `php scripts/blackbox.php advanced`. Ini **append-only** dalam transaksi dan menolak pengulangan; tidak memperbarui transaksi sebelumnya. Jika Kasir A sudah tidak mempunyai shift terbuka, satu shift baru100000 dibuat untuk prasyarat rekonsiliasi; shift lama tetap.

Tambahan:

- `bb.kasir.overlap` di A dengan shift open50000, satu shift A reviewed/final, lima cash/paid historis A tanpa shift (`BB-H-ASSIGN`, `BB-H-UNALLOC`, `BB-H-BLOCK`, `BB-H-OVERLAP`, `BB-H-LATER`) dan satu B (`BB-H-B`). Semuanya15000; belum direkonsiliasi dan dapat memblokir close sampai ditinjau.
- Transfer/QRIS rejected `BB-RETRY-TRANSFER`, `BB-RETRY-QRIS`, `BB-RETRY-LOOP`, satu transfer pending `BB-DIGITAL-PENDING`. Arsip attempt lama dibuat oleh aplikasi saat retry, mengikuti kompatibilitas legacy yang sudah ada.
- Usaha/cabang asing sintetis dengan `bb.pemilik.asing`, `bb.kasir.asing`, shift terpisah dan `BB-FOREIGN-RETRY` untuk pengujian penolakan lintas usaha. Password tetap nilai lokal yang sama. Profil lanjutan total2Pemilik/4Kasir/3cabang/2usaha; **profil awal tetap1Pemilik/2Kasir/2cabang**.

Fixture negatif seperti cash pending/rejected atau akun nonaktif tidak dihasilkan UI normal; siapkan hanya lewat DB GUI dengan akun manual terbatas sebagai prasyarat yang dicatat, bukan memodifikasi kode/DB utama. Ikuti catatan urutan; bila prasyarat tidak tersedia tandai Terhambat, bukan Lulus.

## Pengaman dan pengulangan

- Hanya `.env.blackbox` yang diparse dengan whitelist; APP_ENV wajib **blackbox**, bukan testing, sehingga tidak tertukar profil PHPUnit. Nama DB hardcoded pada koneksi `blackbox`; DB_DATABASE harus marker exact laundryku_blackbox, DB_URL/read-write override ditolak.
- Generator fixture juga menjalankan guard ketika dipanggil langsung dari luar launcher; tes memastikan panggilan di environment PHPUnit ditolak sebelum satu query pun.
- Sebelum migrasi/fixture/query operasional: verifikasi konfigurasi, actual SELECT DATABASE(), dan SHOW GRANTS. Server DB sendiri menolak write ke schema lain. Akun aplikasi tidak memiliki hak primary/PHPUnit.
- Config/route cache bersama tidak dibaca; launcher memakai path tersendiri dan menolak config/route cache manual yang tak didukung. Semua key, package/service cache, session, rate-limit cache, view, log dan bukti berada di storage/blackbox. Cookie `laundryku_blackbox_session`; mail memakai log, queue sync. .env utama tidak dimuat.
- Guard runtime aktif pada mode blackbox; akses melalui launcher/router melakukan pemeriksaan yang sama. Jangan menjalankan raw `artisan --env=blackbox` atau public/index.php sebagai pengganti launcher.
- Setup memakai advisory lock DB; **schema harus kosong**. Database yang memiliki tabel, termasuk setup sebelumnya/partial failure, ditolak tanpa wipe/reset/rollback. Advanced juga menolak data lanjut yang sudah ada. Hasil operator tidak ditimpa.
- DDL MySQL bukan transaksi penuh. Bila migrasi terhenti, schema parsial dibiarkan untuk ditinjau, tidak dihapus otomatis. Simpan bukti/data, gunakan instance manual baru yang kosong bila perlu mengulang dari nol; administrator meninjau pemulihan manual secara terpisah. Tidak ada command reset atau opsi target database lain di mekanisme ini.
- PHPUnit tetap berjalan pada laundryku_testing melalui phpunit.xml/TestCase asli dan tidak mengenal fixture manual. Jangan menjalankan suite RefreshDatabase dengan kredensial manual; TestCase akan menolak DB manual.

## Bukti dan format hasil

Gunakan run-id `2026-11-10-penguji-01` (contoh nama saja), kemudian:

```text
storage/blackbox/evidence/<run-id>/hasil.csv
storage/blackbox/evidence/<run-id>/BB-41/BB-41-01-kasir-a-desktop-input.png
storage/blackbox/evidence/<run-id>/BB-41/BB-41-02-kasir-a-desktop-total.png
storage/blackbox/evidence/<run-id>/BB-41/BB-41-03-kasir-a-mobile-total.png
```

Simpan screenshot setelah tindakan yang benar-benar terlihat. Catat tanggal/jam+zona, penguji, role, browser/perangkat/lebar, fixture/order/shiftID, langkah/input, harapan, hasil aktual, status, lokasi gambar dan catatan. Untuk pengulangan gunakan nomor percobaan/run baru, pertahankan bukti lama. Jangan unggah data nyata, password, token atau dump database. Template mempunyai52 ID unik dan **semua status Belum diuji manual**, hasil aktual/tanggal/penguji/screenshot kosong.

## Verifikasi otomatis yang benar-benar dilakukan

[Hasil setup](setup-verification.json), [log PHPUnit](automated-tests.txt), [smoke browser](browser-smoke.json). Ini pemeriksaan teknis otomatis, **bukan pengisian hasil52 skenario manual**.

- Setup di schema kosong laundryku_blackbox berhasil; fixture awal3akun/6pesanan/2shift dan cashA40000 diperiksa.
- Setup ulang ditolak, status/fixture tidak berubah; konfigurasi laundryku/laundryku_testing dan root ditolak sebelum setup.
- Pada server MariaDB manual **disposable terpisah**, dua schema canary sintetis bernama laundryku dan laundryku_testing dipakai untuk membuktikan engine menolak UPDATE oleh akun manual; keduanya tetap utuh. Ini bukan database operasional atau server PHPUnit sebenarnya.
- Seluruh **98 tes / 1.120 assertion** berjalan pada server PHPUnit/laundryku_testing; status fixture manual sebelum/sesudah sama. Advanced berhasil dan pengulangan ditolak tanpa perubahan.
- Browser otomatis login3akun awal, memeriksa cookie sesi terpisah, cash140000 di A, serta daftar pesanan A/B tidak tercampur. Tidak ada hasil manual diisi. view:cache dan diff --check berhasil.
- Lingkungan verifikasi sudah mengaktifkan advanced untuk menguji pengamannya. Operator yang menjalankan setup pada schema kosong akan mendapat profil awal; jangan mereset instance berisi bukti untuk mengembalikannya.

Belum ada UAT/hasil manual BB-01–BB-52, pemeriksaan printer fisik atau pembaca layar. Konkurensi setup dilindungi advisory lock, tetapi belum ada stress test multiproses. Berikutnya operator menjalankan urutan dan mengisi bukti aktual. Jangan merge otomatis.
