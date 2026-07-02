# Calendar Modal Location Sanitized

## Ringkasan
- Mengembalikan sumber lokasi modal calendar Attendance ke kolom `location`.
- Membersihkan kode pos 5 digit dan kata `Indonesia` dari nilai lokasi sebelum dikirim ke modal.
- Menyesuaikan test agar memastikan lokasi memakai kolom `location` tanpa `address_postal_code`.

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceNamingConventionTest.php`
