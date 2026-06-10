# Attendance Report Export Plain Style

## Perubahan
- Menghapus background warna dari title dan header file export Attendance Report `.xlsx`.
- Style export tetap memakai teks bold untuk title/header, tetapi fill cell kembali ke default polos.

## Verifikasi
- `php -l app/Http/Controllers/ReportController.php`
- `php -l tests/Feature/AttendanceReportExcelExportTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests/Feature/AttendanceReportExcelExportTest.php`
