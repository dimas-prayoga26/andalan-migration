# Deviation Request Type Label

## Ringkasan
- Mengubah label `Request Type` pada modal `deviation` agar `late_arrival` dan `early_departure` sama-sama tampil sebagai `Permitted Late Arrival | Early Departure`.
- Mempertahankan title modal `Permitted Late Arrival / Early Departure`.
- Memperbarui test routing modal attendance untuk mengunci label request type baru.

## File Terkait
- `app/Http/Controllers/AttendanceController.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`

## Verifikasi
- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `git diff --check`
