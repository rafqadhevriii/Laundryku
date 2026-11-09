# Spesifikasi konteks cabang aktif Laundryku

Tanggal: 2026-11-09. Status: disetujui untuk diterapkan pada PR terpisah.
Keputusan: Pemilik memilih cabang sekali pada header ruang kerja. Kasir selalu memakai cabang yang ditugaskan. Form pesanan tidak lagi meminta pilihan cabang.

## Perilaku
- Pemilik memiliki pengalih cabang pada area header/topbar: "Semua Cabang" untuk melihat ringkasan gabungan, atau satu cabang aktif untuk bekerja. Pilihan disimpan per sesi login, bukan sebagai cabang permanen di akun pemilik.
- Kasir tidak melihat pengalih cabang; identitas cabang akunnya terlihat dan tidak bisa diubah.
- Semua Cabang hanya untuk tampilan agregat/daftar/laporan yang relevan, bukan untuk menyimpan pesanan. Saat Pemilik membuka form Pesanan Baru pada mode Semua Cabang, minta memilih satu cabang melalui pengalih header sebelum form dapat digunakan. Jangan menampilkan dropdown cabang kedua di dalam form pesanan.
- Untuk pembuatan pesanan, backend selalu menentukan branch_id dari konteks cabang aktif yang valid di server untuk Pemilik, dan user.branch_id untuk Kasir. Jangan percaya nilai branch_id yang disisipkan dari request/form untuk mengalihkan cabang.
- Pada form pesanan tampilkan cabang aktif sebagai informasi read-only. Batasi daftar pelanggan ke cabang aktif. Jika cabang berubah, jangan gunakan pelanggan pilihan sebelumnya yang berasal dari cabang lain. Selalu cek customer.branch_id di server.
- Pilihan cabang Pemilik berlaku pada dashboard, daftar pesanan, daftar pelanggan, stok, serta daftar/monitor operasional yang terkait. Laporan pendapatan/operasional dapat menampilkan gabungan dalam mode Semua Cabang; jangan hilangkan kemampuan agregat yang sudah ada.
- Tindakan update untuk pesanan/pelanggan/stok yang spesifik mengikuti scope usaha dan izin yang sudah ada. Pada konteks cabang tertentu, utamakan konsistensi ruang kerja, tetapi jangan diam-diam mengubah identitas cabang historis sebuah transaksi.
- Form pembuatan pelanggan (dan stok/pengeluaran jika termasuk dalam implementasi) sebaiknya mengikuti cabang aktif agar navigasi konsisten. Jika terlalu luas, catat sebagai follow-up yang jelas, jangan memperlemah aturan otorisasi atau membuat form pesanan kembali meminta cabang.

## Keamanan, sesi, dan kondisi tepi
- Validasi setiap perubahan konteks: authenticated Pemilik, cabang milik business_id pengguna, aktif. Gunakan POST + CSRF dan whitelist nilai 'all' atau ID cabang yang sah.
- Jangan izinkan Kasir memodifikasi cabang melalui URL, POST, session, atau form. Gunakan branch_id dari akun Kasir.
- Revalidasi pilihan cabang tersimpan pada setiap request penting. Jika cabang dinonaktifkan/dihapus atau tidak lagi milik usaha, hapus konteks tersebut dan kembali ke Semua Cabang; untuk POST mutasi, gagal aman dengan error jelas, jangan mengalihkan transaksi ke cabang lain.
- Jangan gunakan session global yang dapat bercampur antarakun. Reset/namespace konteks saat login/logout, sehingga user berikutnya tidak mewarisi cabang sebelumnya.
- Saat pilihan cabang berubah, jangan mengirim ulang POST lama. Arahkan kembali secara aman ke GET yang relevan. Tampilkan cabang aktif secara jelas di header, termasuk di mobile.
- Dalam mode Semua Cabang, tampilkan konteks gabungan secara jelas agar Pemilik tidak salah menafsirkan angka dashboard.
- Mode cabang terpilih harus menyaring query sebelum pagination dan agregasi, tidak hanya menyembunyikan baris dengan CSS atau JavaScript.
- Jangan mengubah database, data nyata, migration, angka pembayaran atau otorisasi finansial untuk fitur ini.

## Test wajib
1. Pemilik memilih Cabang A, berpindah halaman, filter dashboard/pesanan/pelanggan tetap Cabang A.
2. Pemilik memilih Semua Cabang, ringkasan gabungan benar, form tambah pesanan meminta memilih cabang aktif melalui header.
3. Setelah memilih Cabang B, form pesanan tidak memiliki dropdown cabang, pelanggan yang tersedia hanya Cabang B, POST tersimpan dengan branch_id B.
4. Kasir A tidak melihat pengalih; semua pesanan baru masuk Cabang A. Upaya mengirim branch_id B ditolak/diabaikan aman tanpa mengubah cabang akun.
5. Pemilik mencoba cabang usaha lain atau nonaktif => ditolak; stale session => gagal aman pada POST.
6. Dua akun yang masuk bergantian tidak berbagi cabang aktif sesi yang lama.
7. Dashboard agregat dan laporan lama tidak rusak; histori transaksi tidak berubah.
8. npm run build, php artisan test, php artisan view:cache, git diff --check lulus; pengujian browser desktop dan mobile serta aksesibilitas header dilakukan pada database laundryku_testing.
9. Screenshot konteks Semua Cabang, Cabang A, form pesanan, kasir dan mobile, simpan laporan di docs/branch-context.

## Instruksi pengerjaan
Baca AGENTS.md, UIUX_REDESIGN_SPEC.md, UIUX_STAGE_B_SPEC.md, PROGRESS.md. Gunakan service/helper terpusat untuk resolve konteks cabang sehingga controller tidak menduplikasi logika. Terapkan perubahan minimum yang konsisten, tambah tes multi-cabang dan update PROGRESS.md/CHANGELOG.md. Buat branch dan PR terpisah, jangan merge otomatis.
