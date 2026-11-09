# Spesifikasi Redesign UI/UX Laundryku

Tanggal: 2026-11-09
Status: siap diimplementasikan oleh Codex; belum ada perubahan visual.
Tujuan: memperbaiki kegunaan antarmuka untuk pemilik dan kasir UMKM laundry tanpa mengubah aturan bisnis.

## Arah visual
- Identitas Laundryku: profesional, bersih, mudah dibaca, tidak berlebihan.
- Warna: navy #142D4E, teal #0F8B8D, latar #F5F7FA, teks #172B4D, putih #FFFFFF. Gunakan warna status yang dapat dibedakan.
- Gunakan font sans-serif yang terbaca, ukuran dasar 14–16px, hierarki judul jelas, spasi konsisten.
- Buat logo vektor orisinal sederhana berupa simbol pakaian/gelembung dan tulisan Laundryku; simpan sebagai SVG lokal, sertakan favicon orisinal. Jangan memakai logo merek lain.
- Sistem komponen reusable: tombol, badge status, card KPI, input, select, tabel, empty state, alert, modal konfirmasi, breadcrumb, pagination.
- Ikon konsisten dari satu set ikon yang legal, lebih baik SVG inline/lokal, tanpa dependensi CDN untuk fungsi inti.

## Prioritas layar
1. Layout utama: sidebar dengan ikon dan pengelompokan menu Pemilik/Kasir, topbar, identitas cabang dan peran, logout, navigasi aktif, sidebar mobile yang dapat dibuka/ditutup.
2. Login dan setup: identitas visual yang konsisten, label form, validasi jelas, fokus keyboard.
3. Dashboard: ringkasan pesanan hari ini, status pesanan, pembayaran belum lunas, siap diambil, terlambat, stok menipis, pendapatan; CTA kontekstual.
4. Pesanan: daftar dengan filter/pencarian, status jelas, tombol tambah pesanan dominan; formulir input pelanggan, layanan, berat, estimasi, dan ringkasan total mudah dipahami.
5. Detail pesanan: timeline status, pembayaran, nota, riwayat aksi; konfirmasi tindakan berisiko.
6. Shift dan pembayaran: saldo awal, uang tunai diterima, saldo seharusnya, saldo aktual, selisih, konfirmasi digital, pengajuan ulang, rekonsiliasi.
7. Pelanggan, layanan, kasir, stok, pengeluaran, laporan, audit: desain tabel dan form konsisten; kondisi data kosong dan validasi.
8. Nota thermal 58/80mm: pertahankan CSS cetak dan keterbacaan; jangan masukkan elemen dashboard ke print.

## Prinsip UX
- Terapkan 10 Nielsen heuristics: status sistem terlihat, istilah operasional sederhana, kontrol pengguna, konsistensi, pencegahan kesalahan, pengenalan, efisiensi, minimalisme, pesan error jelas, bantuan kontekstual.
- Target WCAG 2.2 AA: kontras, fokus terlihat, label form, pesan error, navigasi keyboard, target sentuh memadai, semantik HTML.
- Responsif desktop/tablet/ponsel; tabel besar boleh scroll horizontal dengan indikator.
- Loading, success, error, empty state jelas. Hindari menghilangkan data input saat validasi gagal.
- Animasi hanya transisi 120–200ms untuk hover/fokus, sidebar dan feedback ringan. Hormati prefers-reduced-motion. Jangan memakai animasi yang menghambat transaksi.
- Hindari chart dekoratif, gambar stok, gradient berlebihan, glassmorphism, dan efek berat.

## Batas implementasi
- Pertahankan Laravel 12, Blade, route, controller, validasi, peran, isolasi usaha/cabang, kalkulasi pembayaran dan shift.
- Gunakan CSS/JS lokal yang dibundel melalui Vite jika sesuai; pastikan npm run build menghasilkan public/build dan tidak tergantung dev server untuk penggunaan biasa.
- Jangan menambahkan CDN eksternal sebagai dependensi kritis.
- Refactor CSS inline di layout menjadi stylesheet terstruktur; komponen Blade reusable bila praktis.
- Jangan mengubah schema database, seed data, data nyata, atau aturan bisnis.
- Gunakan desain orisinal, bukan menyalin tampilan merek lain.
- Jangan memasukkan logo atau font pihak ketiga tanpa lisensi sesuai.

## Kriteria penerimaan
- Semua halaman pemilik/kasir yang ada tetap bisa diakses sesuai peran.
- Login/setup dan dashboard terlihat konsisten.
- Tidak ada horizontal overflow tak disengaja pada lebar 375px, 768px, 1280px, kecuali tabel yang memiliki area scroll.
- Sidebar mobile dapat dioperasikan dengan keyboard dan memiliki label aksesibel.
- Status pesanan dan pembayaran dibedakan dengan teks, bukan hanya warna.
- Error validasi terlihat dekat field dan dapat dipahami.
- Nota tetap dapat dicetak 58/80mm.
- npm run build dan php artisan test lulus, php artisan view:cache lulus.
- Screenshot sebelum/sesudah untuk login, dashboard pemilik, dashboard kasir, pesanan, pembayaran, stok, laporan, mobile.
- Perbarui PROGRESS.md dan CHANGELOG.md. Buat PR terpisah; jangan merge otomatis.

## Urutan pengerjaan
Tahap A: design tokens, logo, layout, navigasi, login/setup.
Tahap B: dashboard, pesanan, detail, pembayaran dan shift.
Tahap C: tabel/form modul lain, laporan, stok, responsif, aksesibilitas, regresi.
Jika pekerjaan terlalu besar, pecah menjadi beberapa PR agar mudah diaudit.
