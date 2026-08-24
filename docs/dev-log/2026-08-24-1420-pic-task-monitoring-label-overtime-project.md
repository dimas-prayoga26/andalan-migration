# PIC Task Monitoring Label Overtime Project

Tanggal: 2026-08-24 14:20 WIB

## Ringkasan

- Mengubah label kecil pada kolom Task di PIC Attendance > Task Monitoring agar mengikuti sumber task.
- Task yang terhubung ke overtime sekarang ikut tampil dan title task diberi badge `Overtime` di sampingnya.
- Task yang berasal dari project tampil sebagai `Task (Nama Project)`.
- Daily task tetap tampil sebagai `Daily Task`.
- Modal detail task memakai label konteks yang sama agar konsisten dengan tabel.

## File yang Berubah

- `app/Http/Controllers/PicAttendance/PicAttendanceTaskController.php`
- `resources/views/pic_attendance/task/index.blade.php`
- `tests/Feature/PicAttendanceModuleTest.php`

## Test dan Verifikasi

- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/PicAttendanceModuleTest.php`

Hasil terakhir: `4 passed (198 assertions)`.
