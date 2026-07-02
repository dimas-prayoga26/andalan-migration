# Report Attendance Note, Attachment, dan Export Excel

## Ringkasan
- Menambahkan field `note` pada response DataTable Attendance Report.
- Mengubah kolom tabel report dari `Notes` menjadi `Note` dan `Attachment`.
- Nilai `Note` sekarang diambil dari:
  - holiday/cuti bersama/weekend virtual row,
  - tipe cuti dari `leave_requests`,
  - `attendance_exceptions.note`,
  - `attendances.late_minutes`,
  - fallback `On Time`.
- Attachment report memakai `leave_requests.attachment_path` dan ditampilkan sebagai link `View Attachment`.
- Mengubah export report dari PDF menjadi Excel tanpa menambah dependency baru.

## File Terkait
- `app/Http/Controllers/ReportController.php`
- `resources/views/attendance/reports/index.blade.php`
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
- `php artisan test --compact tests\Feature\AttendanceReportExcelExportTest.php tests\Feature\AttendanceNamingConventionTest.php tests\Feature\ReportHolidayDatabaseTest.php` belum bisa penuh karena environment lokal tidak memiliki PDO database driver (`could not find driver`) pada test holiday existing.
- PHP lokal masih menampilkan warning `Module "mysqli" is already loaded`, tetapi lint, formatter, Blade compile, dan test terfokus tetap berhasil.
