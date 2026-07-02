# Calendar Modal Location Address Fields

## Ringkasan
- Mengubah sumber lokasi modal calendar Attendance agar hanya memakai kolom `address_village`, `address_district`, `address_regency`, `address_city`, dan `address_province`.
- Menghapus pemakaian kolom `location` dan `address_postal_code` dari payload lokasi calendar modal.
- Menambahkan assertion test agar pemakaian kolom lokasi legacy tidak kembali.

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceNamingConventionTest.php`
