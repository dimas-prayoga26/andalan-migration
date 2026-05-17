# Dev Log - Fix LeaveRequestController Tidak Pakai Users Name

Tanggal: 2026-05-13 15:41 WIB  
File: `app/Http/Controllers/LeaveRequestController.php`

## Ringkasan
- Memperbaiki query yang masih memakai kolom `users.name` (kolom sudah tidak ada).
- Daftar staff filter sekarang query `users.id, users.username, users.email` + relasi employee, lalu nama display diambil dari `employee_profiles.name` (fallback ke `username/email`).
- Data table izin dan detail izin juga diubah agar tidak lagi memakai `user->name`.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php -l app/Http/Controllers/LeaveRequestController.php` passed.
