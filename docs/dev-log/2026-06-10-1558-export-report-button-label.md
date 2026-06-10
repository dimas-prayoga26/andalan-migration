# Export Report Button Label

## Perubahan
- Mengubah tombol export di Attendance Report dari `Export Excel` menjadi `Export Report`.
- Menambahkan icon FontAwesome Excel: `<i class="fa-solid fa-file-excel me-1"></i>`.

## Verifikasi
- `php artisan test --compact tests/Feature/AttendanceReportExcelExportTest.php`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
