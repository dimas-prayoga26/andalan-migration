# Stabilize Izin Header Layout by Removing Carousel

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Mengganti summary card dari `owl-carousel` ke grid Bootstrap statis.
  - Menata ulang kolom header section logs menjadi:
    - `col-lg-2` : Logs
    - `col-lg-8` : 4 summary card (`col-6 col-lg-3`)
    - `col-lg-2` : List Pengajuan
  - Menonaktifkan inisialisasi JS `owlCarousel` untuk summary card.

## Alasan
- Layout sebelumnya sering bertabrakan karena kombinasi carousel + grid/flex.
- Grid statis lebih stabil dan konsisten di desktop/mobile.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
