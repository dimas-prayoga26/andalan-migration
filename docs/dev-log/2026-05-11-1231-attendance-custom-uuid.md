# 2026-05-11 12:31 - Attendance Pakai Custom UUID

## Perubahan
- Mengubah model `Attendance` agar memakai generator UUID custom project:
  - format `ddmmyyyy + urutan 4 digit`.
- Mengubah PK tabel `attendances` dari auto increment ke UUID string.
- Mengubah FK `attendance_logs.attendance_id` menjadi `foreignUuid` agar konsisten dengan PK `attendances`.
- Menyesuaikan `AttendanceFactory` agar `user_id` menggunakan `User::factory()` (kompatibel UUID user).

## File Terdampak
- `app/Models/Attendance.php`
- `database/migrations/2026_05_05_014430_create_attendances_table.php`
- `database/migrations/2026_05_05_065440_create_attendance_logs_table.php`
- `database/factories/AttendanceFactory.php`
