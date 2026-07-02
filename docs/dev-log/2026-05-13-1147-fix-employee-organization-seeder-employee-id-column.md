# Dev Log - Fix EmployeeOrganizationSeeder Employee ID Column

Tanggal: 2026-05-13 11:47 WIB  
File: `database/seeders/EmployeeOrganizationSeeder.php`

## Ringkasan
- Menyesuaikan seeder `EmployeeOrganizationSeeder` dengan struktur tabel `employee_organization` terbaru.
- Perubahan:
  - Ganti kolom filter `employee_uid` menjadi `employee_id`.
  - Ganti kolom insert `employee_uid` menjadi `employee_id`.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php artisan db:seed --class=EmployeeOrganizationSeeder --no-interaction` berhasil.
