# Director Task Monitoring Sama PIC

Tanggal: 2026-08-24 15:15 WIB

## Ringkasan

- Menyamakan tampilan data Task Monitoring pada Director Attendance > Task dengan PIC Attendance > Task.
- Task yang memiliki `overtime_id` sekarang ikut tampil di Director Task Monitoring.
- Title task overtime diberi badge `Overtime`, dan `record_number` overtime tampil di bawah title.
- Task project tampil sebagai `Task (Nama Project)`, sedangkan daily task tetap `Daily Task`.
- Modal detail Director memakai label konteks task yang sama dengan tabel.

## File yang Berubah

- `app/Http/Controllers/DirectorAttendance/DirectorAttendanceTaskController.php`
- `resources/views/director_attendance/task/index.blade.php`
- `tests/Feature/DirectorAttendanceModuleTest.php`

## Test dan Verifikasi

- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/DirectorAttendanceModuleTest.php`

Hasil terakhir: `3 passed (209 assertions)`.
