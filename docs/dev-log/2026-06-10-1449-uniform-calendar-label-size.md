# Uniform Calendar Label Size

## Ringkasan
- Menambahkan `fc-calendar-label-card` ke CSS ukuran label calendar yang sudah dipakai weekend, holiday, dan attendance.
- Menyamakan tinggi minimum, padding, radius, font, dan ellipsis text untuk semua label calendar.
- Memperbarui test calendar modal untuk memastikan label Leave/Business Trip memakai ukuran visual yang sama.

## File Terkait
- `resources/views/attendance/attendance/index.blade.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`

## Verifikasi
- `php -l tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `vendor/bin/pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `git diff --check`
