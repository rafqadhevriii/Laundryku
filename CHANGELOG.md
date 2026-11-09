# Catatan perubahan Laundryku

## 2026-11-09 (konteks cabang aktif)
- Menambahkan pengalih cabang Pemilik di header desktop/mobile, mode Semua Cabang dan identitas penugasan Kasir yang read-only.
- Menambahkan resolver/middleware konteks terpusat, namespace sesi akun/usaha, reset autentikasi, validasi ulang cabang aktif, CSRF dan redirect GET aman. Sesi stale menggagalkan POST mutasi; GET kembali ke agregat dengan peringatan.
- Menghapus dropdown cabang dalam form pesanan, menurunkan branch_id dari server, membatasi pelanggan, dan membersihkan input lama saat beralih cabang. Pembuatan pelanggan/stok/pengeluaran mengikuti konteks yang sama; histori tidak dipindahkan cabangnya.
- Menerapkan scope pada dashboard, pesanan/pembayaran/nota, pelanggan, persediaan/koreksi, pengeluaran, shift, rekonsiliasi dan laporan/ekspor; mempertahankan agregat Semua Cabang/filter laporan lama. Filter cabang URL bertentangan ditolak.
- Menambahkan 11 tes konteks multi-cabang: request/sesi manipulatif, stale/asing/nonaktif, akun bergantian, scope dan pembuatan, laporan serta snapshot pembayaran/shift. Seluruh **69 tes lulus / 671 assertion** di laundryku_testing; build Vite, view:cache dan diff --check lulus.
- Bukti di `docs/branch-context`: 10 screenshot desktop/mobile, hasil browser/axe dan skrip reproduksi. Keyboard, CSRF, navigasi serta pemisahan Kasir berhasil; nol temuan axe pada tampilan teruji, nol overflow/error JS. Bukan sertifikasi aksesibilitas menyeluruh.
- Tidak mengubah database utama, migration, model, atau rumus keuangan; tidak menjalankan migrasi manual. Administrasi usaha tetap business-wide dan sesi beberapa tab berbagi pilihan. PR terpisah, tidak merge otomatis.

## 2026-11-09 (UI/UX Tahap B)
- Mendesain dashboard Pemilik/Kasir dengan KPI dari data controller, konteks periode/cabang, CTA nyata, prioritas pekerjaan dan empty state. Tidak membuat angka/grafik dekoratif.
- Mendesain daftar pesanan: pencarian/status GET tetap, badge teks, tabel desktop/kartu mobile, jumlah hasil dan pagination yang menjaga query.
- Mengelompokkan form tambah pesanan, mengasosiasikan label/error, menjaga old input termasuk indeks layanan berlubang, serta menambah/hapus hingga 20 baris. Ringkasan tarif/jumlah/durasi tanpa kalkulasi uang klien; harga/total/estimasi resmi tetap dari server.
- Menambahkan timeline berbasis status, rincian snapshot, nota 58/80mm, pembayaran/riwayat percobaan, aksi sesuai state/peran, dan dialog konfirmasi transaksi/pickup/tutup-review shift. Nilai shift memakai hasil tersimpan tanpa query/agregasi keuangan baru di view.
- Komponen baru: ui/kpi, status-badge, empty-state, section-header, form-feedback, field-error, select, pagination, confirm-dialog dan orders/service-row; partial pembayaran, CSS terpusat, JS order-composer/confirm-actions lokal.
- File terkait: views dashboard/orders/shifts, layouts/app, komponen/partial tersebut, resources/css/app.css, resources/js/app.js dan dua modul JS, StageBPresentationTest, PROGRESS dan docs/uiux/stage-b (32 screenshot, laporan, hasil mesin).
- Hasil sebenarnya: npm ci/build berhasil; php artisan test **58 lulus / 517 assertions**; view:cache dan diff --check berhasil. Playwright memeriksa 14 kelompok alur dan 39 kombinasi peran/lebar layar (termasuk pagination) tanpa overflow root. axe pada 21 keadaan: 0 pelanggaran; tidak ada error JS. Bukti dan keterbatasan pada laporan Tahap B.
- Controller/route/model/middleware/validasi/database/migration/seeder/aturan bisnis tidak diubah; database utama tidak diakses. Minimum berat yang belum diterapkan dan rincian kas masuk yang belum disediakan controller dicatat untuk PR terpisah, tanpa mengarang data atau menambah aturan di UI.
- Belum menguji pembaca layar/pengguna/perangkat lain/printer fisik/konkurensi multiproses. Tahap C dan tindak lanjut data tetap terpisah. PR baru, jangan merge otomatis.

