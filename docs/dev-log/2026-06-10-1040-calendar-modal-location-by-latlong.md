# Calendar Modal Location By Latlong

## Ringkasan
- Mengubah bukti lokasi modal calendar Attendance agar memakai link Google Maps dari `attendance_logs.latitude` dan `attendance_logs.longitude`.
- Menampilkan label lokasi sebagai `Koordinat Absensi` saat koordinat tersedia, supaya tidak bergantung pada `formatted_address` reverse geocode yang bisa meleset.
- Menambahkan test untuk memastikan payload `locationMapUrl` dan link modal on-time/late tersedia.

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceNamingConventionTest.php`
