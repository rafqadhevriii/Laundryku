# Laundryku UI/UX Tahap B: Dashboard, Pesanan, Pembayaran, Shift

Tanggal: 2026-11-09. Dasar: UIUX_REDESIGN_SPEC.md dan Tahap A pada main.

## Tujuan
Memperbaiki pengalaman operasional kasir dan pemilik tanpa mengubah route, validasi, logika bisnis, database, kalkulasi, otorisasi, maupun ruang lingkup cabang.

## Lingkup dan prioritas
1. Dashboard (resources/views/dashboard/index.blade.php): kartu KPI reusable dengan angka utama, label, konteks periode/cabang, status dan CTA; tampilkan data yang benar-benar disediakan controller; empty state yang bermakna; prioritas pesanan terlambat, siap diambil, belum dibayar, stok menipis. Jangan menciptakan angka atau grafik dekoratif.
2. Daftar pesanan (orders/index.blade.php): header aksi tambah pesanan, filter dan pencarian, badge status dengan teks, tabel responsif yang tetap mudah dipindai, indikator pembayaran, empty state, pagination dan tautan detail. Pertahankan nama field/filter/query.
3. Buat pesanan (orders/create.blade.php): kelompokkan pelanggan, layanan/jumlah/berat, estimasi, ringkasan harga. Tampilkan bantuan validasi dan biaya secara jelas; jangan hitung total berbeda dari server. Jangan menghilangkan input saat gagal validasi. Periksa aturan minimum berat dan layanan gabungan sebelum mengubah tampilan.
4. Detail pesanan (orders/show.blade.php): ringkasan identitas pesanan, status/timeline, pembayaran dan riwayat percobaan, tindakan berikutnya dan nota. Bedakan pembayaran pending/paid/rejected, tampilkan konfirmasi tindakan berisiko tanpa mengubah endpoint dan kebijakan role. Jangan tampilkan tombol yang tidak berhak digunakan.
5. Shift kasir (shifts/index.blade.php): tampilkan status shift, saldo awal, kas masuk, saldo seharusnya, saldo aktual, selisih, formulir buka/tutup/review dengan label jelas dan konfirmasi sesuai aturan yang ada. Jelaskan transfer/QRIS terpisah dari uang tunai.
6. Komponen reusable Blade untuk KPI, badge status, empty state, section header dan form feedback; pakai tokens/ikon Tahap A, jangan duplikasi stylesheet inline.

## UX dan kualitas
- Mobile 375px, tablet 768px, desktop 1280px, keyboard, fokus terlihat, semantik HTML, kontras WCAG 2.2 AA, target sentuh memadai.
- Status dibedakan oleh teks selain warna. Animasi <=200ms dan prefers-reduced-motion.
- Hindari modal yang memaksa untuk aksi biasa; gunakan konfirmasi untuk tindakan yang tidak mudah dibatalkan.
- Pertahankan nota cetak thermal 58/80mm tanpa shell aplikasi.
- Hindari dependensi CDN, library chart berat, API baru dan migrasi.
- UI dalam bahasa Indonesia sederhana untuk kasir laundry.
- Pertahankan filter cabang dan hak akses pemilik/kasir; jangan bocorkan data lintas cabang.
- Jika ada ketidaksesuaian data/controller, catat dan ajukan PR terpisah; jangan ubah logika bisnis dalam PR desain.

## Verifikasi dan bukti
- npm ci, npm run build, php artisan view:cache, php artisan test, git diff --check.
- Uji browser login, dashboard pemilik/kasir, pesanan kosong/berisi, form create invalid, detail status dan pembayaran, shift buka/tutup dan tampilan mobile; gunakan database pengujian dan data sintetis saja.
- Uji aksesibilitas otomatis pada halaman Tahap B dan laporkan cakupan/keterbatasan, termasuk navigasi keyboard.
- Simpan screenshot before/after desktop/mobile dan laporan singkat docs/uiux/stage-b/README.md.
- Perbarui PROGRESS.md dan CHANGELOG.md.
- Branch terpisah dari main terbaru, PR untuk review, jangan merge otomatis.
