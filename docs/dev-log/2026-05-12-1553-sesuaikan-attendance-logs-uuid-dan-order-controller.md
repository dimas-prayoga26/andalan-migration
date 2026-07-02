# Dev Log - Sesuaikan Attendance Logs UUID dan Order Controller

Tanggal: 2026-05-12  
File:
- `app/Http/Controllers/AttendanceController.php`
- `app/Models/AttendanceLog.php` (formatting oleh Pint)

## Ringkasan
- Verifikasi `attendance_logs` sudah menggunakan UUID:
  - migration `create_attendance_logs_table` sudah `uuid('id')->primary()`
  - `attendance_id` sudah `foreignUuid(...)`
- Menyesuaikan controller agar kompatibel dan stabil untuk UUID:
  - mengubah pengurutan dari `orderByDesc('id')` menjadi `orderByDesc('created_at')` pada query attendance dan attendance_logs.
- Menjalankan formatter:
  - `vendor/bin/pint --dirty --format agent`
