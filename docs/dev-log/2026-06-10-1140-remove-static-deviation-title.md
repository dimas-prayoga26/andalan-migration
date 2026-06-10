# Remove Static Deviation Title

## Ringkasan
- Menghapus default title statis `Permitted Late Arrival / Early Departure` dari modal `deviation`.
- Default title modal diganti menjadi `Attendance Exception`, lalu tetap diisi dinamis dari event yang diklik.
- Menambahkan assertion test agar teks statis lama tidak kembali.

## Verifikasi
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php`
