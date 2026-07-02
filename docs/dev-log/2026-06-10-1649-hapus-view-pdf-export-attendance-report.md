# Hapus View PDF Export Attendance Report

## Ringkasan
- Menghapus view lama `attendance.reports.pdf` karena export Attendance Report sudah dipindahkan ke Excel.
- Menambahkan proteksi test agar view PDF report tidak muncul lagi.
- Logic export tetap memakai endpoint `attendance.reports.export`.

## File Terkait
- `resources/views/attendance/reports/pdf.blade.php`
- `tests/Feature/AttendanceNamingConventionTest.php`
- `tests/Feature/AttendanceReportExcelExportTest.php`

## Verifikasi
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceReportExcelExportTest.php tests\Feature\AttendanceNamingConventionTest.php`

## Catatan
- Dependency PDF di `composer.json` tidak dihapus karena perubahan dependency membutuhkan approval dan bisa saja masih dipakai modul lain.
