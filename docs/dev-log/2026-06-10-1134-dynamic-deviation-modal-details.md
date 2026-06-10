# Dynamic Deviation Modal Details

## Ringkasan
- Menghapus copy statis pada modal `deviation`.
- Menambahkan field dinamis untuk date, location, clock in, dan clock out pada modal Attendance Exception.
- Menambahkan payload `attendanceDateLabel` dari controller untuk tampilan tanggal modal.
- Memperbarui test agar modal `deviation` tidak kembali memakai konten statis.

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php`
