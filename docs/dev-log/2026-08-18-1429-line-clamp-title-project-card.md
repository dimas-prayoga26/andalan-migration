# Line Clamp Title Project Card

Tanggal: 2026-08-18 14:29 WIB

## Ringkasan

- Membatasi title project card pada halaman Project Management > Projects menjadi satu baris.
- Title panjang sekarang dipotong dengan ellipsis agar tinggi card tetap rapi.
- Menambahkan `min-width: 0` pada wrapper title agar `text-overflow: ellipsis` bekerja di dalam layout flex.

## File yang Berubah

- `resources/views/project_management/projects/index.blade.php`

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=project`

Hasil terakhir: `2 passed, 5 skipped (639 assertions)`.
