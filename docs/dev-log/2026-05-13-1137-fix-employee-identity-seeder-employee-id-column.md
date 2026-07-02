# Dev Log - Fix EmployeeIdentitySeeder Employee ID Column

Tanggal: 2026-05-13 11:37 WIB  
File: `database/seeders/EmployeeIdentitySeeder.php`

## Ringkasan
- Memperbaiki seeder `EmployeeIdentitySeeder` agar sesuai struktur tabel `employee_identities` terbaru.
- Mengganti referensi kolom dari `employee_uid` ke `employee_id` pada query:
  - cek existing data
  - update data
  - insert data baru

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php artisan db:seed --class=EmployeeIdentitySeeder --no-interaction` berhasil.
