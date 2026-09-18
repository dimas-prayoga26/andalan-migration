# Konfigurasi Drive Project Detail

Tanggal: 2026-08-24 14:50 WIB

## Ringkasan

- Mengubah tombol konfigurasi Google Drive pada detail Project Management menjadi `Konfigurasi Drive`.
- Tombol `Drive` untuk division yang sudah memiliki URL membuka folder langsung untuk staff non-admin, sedangkan event project admin membuka modal konfigurasi yang sudah terisi.
- Klik `Konfigurasi Drive` memanggil Google OAuth terlebih dahulu.
- Modal konfigurasi menyediakan pilihan folder induk melalui Google Picker.
- Saat Google Picker aktif, modal konfigurasi disembunyikan sementara lalu ditampilkan kembali setelah folder dipilih atau Picker ditutup.
- Modal konfigurasi menyediakan field nama folder project dan folder divisi agar user bisa menyesuaikan struktur sebelum folder dibuat.
- Setelah folder induk dipilih, tombol `Buat Struktur Folder` membuat struktur `Nama Project / Nama Division` memakai Drive API `parents`, menyimpan `webViewLink` folder division, lalu memuat ulang halaman.
- Input URL manual tetap tersedia sebagai fallback atau override.
- Tombol manual diubah menjadi `Simpan URL`, dan proses pembuatan struktur kini menampilkan status `Menyimpan link...` saat URL final sedang dikirim ke backend.
- Division yang sudah punya URL Drive kini tetap menampilkan link `Drive` walaupun user juga memiliki akses `Konfigurasi Drive`.
- Setelah URL Drive berhasil disimpan, halaman tidak lagi reload; link `Drive` dan atribut tombol konfigurasi diupdate langsung di DOM agar token OAuth aktif tidak hilang.
- Untuk event project admin, division yang sudah punya URL hanya menampilkan tombol `Drive`; klik tombol ini membuka modal konfigurasi yang sudah terisi agar URL tetap bisa diedit.
- Field `Google Drive URL` di modal mendapat tombol `Open` untuk membuka link folder pada tab baru.
- Label `Google Drive URL` di modal mendapat badge `Folder siap` berwarna hijau atau `Belum siap` berwarna merah berdasarkan ada tidaknya URL.
- Modal konfigurasi memakai title `Konfigurasi Drive {Nama Division}`.
- Menambahkan konfigurasi `services.google.api_key`, `services.google.client_id`, dan `services.google.client_secret` dari env `GOOGLE_API_KEY`, `GOOGLE_CLIENT_ID`, dan `GOOGLE_CLIENT_SECRET` untuk integrasi Google API/OAuth.

## File yang Berubah

- `config/services.php`
- `resources/views/project_management/projects/detail.blade.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`
- `docs/catatan-dev-log.md`

## Test dan Verifikasi

- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=monthly_overview_cards_use_equal_height_layout`

Hasil terakhir: `1 passed (745 assertions)`.
