# Clock Out Fallback Strip

## Ringkasan
- Mengubah fallback clock out pada event calendar Attendance dari `Belum Clock Out` menjadi `-`.
- Menambahkan assertion test agar teks fallback lama tidak kembali.

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php`