## 2026-11-09 (UI/UX Tahap A)
- Menambahkan identitas SVG orisinal Laundryku (logo terang/gelap, mark, favicon) dan set ikon inline orisinal, tanpa CDN/font/aset pihak ketiga untuk fungsi inti.
- Memindahkan CSS layout ke tokens navy/teal/putih dan tipografi lokal melalui Vite. Menambahkan package-lock.json untuk npm ci; aset produksi dihasilkan pada public/build sesuai .gitignore.
- Membangun komponen Blade head/brand/icon/button/alert/field/nav-item/sidebar/topbar serta layout guest. Sidebar berkelompok per peran, indikator aktif aksesibel, konteks usaha/cabang, dan logout menggunakan route/CSRF yang sama.
- Sidebar mobile mendukung keyboard, focus trap, Escape/backdrop/tombol tutup, fokus kembali, aria-expanded, dan fallback tanpa JS. Transisi 160ms mendukung reduced motion.
- Menata ulang login/setup dengan label/error terasosiasi, old input nonrahasia, bantuan kontekstual, toggle password dan feedback submit. Menambahkan area scroll tabel berlabel serta pembungkusan tombol filter tanpa mengubah isi atau aksinya.
- File terkait: public/brand dan favicon.svg, resources/css/app.css, resources/js/app.js, components Blade, layouts app/guest, auth login/setup, package-lock.json, UiShellTest, PROGRESS.md dan docs/uiux/stage-a (laporan/34 screenshot/hasil browser).
- Hasil sebenarnya: npm run build berhasil; php artisan test **51 lulus / 456 assertions** pada laundryku_testing; view:cache dan diff --check berhasil. Playwright/Chromium memverifikasi drawer, keyboard, reduced motion, no-JS dan validasi. Tidak ada overflow root pada 39 kombinasi URL/lebar. axe tidak menemukan pelanggaran pada layar Tahap A yang diperiksa; detail dan batas cakupan pada laporan desain.
- Controller, route, validasi server, hak akses, kalkulasi, schema/migration, seeder, dan nota standalone tidak diubah. Database utama tidak diakses/diubah; fixture screenshot hanya di database pengujian.
- Belum melakukan uji manual pengguna/pembaca layar/perangkat lain/printer fisik. Tahap B/C dan audit aksesibilitas menyeluruh tetap pekerjaan berikutnya. PR terpisah, jangan merge otomatis.

## 2026-11-09 (rekonsiliasi historis dan retry digital)
- Menambahkan halaman khusus Pemilik untuk pembayaran tunai historis: filter cabang/tanggal/nomor, pengaitan eksplisit ke shift terbuka yang sesuai, pengecualian belum teralokasi dengan alasan wajib, ringkasan, dan laporan keputusan.
- Setiap keputusan direkam dengan aktor/waktu/alasan/status sebelum-sesudah/shift tujuan. Pengaitan ganda dan pengaitan ke shift final ditolak; saldo final tidak dihitung ulang. Pengecualian ditinjau tidak lagi memblokir penutupan dan tetap tidak masuk kas shift. Pendapatan tetap menghitung pembayaran lunas sekali.
- Mengizinkan retry rejected oleh Kasir dalam cabangnya dan Pemilik dalam usahanya. Sesuai spesifikasi, hanya transfer/QRIS, selalu pending, nominal dari total pesanan; konfirmasi ulang Pemilik tetap wajib. Endpoint lama juga menerapkan aturan ini, termasuk menolak penggantian rejected menjadi tunai.
- Menambahkan riwayat payment_attempts untuk menjaga referensi, catatan awal, alasan penolakan, aktor dan waktu setiap percobaan. Snapshot pembayaran lama dibuat saat ditindaklanjuti tanpa menebak riwayat yang sudah tidak tersedia. Tampilan pesanan mempertahankan alasan penolakan dan riwayat setelah retry.
- Migrasi tambahan hanya disiapkan: kolom rekonsiliasi nullable dan tabel percobaan, tanpa backfill/perubahan/penghapusan data nyata. Tidak menjalankan migrasi manual atau mengakses database utama.
- File: controller PaymentReconciliation/Payment/CashShift/Order, model Payment/PaymentAttempt, migrasi 160000, routes, view payments/reconciliation dan orders/show/layout, PaymentReconciliationTest, DigitalPaymentRetryTest, CashPaymentShiftTest, PaymentFixtures, pengaman TestCase/phpunit, PROGRESS, AUDIT_PAYMENT_SHIFT, SPEC_RECONCILIATION_RETRY, dan BLACKBOX_TEST_PLAN.
- Hasil sebenarnya: **46 tes lulus, 401 assertions** pada laundryku_testing terisolasi (PHP 8.5.12/MariaDB 11), view:cache dan diff --check berhasil. Uji browser otomatis Playwright/Chromium berhasil untuk rekonsiliasi, validasi alasan, retry, riwayat, akses Kasir 403, dan konfirmasi ulang.
- Keterbatasan: belum diuji manual oleh pengguna/printer atau konkurensi multiproses; pengaitan/koreksi saldo shift final tidak tersedia. Lanjutkan review PR, pengujian pengguna, dan rencana penerapan; jangan merge otomatis.

