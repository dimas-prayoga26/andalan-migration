# Dynamic Deviation Modal Intro

## Ringkasan
- Mengembalikan header dan copy pembuka modal `deviation` sesuai desain Attendance Exception.
- Copy pembuka tidak ditulis statis di Blade, tetapi dikirim dari `AttendanceController` melalui `extendedProps`.
- Modal `deviation` tetap hanya menampilkan field utama `Request Type`, `Reason`, `Time Variance`, dan `Status`.

## File Terkait
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/attendance/attendance/index.blade.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `docs/runbook/attendance-report-calendar-location.md`

## Verifikasi
- `php -l app/Http/Controllers/AttendanceController.php`
- `php -l tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `vendor/bin/pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `git diff --check`
