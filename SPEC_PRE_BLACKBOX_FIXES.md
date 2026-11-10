# Spesifikasi penyempurnaan fungsional sebelum Black Box Testing

Tanggal: 2026-11-10. Dasar: main setelah PR #6. Tujuan: dua temuan audit, tanpa mengubah transaksi lama.

## Fitur 1: Minimum berat layanan (minimum_weight)
Fakta baseline:
- services.minimum_weight nullable, hanya disetel untuk unit 'kg' melalui ServiceController.
- OrderController mengalikan berat aktual x tarif. User telah menetapkan bahwa berat di bawah minimum tetap ditagih berdasarkan BERAT ASLI, bukan minimum yang dibulatkan.
- Halaman tambah pesanan saat ini belum menampilkan minimum berat layanan.

Perilaku yang wajib:
1. Pertahankan harga = berat aktual x harga per kg, termasuk bila berat aktual kurang dari minimum_weight; jangan mengenakan minimum charge, menolak berat di bawah minimum, atau mengganti berat aktual menjadi minimum.
2. Tampilkan informasi minimum_weight pada pilihan layanan kg dan ringkasan pesanan, serta di rincian/nota bila memang relevan dan tersedia; beri informasi non-blocking saat berat aktual di bawah batas. Jelaskan eksplisit harga memakai berat aktual.
3. Unit item tidak memakai minimum_weight; quantity unit item tetap bilangan bulat, unit kg hingga dua desimal.
4. Data lama dan snapshot harga pesanan tidak boleh dihitung ulang. Jika butuh snapshot minimum_weight untuk nota historis, ajukan desain migrasi nullable non-destruktif pada PR terpisah dan jangan lakukan tanpa evaluasi.
5. Gunakan validasi backend yang sudah ada, tambah tes nilai 0.5kg dengan minimum 2kg dan tarif 8000/kg => total resmi Rp4000, bukan Rp16000; contoh 2.5kg => Rp20000. Pastikan layanan gabungan tetap menjumlahkan setiap baris secara benar.

## Fitur 2: Ringkasan uang kas pada shift aktif
Fakta baseline:
- CashShiftController::close menghitung expected_cash = opening_cash + sum(Payment yang terkait cash_shift_id, method='cash', status='paid').
- Halaman shift aktif menulis 'Rincian belum tersedia' untuk kas masuk.
- Rekonsiliasi historis cash tanpa shift ada aturan khusus, tidak dihitung otomatis. Status pending/rejected dan transfer/QRIS tidak boleh masuk kas tunai.

Perilaku yang wajib:
1. Pada Kasir dengan shift OPEN, tampilkan saldo awal, pembayaran tunai terhubung dan lunas pada shift tersebut, saldo seharusnya SAAT INI = saldo awal + kas masuk terhubung, serta jumlah transaksi tunai jika membantu. Gunakan agregat server pada relasi payments dan penyaringan yang sama dengan close, bukan hitungan klien.
2. Bedakan secara eksplisit estimasi saldo saat ini dari angka final setelah penutupan. Selisih kas belum ada sebelum menghitung uang fisik; jangan menampilkan selisih = 0 palsu pada shift open.
3. Pembayaran tunai tanpa shift yang belum direkonsiliasi tidak boleh otomatis dimasukkan; jika ada, tampilkan peringatan relevan dan arahkan ke proses Pemilik. Jangan merekonsiliasi otomatis.
4. Transfer/QRIS, pending/rejected, pembayaran shift lain, cabang lain dan usaha lain tidak dihitung. Kasir hanya melihat shift miliknya; Pemilik tetap melihat data sesuai konteks cabang aktif.
5. Perhitungan final pada CashShiftController::close TETAP sama; hindari duplikasi logika dengan mengekstrak helper query bila bermanfaat, tetapi jangan mengubah urutan lock/transaksi dan aturan audit.
6. Uji contoh saldo awal 100000 + 2 pembayaran tunai linked/paid 25000 dan 15000 => kas masuk 40000, saldo seharusnya saat ini 140000. Tambah transfer 30000 dan tunai pending/shift lain, ringkasan tetap 140000. Tutup dengan 138000 => selisih -2000; tinjau tanpa hitung ulang.

## Batas keamanan
- Jangan mengubah skema/migration tanpa alasan kuat. Jangan menghapus/memodifikasi data utama atau menjalankan migrasi manual.
- Jaga pembatasan usaha/cabang, role Pemilik/Kasir, session cabang aktif, larangan bayar ganda dan shift overlap.
- UI mengikuti komponen Tahap A-C, responsive mobile, bahasa Indonesia, WCAG 2.2 AA, reduced motion.
- Jangan menambah gateway pembayaran atau fitur baru di luar dua lingkup.

## Validasi wajib
- Test unit/feature untuk berat di bawah/atas minimum, transaksi multi-layanan dan quantity item.
- Test feature untuk shift OPEN, mutasi pembayaran, multiple cashier, status, method, cabang dan usaha, saldo final, rekonsiliasi historis.
- npm ci, npm run build, php artisan view:cache, php artisan test, git diff --check.
- Browser screenshot form pesanan dan shift aktif desktop/mobile, data sintetis di laundryku_testing saja.
- Perbarui BLACKBOX_TEST_PLAN.md dengan kasus eksplisit minimum berat dan ringkasan shift. Jangan menandai uji manual selesai. Perbarui PROGRESS.md dan CHANGELOG.md.
- Buat satu branch dan PR khusus penyempurnaan fungsional. Jangan merge otomatis.
