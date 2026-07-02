# Move Summary Cards Below List Pengajuan Title (Full Width)

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Header `List Pengajuan` dijadikan baris judul terpisah.
  - Summary cards dipindahkan ke bawah judul dalam wrapper `col-12` (full width).
  - Struktur dipisah agar title dan cards tidak saling dorong/bertabrakan.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
