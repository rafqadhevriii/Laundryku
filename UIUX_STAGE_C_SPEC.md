# Laundryku UI/UX Tahap C: modul administrasi dan laporan

Tanggal: 2026-11-09. Baseline: main setelah PR #5 (cabang aktif).
Tujuan: menyelesaikan konsistensi antarmuka, tanpa mengubah perhitungan, database, aturan keuangan, dan isolasi cabang.

## Ruang lingkup
1. Pelanggan: daftar, pencarian, formulir tambah/edit, validasi, pesan sukses, empty state, konteks cabang aktif read-only, informasi kontak dan catatan jelas.
2. Stok: ringkasan stok menipis yang berasal dari data nyata, tabel dengan badge jumlah/status, formulir barang masuk/keluar/koreksi, status persetujuan, penjelasan unit dan history apabila sudah tersedia dari controller. Jangan membuat angka fiktif.
3. Pengeluaran: list, filter jika tersedia, form kategori/tanggal/jumlah, konteks cabang aktif, validasi dan feedback.
4. Layanan dan harga: list, formulir tambah/edit, unit kg/item, tarif/estimasi, validasi dan kejelasan status aktif. Tidak mengubah rumus harga/minimum berat.
5. Akun kasir: daftar, buat akun, status aktif, penugasan cabang, persyaratan form dan error; pertahankan otorisasi owner.
6. Laporan pendapatan, laporan operasional, ekspor CSV/PDF/Excel, rekonsiliasi, audit: hierarki informasi, tabel responsif, periode/cabang aktif, status/empty state, tombol ekspor sesuai endpoint nyata. Jangan menambah chart atau statistik yang tidak tersedia.
7. Semua layar harus menggunakan identitas/logo/nav dan tokens Tahap A, komponen reusable Tahap B, serta sistem cabang aktif PR #5.

## Prinsip khusus
- Tampilkan Semua Cabang untuk ringkasan dan daftar yang diizinkan; formulir pembuatan data per cabang menampilkan gate memilih cabang melalui header.
- Cabang kasir selalu mengikuti penugasan. Tidak ada dropdown cabang berulang untuk pesanan, pelanggan, stok atau pengeluaran.
- Pertahankan nama input, route, CSRF, method dan validasi server. Jangan memindahkan aturan bisnis ke JavaScript.
- Fokus WCAG 2.2 AA: keyboard, kontras, label, feedback, status bukan warna saja, reduced-motion, responsif 375/768/1280px. Area tabel besar boleh scroll berlabel.
- Alur cepat untuk kasir, visualisasi informasi penting bagi pemilik. Animasi ringan <=200ms.
- Jangan mengubah nota thermal 58/80mm kecuali memperbaiki bug yang disetujui dalam PR terpisah.
- Jangan mengubah migration, seeder, struktur DB, payment/shift logic, multi-business security. Catat temuan logika bisnis sebagai issue/PR lain.

## Acceptance tests
- npm ci, npm run build, php artisan view:cache, php artisan test, git diff --check lulus.
- Browser check desktop/mobile pada semua modul yang berubah, baik Pemilik dan Kasir, data kosong/terisi/validasi.
- Cek izin 403 bagi Kasir untuk menu Pemilik, multi-cabang filter dan ekspor sesuai konteks yang tersimpan.
- Jalankan axe otomatis pada layar prioritas, laporkan batas cakupannya.
- Simpan screenshot before/after dan catatan hasil di docs/uiux/stage-c/README.md.
- Perbarui PROGRESS.md dan CHANGELOG.md. Kerjakan branch baru dari main, buat PR, jangan merge otomatis.

## Fokus setelah Tahap C
Pengujian Black Box manual menggunakan BLACKBOX_TEST_PLAN.md; audit fungsional minimum_weight layanan dan rincian kas masuk shift aktif dipisah dari PR desain.
