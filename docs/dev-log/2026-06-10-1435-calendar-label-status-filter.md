# Calendar Label Status Filter

## Ringkasan
- Mengubah filter label Leave calendar agar menampilkan `leave_requests` status `pending` dan `approved`.
- Mengubah filter label Business Trip calendar agar hanya menampilkan `business_trips.approval_status = approved`.
- Memperbarui test dan runbook untuk mengunci aturan status calendar terbaru.

## File Terkait
- `app/Http/Controllers/AttendanceController.php`
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
