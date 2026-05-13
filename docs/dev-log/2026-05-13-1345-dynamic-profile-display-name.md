# Dev Log - Dynamic Display Name di Profile Header

Tanggal: 2026-05-13 13:45 WIB  
File:
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/absensi/layouts_absensi/profileHeader.blade.php`

## Ringkasan
- Mengubah nama statis di profile header menjadi dinamis dari database.
- Sumber nama:
  - utama: `employee_profiles.name`
  - fallback 1: `users.username`
  - fallback 2: local-part dari `users.email`
  - fallback akhir: `-`
- Menyesuaikan path icon blue tick agar menggunakan `asset()`.
