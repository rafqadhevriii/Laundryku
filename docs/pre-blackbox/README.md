# Penyempurnaan sebelum Black Box

2026-11-10. Branch `feat/pre-blackbox-refinements`, basis main `41f47f0` setelah PR UI/UX Tahap C. Spesifikasi: SPEC_PRE_BLACKBOX_FIXES.md.

## Minimum berat adalah informasi

Pilihan layanan kilogram menampilkan minimum. Setelah dipilih, baris dan ringkasan memberi informasi bahwa berat di bawahnya tetap boleh dan tagihan memakai berat asli. JavaScript tidak mengganti quantity/min menjadi minimum layanan dan tidak menghitung total uang resmi; total tetap dari OrderController. Tanpa JavaScript, pilihan layanan dan petunjuk statis tetap memuat minimum/aturan asli. Unit item tetap bilangan bulat dan tidak memakai minimum kg.

Contoh teruji: minimum2 kg/tarif8000, quantity0,5 menghasilkan **Rp4000**, quantity2,5 menghasilkan **Rp20000**. Gabungan 0,5 kg dan 2 barang bertarif15000 menghasilkan **Rp34000**. Manipulasi field minimum/total klien tidak menentukan uang server. Snapshot quantity/harga/subtotal pesanan lama tetap utuh ketika konfigurasi layanan berubah.

Item pesanan belum memiliki snapshot minimum_weight. Karena itu detail/nota tidak diberi minimum layanan terkini yang dapat menyesatkan riwayat. Nota 58/80mm tetap menampilkan berat/harga aktual dari snapshot yang tersedia. Tidak menambah schema/backfill atau migrasi; snapshot minimum opsional memerlukan evaluasi PR terpisah.

## Ringkasan kas saat halaman dimuat

CashShiftController hanya mengagregasi shift **open** dalam scope usaha/cabang dan peran. Kasir hanya melihat shift miliknya; Pemilik mempertahankan Semua Cabang/cabang terpilih. Shift cabang lama yang bukan penugasan aktif Kasir tidak disajikan sebagai shift aktif saat ini.

Query `paidCashPayments()` memakai relasi payments (cash_shift_id shift tersebut), method=cash dan status=paid; dipakai baik oleh display maupun close. Count/sum berasal dari server. Saldo saat ini = round(opening_cash + sum,2), belum disimpan sebagai expected_cash final. Tidak memasukkan digital, pending/rejected atau shift lain; atribusi historis tidak ditebak dari waktu.

Helper `unreviewedHistoricalCash()` mempertahankan predicate baseline: tunai paid tanpa shift, reconciliation_status NULL, paid_at antara opened_at dan sekarang, order pada usaha/cabang sama. Jika ada, tampilkan warning bahwa uang tidak masuk ringkasan dan Pemilik perlu merekonsiliasi; close tetap diblokir. Unallocated tidak dihitung. Assignment eksplisit oleh Pemilik masuk tepat shift tujuan, sekali.

Agregat dilakukan sekali per shift open yang ditampilkan (maksimal 15 baris riwayat, ditambah shift sendiri bila di luar halaman), plus pemeriksaan historis relevan. Shift submitted/reviewed tidak diagregasi ulang. GET tidak menulis data. Pembatasan Kasir pada riwayat sendiri mengikuti spesifikasi; route/izin mutasi tidak diperluas.

Close tetap memegang lock shift, lalu cek historis, sum receipt, round saldo/difference, update dan audit dalam transaksi yang sama seperti baseline. Hanya ekspresi query dipindahkan ke helper. Review mempertahankan hasil final. Pengujian membuktikan pembayaran yang datang **setelah** halaman dimuat tetap masuk perhitungan close, bukan memakai angka lama dari klien.

Ringkasan memerlukan muat ulang setelah penerimaan baru; tidak ada polling atau perhitungan uang di browser. Label membedakan sementara dari final dan tidak menampilkan difference=0 fiktif pada shift open.

## Hasil pengujian sebenarnya

| Pemeriksaan | Hasil |
| --- | --- |
| npm ci | Berhasil |
| npm run build | Berhasil, Vite 7.3.7 |
| php artisan test --compact | **82 passed, 809 assertions**; PHP8.5.12/MariaDB11, laundryku_testing |
| php artisan view:cache | Berhasil |
| git diff --check | Berhasil |
| Chromium | 1280×1000 dan 375×1000, minimum/mixed submit dan shift aktif berhasil |
| Axe | 8 kondisi WCAG2 A/AA, 2.1 A/AA, 2.2AA tanpa pelanggaran |
| JS/layout | Nol pageerror dan overflow root |

Delapan tes baru di PreBlackboxRefinementsTest mencakup bawah/atas minimum, mixed units, item pecahan/kg lebih dari dua desimal, snapshot, filter method/status/shift/cabang/usaha, overlapping cashier, warning historis, unallocated/assignment, saldo kosong nyata, close138000 menghasilkan expected140000/difference-2000, review tanpa hitung ulang, dan penugasan cabang lama. Assertion StageBPresentationTest diperbarui karena placeholder rincian kas telah digantikan data nyata; assertion shift final tanpa SUM tetap dipertahankan.

[Hasil browser](browser-results.json) dan [skrip](browser-check.cjs) tersedia. BB-41–BB-52 ditambahkan ke BLACKBOX_TEST_PLAN.md sebagai **rencana manual**, bukan hasil manual yang sudah lulus. Suite otomatis tidak menggantikan UAT TA.

## Screenshot data sintetis

| Tampilan | Desktop | Mobile |
| --- | --- | --- |
| Form: 0,5kg di bawah minimum + layanan item | [Lihat](screenshots/desktop-order-minimum.jpg) | [Lihat](screenshots/mobile-order-minimum.jpg) |
| Shift aktif: tunai40000/saldo140000, warning historis | [Lihat](screenshots/desktop-active-shift.jpg) | [Lihat](screenshots/mobile-active-shift.jpg) |

Semua layar berasal dari akun Kasir Demo dan pelanggan/uang sintetis, bukan data nyata. Gambar diperiksa sebelum publikasi. Tidak ada password/token atau kontak pelanggan asli yang terlihat.

## Reproduksi dan batasan

Gunakan environment testing, database laundryku_testing dan session file untuk web. TestCase menolak database/driver lain sebelum RefreshDatabase; jangan menggunakan database utama atau menjalankan migrasi manual. Jalankan npm ci/build, php artisan test --compact dan view:cache.

Setelah suite selesai, siapkan fixture sintetis Pemilik/Kasir, Utama/Selatan, pelanggan, layanan kg minimum2/tarif8000, item15000, shift100000 dengan cash/paid linked25000+15000 dan digital30000, serta cash/paid historis belum ditinjau tanpa shift pada periode sama. Pasang Playwright/axe pada scratch node_modules dan arahkan NODE_PATH. Set PRE_BASE_URL (default localhost8085), PRE_CASHIER, PRE_OWNER, PRE_TEST_PASSWORD untuk akun uji, lalu jalankan `node docs/pre-blackbox/browser-check.cjs`. Skrip membuat pesanan sintetis untuk memeriksa total dan menulis screenshot/hasil. Jangan menjalankan RefreshDatabase bersamaan dengan browser.

Belum menguji pembaca layar, browser lain, printer/perangkat fisik atau konkurensi multiproses. Axe bukan sertifikasi menyeluruh. Database utama tidak diakses atau diubah; schema/migration/seeder tidak disentuh. Berikutnya review PR dan Black Box manual; jangan merge otomatis.
