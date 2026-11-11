# Verifikasi keamanan tambahan, 11 November 2026

Lingkungan: laundryku_blackbox (127.0.0.1:8086), hanya akun dan transaksi sintetis.

## Hasil pemeriksaan HTTP
- BB-37 (cakupan akses baca): Pemilik usaha utama mendapat HTTP 404 pada order #20 milik usaha asing. Pemilik usaha asing dapat membaca order #20 miliknya. Kasir Cabang B mendapat HTTP 404 pada order #16 milik Cabang A. LULUS dalam lingkup ini.
- BB-29 (cakupan pilihan shift UI): dropdown rekonsiliasi pada usaha utama tidak menampilkan shift #7 yang merupakan usaha asing. LULUS dalam lingkup ini.
- BB-31 (cakupan pilihan shift UI): dropdown `cash_shift_id` tidak menampilkan shift #1 dan #6 yang berstatus reviewed. LULUS dalam lingkup ini.
- Pemeriksaan BB-31 sempat gagal palsu karena selector umum mendeteksi ID cabang 1. Pemeriksaan ulang dibatasi pada elemen select khusus `cash_shift_id`, hasil sesuai. Tidak ditemukan bug aplikasi dari indikasi itu.

Bukti lokal: `storage/blackbox/evidence/automated-http-reconciliation-negative-ui.json` (tidak dikomit).

## Status pengujian yang masih diperlukan
- BB-29 dan BB-31: penolakan POST tidak sah secara server-side tidak diuji kali ini; UI bukan satu-satunya kontrol keamanan.
- BB-37: belum ada percobaan mutasi terhadap pembayaran usaha asing.
- BB-30/32/34/40/49: pengaitan positif, unallocated, overlapping shift dan rekonsiliasi lanjut belum dijalankan.
- BB-36/38: pengajuan ulang QRIS penuh dan siklus penolakan berulang belum selesai.
- BB-39: sesi akun atau cabang nonaktif perlu uji khusus dengan pemulihan status.
- Seluruh 52 skenario manual masih Belum diuji manual karena belum tersedia screenshot pengujian manusia.

Catatan: Database uji advanced sudah memiliki data hasil berbagai pemeriksaan otomatis. Jangan reset, ubah timestamp historis, atau menyebut baseline fixture awal masih sama. Lihat `docs/blackbox/automated-local-progress.md` dan `docs/blackbox/manual-reconciliation-checklist.md`.
