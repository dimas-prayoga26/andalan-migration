# Report Note Attendance Exception Label

## Ringkasan
- Menyesuaikan logic kolom `Note` pada Attendance Report.
- Attendance normal tetap menampilkan:
  - `Late X Minutes` jika `late_minutes` lebih dari 0,
  - `On Time` jika clock in tidak terlambat.
- Attendance exception sekarang menampilkan label berdasarkan `type`:
  - `late_arrival` menjadi `Izin Masuk Terlambat`.
  - `early_departure` menjadi `Izin Pulang Lebih Awal`.
- Jika `from_time` dan `to_time` tersedia, label exception ditambah durasi, contoh:
  - `Izin Masuk Terlambat 3 Jam`
  - `Izin Pulang Lebih Awal 2 Jam 30 Menit`

## File Terkait
- `app/Http/Controllers/ReportController.php`
- `tests/Feature/AttendanceReportExcelExportTest.php`

## Verifikasi
- `php -l app\Http\Controllers\ReportController.php`
- `php -l tests\Feature\AttendanceReportExcelExportTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceReportExcelExportTest.php tests\Feature\AttendanceNamingConventionTest.php`
