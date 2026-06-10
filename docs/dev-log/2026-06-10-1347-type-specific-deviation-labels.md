# Type Specific Deviation Labels

## Ringkasan
- Mengubah title modal `deviation` agar mengikuti `attendance_exceptions.type`.
- `late_arrival` tampil sebagai `Permitted Late Arrival`.
- `early_departure` tampil sebagai `Early Departure`.
- Mengubah `Request Type` dan intro modal agar tidak lagi menampilkan gabungan dua tipe sekaligus.

## File Terkait
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/attendance/attendance/index.blade.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`

## Verifikasi
- `vendor/bin/pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `git diff --check`
