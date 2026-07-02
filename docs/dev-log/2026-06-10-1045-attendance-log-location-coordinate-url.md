# Attendance Log Location Coordinate URL

## Ringkasan
- Mengubah proses penyimpanan `attendance_logs.location` agar memakai URL Google Maps dari latitude dan longitude absensi.
- Reverse geocode tetap dipakai untuk mengisi kolom address component, tapi bukan lagi sumber utama kolom `location`.
- Menambahkan test untuk memastikan `location` tersimpan dari koordinat, bukan dari `formatted_address`.

## Verifikasi
- `php -l app\Services\Attendance\AttendanceMutationService.php`
- `php -l tests\Feature\AttendanceLogLocationByCoordinatesTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests\Feature\AttendanceLogLocationByCoordinatesTest.php tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceNamingConventionTest.php`
