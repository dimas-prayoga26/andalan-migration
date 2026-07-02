# Hide Show Entries and Search on Izin Table

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Pada inisialisasi DataTable `#tableLogs` ditambahkan:
    - `searching: false`
    - `lengthChange: false`

## Hasil
- Kontrol `Show 10 entries` dan `Search` disembunyikan dari halaman izin.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
