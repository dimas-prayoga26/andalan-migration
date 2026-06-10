# Cleanup Report Controller Variance dan Notes

## Ringkasan
- Menghapus sisa script/kode `variance` di `ReportController` karena kolom `Variance` sudah diganti menjadi `Note`.
- Menghapus helper `formatVariance()` yang sudah tidak dipakai.
- Menghapus pengambilan kolom `from_time` dan `to_time` dari query `attendance_exceptions` di report.
- Menghapus field response legacy `variance` dan `notes` dari payload DataTable report.
- Menambahkan proteksi test agar field legacy tersebut tidak muncul lagi di controller.

## File Terkait
- `app/Http/Controllers/ReportController.php`
- `tests/Feature/AttendanceReportExcelExportTest.php`

## Verifikasi
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceReportExcelExportTest.php tests\Feature\AttendanceNamingConventionTest.php`
