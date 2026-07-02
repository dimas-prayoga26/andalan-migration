# Dev Log - Header User Name Ambil dari Employee Profiles

Tanggal: 2026-05-13 15:27 WIB  
File: `resources/views/layouts/header.blade.php`

## Ringkasan
- Mengubah source `$headerUserName` di layout header.
- Prioritas nama sekarang:
  1. `employee_profiles.name` (berdasarkan `employee_id` user login)
  2. fallback `users.username`
  3. fallback prefix `users.email`
  4. fallback akhir `-`
