# Dev Log - Dynamic Profile Header Info dari Database

Tanggal: 2026-05-13 13:36 WIB  
File:
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/absensi/layouts_absensi/profileHeader.blade.php`

## Ringkasan
- Mengubah 3 item info di profile header agar dinamis dari database:
  - List 1 (`suitcase`): dari `employee_deployments.current_position_id -> positions.name`
  - List 2 (`map-marker`): dari `employee_addresses` kolom `village` dan `subdistrict`
  - List 3 (`envelope`): dari `users.business_email` (fallback ke `users.email`)
- Menambahkan fallback `-` jika data belum tersedia.
- Menambahkan guard `Schema::hasTable(...)` dan `Schema::hasColumn(...)` agar aman pada kondisi migrasi belum lengkap.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php -l app/Http/Controllers/AttendanceController.php` passed.
