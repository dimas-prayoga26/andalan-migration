# Deviation Modal Title

## Ringkasan
- Mengubah default header modal `deviation` menjadi `Permitted Late Arrival / Early Departure`.
- Mengubah fallback JavaScript untuk `deviationLabel` agar sama dengan title modal yang dikirim controller.
- Memperbarui test routing modal attendance untuk mengunci title baru.

## File Terkait
- `resources/views/attendance/attendance/index.blade.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`

## Verifikasi
- `vendor/bin/pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `git diff --check`
