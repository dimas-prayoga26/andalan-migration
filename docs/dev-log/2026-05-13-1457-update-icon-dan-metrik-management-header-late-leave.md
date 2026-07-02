# Dev Log - Update Ikon & Metrik Management Header (Late dan Leave)

Tanggal: 2026-05-13 14:57 WIB  
File:
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/absensi/layouts_absensi/profileHeader.blade.php`

## Ringkasan
- Mengubah 3 card statistik untuk role management (board of directors & superuser):
  - Card 1: ikon `user group`, metrik `Staff Presence (Today)`
  - Card 2: ikon keterlambatan, metrik `Staff Late (Today)`
  - Card 3: ikon izin, metrik `Staff Leave (Today)`
- Menyesuaikan sumber data:
  - `Staff Late (Today)`: dari `attendances.status = terlambat`
  - `Staff Leave (Today)`: dari `leave_requests` (rentang tanggal hari ini, status approved jika kolom tersedia, active, dan non-deleted)

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php -l app/Http/Controllers/AttendanceController.php` passed.
