# Dynamic Attendance Calendar Modals

## Ringkasan
- Mengubah modal calendar attendance `onTime`, `late`, dan `deviation` agar field detail diisi dari data event yang diklik.
- Menambahkan payload event untuk status, lokasi, jam clock in/out, request type, reason, time variance, dan status attendance exception.
- Menambahkan test untuk memastikan controller mengirim payload dinamis dan Blade mengikat field modal melalui JavaScript.

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceNamingConventionTest.php`
