# Calendar Label Remove Location Link

## Ringkasan
- Mengembalikan label/card event calendar Attendance ke format `In : HH:mm - Out : HH:mm`.
- Menghapus button/link `Lihat titik lokasi` dari modal `onTime` dan `late`.
- Menghapus payload dan JavaScript `locationMapUrl` yang sudah tidak dipakai di modal.
- Memperbarui runbook Attendance agar sesuai perilaku akhir.

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceLogLocationByCoordinatesTest.php tests\Feature\AttendanceNamingConventionTest.php`
