# Attendance Report Export Remove Attachment

## Perubahan
- Menghapus kolom `Attachment` dari file export Attendance Report `.xlsx`.
- Header export Excel sekarang hanya berisi Date, Clock In, Clock Out, Note, dan Working Hours.
- Merge title export disesuaikan dari `A1:F1` menjadi `A1:E1`.
- Attachment pada table web tetap dipertahankan.

## Verifikasi
- `php -l app/Http/Controllers/ReportController.php`
- `php -l tests/Feature/AttendanceReportExcelExportTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests/Feature/AttendanceReportExcelExportTest.php`
