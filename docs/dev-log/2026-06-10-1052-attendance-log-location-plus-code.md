# Attendance Log Location Plus Code

## Ringkasan
- Mengubah `attendance_logs.location` agar menyimpan `plus_code.compound_code` dari Google Geocoding API.
- Menambahkan fallback ke URL Google Maps lat/long jika plus code tidak tersedia.
- Mengubah reverse geocode ke bahasa English agar compound code mengikuti format seperti `Sleman Regency, Special Region of Yogyakarta`.
- Menyesuaikan modal calendar agar plus code tampil sebagai teks lokasi, sementara URL maps tetap dipakai sebagai link titik.

## Verifikasi
- `php -l app\Services\Attendance\AttendanceMutationService.php`
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceLogLocationByCoordinatesTest.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceLogLocationByCoordinatesTest.php tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceNamingConventionTest.php`
