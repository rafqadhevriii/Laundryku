# Audit Kesiapan Penyelesaian TA Laundryku

Tanggal: 11 November 2026
Status: siap untuk pelaksanaan Black Box manual, belum layak dinyatakan semua skenario lulus.

## Kondisi aplikasi
- Proyek di branch main setelah UI/UX Tahap A-C dan perbaikan operasional/cabang aktif.
- Pemeriksaan PHPUnit terakhir: **98 tes lulus, 1.118 assertions**.
- Aplikasi Black Box di http://127.0.0.1:8086/login memberi HTTP 200.
- Database uji laundryku_blackbox terpisah dari laundryku dan laundryku_testing.
- 52 folder bukti manual tersedia pada `storage/blackbox/evidence/manual/BB-01` sampai `BB-52`.
- Form kerja manual tersedia: `storage/blackbox/evidence/manual/HASIL_MANUAL_KERJA.csv`. Semua 52 baris masih Belum diuji manual.

## Pemetaan 52 kasus dan catatan bukti otomatis
Dibuat berkas lokal `storage/blackbox/evidence/manual/AUDIT_CAKUPAN_52.csv` yang mencocokkan 52 ID resmi dengan bukti otomatis.
- 28 ID memiliki catatan bukti otomatis dengan ID sama (belum tentu cakupan penuh).
- 12 ID hanya punya catatan subkasus/parsial.
- 12 ID belum memiliki catatan otomatis yang cocok: BB-08, BB-11, BB-12, BB-17, BB-30, BB-32, BB-33, BB-34, BB-38, BB-39, BB-40, BB-49.
- Catatan otomatis tidak dapat digunakan untuk memberi label Lulus pada pengujian manual.

## Koreksi penting pada penamaan bukti
Beberapa pemeriksaan sebelumnya memakai nama BB-28 untuk penolakan pengaitan lintas cabang. Namun definisi BB-28 resmi adalah **validasi alasan kosong/spasi**. Pemeriksaan lintas cabang termasuk BB-29, sehingga BB-28 tetap memerlukan pengujian alasan.
Beberapa bukti dengan nama BB-36-PARTIAL membahas retry QRIS pending kemudian ditolak, sedangkan definisi resmi BB-36 mengharuskan **retry ditolak ketika pending/paid atau metode tunai**. Jangan menyamakan bukti parsial dengan seluruh syarat.
Bukti BB-35 belum membuktikan semua tampilan historis alasan/referensi dan hak konfirmasi kasir dalam browser.
Kasus BB-11/17 memiliki perilaku yang mungkin sudah tersentuh dalam BB-50/51, tetapi tanpa pengaitan bukti/ID spesifik, jangan otomatis menandainya sebagai Lulus.

## Urutan pekerjaan yang disarankan
1. Lakukan manual tahap dasar: BB-01 sampai BB-07. Simpan screenshot dan hasil nyata, bukan hanya HTTP.
2. Lengkapi BB-08, BB-11, BB-12, BB-17 dengan pesanan/shift sintetis baru, catat ID yang dipakai.
3. BB-28: uji alasan kosong/spasi pada `BB-H-ASSIGN` **sebelum** melakukan pengaitan positif.
4. BB-29/31: dokumentasikan penolakan invalid branch/final shift, termasuk respons server jika diizinkan.
5. BB-30: pada `BB-H-ASSIGN`, hubungkan ke shift A #4 yang masih open dan cocok waktu pembayaran. Verifikasi ada tepat satu tautan serta audit. Jika alat otomatis membatasi, lakukan manual melalui browser dan screenshot.
6. BB-32: pada `BB-H-UNALLOC`, tandai unallocated dengan alasan; verifikasi kas shift tidak berubah. Masih ada historis lain yang dapat memblokir close.
7. BB-34/40/49: gunakan fixture khusus dengan verifikasi waktu dan pengaitan. Jangan ubah timestamp historis agar lolos.
8. BB-36/38/39: uji perulangan retry yang sah, akun nonaktif, dan isolasi bisnis tanpa mengubah data utama.
9. Lengkapi pencetakan/pratinjau nota 58/80mm, eksport laporan, pengecekan mobile dan aksesibilitas.
10. Bila 52 skenario manual sudah diperiksa, hitung Lulus, Gagal, Terhambat berdasarkan CSV kerja dan susun tabel hasil nyata untuk laporan TA.

## Rekomendasi pengujian dan perlindungan data
Gunakan database `laundryku_blackbox` saja untuk tindakan manual. Jangan mengulang initial/advanced fixture atau menjalankan migrasi destruktif. Pengujian otomatis PHPUnit tetap berjalan pada `laundryku_testing` sesuai konfigurasi. Pastikan file screenshot yang dipilih untuk laporan tidak membocorkan password lokal.

## Sumber bukti
- Dokumentasi tujuan: `BLACKBOX_TEST_PLAN.md`
- Urutan skenario: `docs/blackbox/execution-order.md`
- Panduan manual: `docs/blackbox/manual-evidence-workflow.md`
- Panduan rekonsiliasi: `docs/blackbox/manual-reconciliation-checklist.md`
- Indeks otomatis lokal: `storage/blackbox/evidence/automatic-evidence-index.csv`
- Audit cakupan 52 kasus lokal: `storage/blackbox/evidence/manual/AUDIT_CAKUPAN_52.csv`
- Hasil manual lokal: `storage/blackbox/evidence/manual/HASIL_MANUAL_KERJA.csv`

**Tidak ada klaim 52 pengujian manual telah selesai dalam dokumen ini.**
