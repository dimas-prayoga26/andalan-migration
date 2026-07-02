# Perbaikan Flow Overtime Task dan Clock In

Tanggal: 2026-07-01 01:26 WIB

## Ringkasan

- Mengubah flow `+ Add Overtime` dari menu PIC agar hanya membuat data overtime dan lifecycle log, tanpa otomatis membuat row `project_tasks`.
- Task lembur sekarang wajib dibuat sendiri oleh staff yang di-assign pada halaman detail overtime.
- Tombol `+ Add Task` pada detail overtime staff hanya aktif setelah staff melakukan `Overtime Clock In` dan sebelum `Overtime Clock Out`.
- Backend `storeTask` ikut dibatasi agar create task ditolak jika overtime belum clock-in atau sudah clock-out.
- Window `Overtime Clock In` disamakan dengan jadwal dari PIC: mulai dari `planned_start_time` sampai `planned_end_time`, tidak lagi bisa clock-in lebih awal dari jam mulai.
- Menambahkan dukungan jadwal lembur lintas hari ketika `planned_end_time` lebih kecil atau sama dengan `planned_start_time`.
- Mengganti pesan "task sudah disubmit" menjadi "task sudah dikerjakan".
- Menyeragamkan urutan lifecycle overtime dari PIC dengan flow staff/seeder agar tidak bentrok unique key `overtime_lifecycle_logs_overtime_step_unique`.
- Menetapkan `task_deliverables_submitted` sebagai step 3 dan `session_ended` sebagai step 4.
- Menahan `task_hours_verification` tetap `waiting` selama sesi overtime masih berjalan.
- Saat staff melakukan `Overtime Clock Out`, `task_hours_verification` baru berubah menjadi `pending` tanpa actor otomatis, sehingga review/approval benar-benar dimulai setelah clock-out.

## File Perubahan

- `app/Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php`
- `app/Http/Controllers/StaffAttendance/AttendanceOvertimeController.php`
- `resources/views/staff_attendance/overtimes/detail.blade.php`
- `tests/Feature/PicAttendanceOvertimeStoreTest.php`
- `tests/Feature/ProjectOvertimeRelationTest.php`

## Verifikasi

- `php artisan test --compact tests\Feature\ProjectOvertimeRelationTest.php`
- `php artisan test --compact tests\Feature\PicAttendanceOvertimeStoreTest.php tests\Feature\ProjectOvertimeRelationTest.php`
- `vendor\bin\pint --dirty --format agent`
- `git diff --check`
