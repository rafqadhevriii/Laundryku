# Audit UI/UX Laundryku: Studio UI
Tanggal 11 November 2026

## Tujuan
Memudahkan kasir membuat dan mencari pesanan dalam sedikit langkah, sekaligus memberikan dashboard pemilik yang ringkas. Desain mengutamakan konsistensi, responsivitas, dan aksesibilitas. Aplikasi disiapkan untuk demonstrasi TA dan evaluasi produk; bukan deklarasi siap dijual tanpa audit lanjutan.

## Hasil audit dan perbaikan
| Temuan awal | Perbaikan | Verifikasi |
| --- | --- | --- |
| Menu terlalu banyak dan semua terlihat | Menu utama hanya Beranda, Pesanan, Pelanggan; fitur lain masuk grup Operasional, Laporan, Pengaturan | Blade dan pemeriksaan browser |
| Pilihan cabang dan nama cabang berulang pada dashboard | Pemilih lokasi dipindah ke menu akun pemilik; bukan di header atau sidebar | Browser desktop dan mobile |
| Tombol Buat pesanan dapat terhambat ketika pemilik di Semua Cabang | Tombol membuka pemilih lokasi kontekstual. Lokasi harus dipilih, lalu dialihkan langsung ke form pesanan | Pemeriksaan interaksi browser dan pengujian otorisasi |
| Judul dan deskripsi diulang | Satu judul utama per halaman, deskripsi dekoratif dikurangi | Screenshot halaman Beranda, Pesanan, Pelanggan |
| Dashboard terlalu padat dan warna seragam | Empat statistik penting, satu grafik 7 hari, daftar tindakan mendesak, lima pesanan terbaru; aksen warna ungu, biru, jingga dan hijau | Screenshot dan tes data |
| Tabel pelanggan tidak nyaman pada HP | Kartu pelanggan responsif dengan tindakan edit | Screenshot lebar 390px |
| Pencarian hanya bisa dilakukan di halaman Pesanan | Pencarian global mengarah ke daftar pesanan; shortcut '/' pada desktop | Route yang sudah ada, JS berprogresif |
| Interaksi terasa statis | Hover/focus, transisi navigasi, animasi grafik, umpan balik aksi | CSS, JavaScript, pemeriksaan browser |
| Perlindungan pengguna rentan terganggu saat ganti UI | Semua aturan role, isolasi usaha/cabang, CSRF, dan nota printer tetap di server | Tes PHPUnit |

## Prinsip UX
- Visibilitas status: indikator status dan tindakan yang membutuhkan perhatian tetap tampil.
- Kesesuaian bahasa dengan pengguna: "Buat pesanan", "Pelanggan", "Butuh tindakan".
- Kontrol pengguna: grup dapat dibuka sesuai kebutuhan, profil punya pengaturan dan keluar akun.
- Pencegahan kesalahan: input lokasi diwajibkan bila tindakan membutuhkan cabang. Pemilik tidak boleh membuat transaksi ke cabang asing. Kasir tetap dibatasi cabang yang ditugaskan.
- Konsistensi: tindakan utama berwarna ungu; status sukses hijau, peringatan jingga; layout mengikuti grid.
- Aksesibilitas: fokus terlihat, label formulir, keyboard, pengurangan animasi pada prefers-reduced-motion, tabel tetap dapat dinavigasi, teks status tidak hanya warna.

## Pemeriksaan yang dilakukan
- Build Vite dan cache Blade.
- Seluruh tes fitur Laravel, termasuk pengujian baru konteks dashboard.
- Pemeriksaan halaman nyata browser desktop 1366px dan HP 390px, kontrol horizontal overflow.
- Uji tombol Buat pesanan membuka menu akun dan pemilih lokasi.
- Kode aplikasi utama tidak memodifikasi database operasional. Browser uji memakai database terisolasi `laundryku_blackbox`.

## Batasan sebelum dijual kepada pengguna umum
- 52 pengujian Black Box manual beserta screenshot belum selesai.
- Audit aksesibilitas manual lengkap WCAG 2.2, termasuk screen reader dan navigasi keyboard di seluruh halaman, masih diperlukan.
- Audit performa perangkat lambat, perangkat cetak nota 58/80 mm, dan uji pengguna kasir sungguhan masih diperlukan.
- Uji keamanan produksi, backup/pemulihan, integrasi pembayaran (masih manual), dan deployment memerlukan proses tersendiri.
- Penampilan berkualitas produk tidak berarti seluruh aspek operasional komersial sudah dinyatakan siap.

Bukti screenshot browser lokal: `storage/blackbox/evidence/visual-dashboard-v3/`, tidak diunggah karena memuat data contoh uji.
