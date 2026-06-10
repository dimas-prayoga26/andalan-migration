# Overtime Detail Card Link

## Perubahan
- Menambahkan route `attendance.overtimes.detail` untuk membuka view `attendance.overtimes.detail`.
- Mengubah link card overtime `#OVT-2605-0101` dari file HTML statis lama ke route detail Blade.
- Membersihkan trailing whitespace di area card overtime yang disentuh.

## Verifikasi
- `php artisan route:list --name=attendance.overtimes.detail`
- `php -l tests/Feature/AttendanceNamingConventionTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests/Feature/AttendanceNamingConventionTest.php`
