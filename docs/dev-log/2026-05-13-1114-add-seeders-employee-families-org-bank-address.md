# Dev Log - Add Seeder Employee Families Organization Bank Accounts Addresses

Tanggal: 2026-05-13 11:14 WIB  
File:
- `database/seeders/EmployeeFamilySeeder.php`
- `database/seeders/EmployeeOrganizationSeeder.php`
- `database/seeders/EmployeeBankAccountSeeder.php`
- `database/seeders/EmployeeAddressSeeder.php`
- `database/seeders/DatabaseSeeder.php`

## Ringkasan
- Menambahkan 4 seeder baru:
  - `EmployeeFamilySeeder`
  - `EmployeeOrganizationSeeder`
  - `EmployeeBankAccountSeeder`
  - `EmployeeAddressSeeder`
- Semua seeder menggunakan pola `try-catch` dan idempotent update/insert.
- Menambahkan guard `Schema::hasTable(...)` agar seeder aman saat tabel target belum dimigrate.
- Mendaftarkan keempat seeder baru ke `DatabaseSeeder`.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php artisan db:seed --class=...` untuk keempat seeder berhasil dieksekusi.
