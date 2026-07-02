# Simplify Deviation Modal Fields

## Ringkasan
- Menyederhanakan modal Attendance Exception agar hanya menampilkan `Request Type`, `Reason`, `Time Variance`, dan `Status`.
- Menghapus field date, location, clock in, dan clock out dari tampilan modal `deviation`.
- Memperbarui test dan runbook agar sesuai perilaku akhir.

## Verifikasi
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php`
