# Report Table Note Gantikan Variance

## Ringkasan
- Menghapus kolom `Note` terpisah pada table Attendance Report.
- Mengubah kolom `Variance` menjadi `Note`.
- Data kolom `Note` tetap memakai field `note` dari response DataTable.
- Urutan kolom table sekarang:
  - Date
  - Clock In
  - Clock Out
  - Note
  - Work Hours
  - Attachment

## File Terkait
- `resources/views/attendance/reports/index.blade.php`
- `tests/Feature/AttendanceReportExcelExportTest.php`

## Verifikasi
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceReportExcelExportTest.php tests\Feature\AttendanceNamingConventionTest.php`
