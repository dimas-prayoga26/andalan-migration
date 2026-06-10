# Dynamic Calendar Label Modals

## Ringkasan
- Menyambungkan label calendar Leave dan Business Trip ke modal:
  - `annualLeave`
  - `specialLeave`
  - `unpaidLeave`
  - `sick`
  - `trip`
- Menambahkan `calendarModalId` dan detail modal ke `extendedProps`.
- Mengubah isi modal agar field Leave/Trip terisi dinamis dari event yang diklik.
- Menampilkan Medical Notes Sick Leave dari `leave_requests.attachment_path` jika tersedia.

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
