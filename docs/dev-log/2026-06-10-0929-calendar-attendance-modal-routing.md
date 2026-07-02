# Calendar Attendance Modal Routing

## Ringkasan
- Menyesuaikan event calendar Attendance agar label/card membuka modal sesuai status:
  - `On Time` membuka modal `#onTime`.
  - `Late X Minutes` membuka modal `#late`.
  - `Attendance Exception | Late Arrival` dan `Attendance Exception | Early Departure` membuka modal `#deviation`.
- Attendance exception diprioritaskan di atas late/on-time, sehingga label langsung berubah ke deviation saat exception dibuat walau staff belum clock out.
- Menambahkan `extendedProps.attendanceModalId` pada event history attendance.
- Menjaga `extendedProps` tetap terbawa saat event FullCalendar dirender di Blade.
- Menambahkan `id` pada select attendance history agar eager load `attendanceException` bisa dipasangkan dengan benar.

## File Terkait
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/attendance/attendance/index.blade.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`

## Verifikasi
- `php -l app\Http\Controllers\AttendanceController.php`
- `php -l tests\Feature\AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `php artisan test --compact tests\Feature\AttendanceCalendarModalRoutingTest.php tests\Feature\AttendanceNamingConventionTest.php`