## 2026-11-09 (perbaikan pembayaran tunai dan shift)
- Mengikat setiap pembayaran tunai baru ke satu `cash_shift_id`. Kasir harus memiliki shift aktif sendiri; Pemilik memilih shift aktif pada usaha/cabang pesanan.
- Penutupan menjumlah pembayaran berdasarkan ID shift, sehingga shift kasir tumpang tindih tidak menghitung uang yang sama. Penguncian baris shift menyelaraskan pembayaran dengan penutupan; perlindungan pembayaran ganda tetap berlaku.
- Migrasi tambahan menambah foreign key nullable tanpa mengubah/menghapus pembayaran historis atau menghitung ulang shift tertutup. Penutupan shift lama yang masih memiliki pembayaran tanpa kaitan ditolak untuk mencegah kekurangan perhitungan. Tidak menjalankan migrasi pada database utama.
- File: PaymentController, CashShiftController, OrderController, Payment, CashShift, orders/show.blade.php, migrasi add_cash_shift_id_to_payments_table, CashPaymentShiftTest, OperationalFlowTest, PROGRESS.md.
- Validasi sebenarnya: `php artisan test --compact` **29 lulus, 220 assertions** pada `laundryku_testing` terisolasi (PHP 8.5.12/MariaDB 11); `php artisan view:cache` dan `git diff --check` berhasil. Tes regresi terbukti gagal dengan perhitungan shift lama (148000 dibanding 124000 yang semestinya).
- Keterbatasan: belum diuji browser/printer atau konkurensi multiproses; data historis ambigu tidak dipasangkan otomatis. Pengeluaran belum diikat ke kas shift; keputusan pengajuan ulang digital oleh Kasir tidak diubah.
- Berikutnya: review PR, uji manual dua kasir/cabang dan skenario penerapan; tutup/review shift lama pada masa pemeliharaan sebelum migrasi. Detail penerapan dan rekonsiliasi ada di PROGRESS.md.

## 2026-11-09 (spesifikasi lanjutan)
- Menyusun SPEC_RECONCILIATION_RETRY.md untuk pekerjaan Codex berikutnya. Belum ada perubahan logika aplikasi.

## 2026-11-09 (audit lanjutan)
- Menjalankan tes otomatis: 20 lulus, 105 assertions.
- Menambahkan AUDIT_PAYMENT_SHIFT.md untuk mencatat risiko shift tumpang tindih dan pengajuan ulang pembayaran digital.
- Belum ada perubahan kode bisnis.

## 2026-11-09 (lanjutan)
- Menambahkan BLACKBOX_TEST_PLAN.md berisi 25 skenario pengujian manual dan format pencatatan hasil untuk TA.
- Memperbarui PROGRESS.md; pengujian manual belum dilakukan.

## 2026-11-09
- Menyiapkan panduan AGENTS.md dan PROGRESS.md untuk kerja bersama ChatGPT dan Codex.
- Menyiapkan repository Git lokal dan perlindungan file database lokal.
- Fitur sebelumnya: dashboard, laporan pendapatan PDF/Excel/CSV, laporan operasional Excel, pengujian otorisasi, cabang, pembayaran, shift, dan stok.

## Panduan pembaruan
Setiap pekerjaan Codex harus menambahkan tanggal, perubahan penting, file terkait, hasil pengujian, dan masalah yang belum selesai di sini.
