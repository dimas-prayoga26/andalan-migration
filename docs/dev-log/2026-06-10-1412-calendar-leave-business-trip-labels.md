# Calendar Leave and Business Trip Labels

## Ringkasan
- Menambahkan event label calendar dari database untuk Leave Request dan Business Trip milik staff login.
- Menambahkan warna calendar:
  - `Special Leave`: `#d63384` / `#a82767`
  - `Sick Leave`: `#0d6efd` / `#0a58ca`
  - `Unpaid Leave`: `#6c757d` / `#5a6268`
  - `Annual Leave`: `#6f42c1` / `#59339d`
  - `Business Trip`: `#0dcaf0` / `#0aa2c0`
- Menambahkan builder JS agar FullCalendar bisa merender label satu hari maupun range beberapa hari.

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
