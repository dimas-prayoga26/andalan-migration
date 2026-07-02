# Export Attendance Report XLSX dan Title Dinamis

## Ringkasan
- Mengubah export Attendance Report dari HTML Excel-compatible `.xls` menjadi file OpenXML `.xlsx`.
- Export `.xlsx` dibuat langsung dari controller memakai `ZipArchive`, tanpa menambah dependency baru.
- Menghapus view HTML export `attendance.reports.excel` karena export sekarang binary `.xlsx`.
- Title baris pertama tidak lagi statis `SIAP - HRIS`.
- Title export diambil dari data report dengan format:
  - `company_name - staff_name`
  - fallback multi data: `Multiple Companies - All Staff`
  - fallback kosong: `Company - Staff`
- Nama file export ikut memakai slug dari title dinamis dan periode.

## File Terkait
- `app/Http/Controllers/ReportController.php`
- `resources/views/attendance/reports/excel.blade.php`
- `tests/Feature/AttendanceNamingConventionTest.php`
- `tests/Feature/AttendanceReportExcelExportTest.php`

## Verifikasi
- `php -l app\Http\Controllers\ReportController.php`
- `php -l tests\Feature\AttendanceReportExcelExportTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceReportExcelExportTest.php tests\Feature\AttendanceNamingConventionTest.php`

## Catatan
- PHP lokal masih menampilkan warning `Module "mysqli" is already loaded`, tetapi lint, formatter, Blade compile, dan test terfokus tetap berhasil.
