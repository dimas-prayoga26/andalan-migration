# Project Modal Date Range

Tanggal: 2026-08-18 14:48 WIB

## Ringkasan

- Menggabungkan field `Live Event Start` dan `Live Event End` pada modal Add/Update Project menjadi satu input `Live Event Date`.
- Menggabungkan field `Start Date` dan `End Date` menjadi satu input `Date` dengan date range picker seperti modal Task List.
- Menjaga nama field backend tetap memakai `live_event_start_date`, `live_event_end_date`, `start_date`, dan `end_date` lewat hidden input agar controller dan data lama tetap kompatibel.
- Menambahkan validasi frontend agar project date range wajib dipilih sebelum submit AJAX.

## File yang Berubah

- `resources/views/project_management/projects/index.blade.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=project`
- `vendor/bin/pint --dirty --format agent`

Hasil terakhir: `2 passed, 5 skipped (654 assertions)`.
