# Dev Log - Fix EmployeeBankAccountSeeder UID Column

Tanggal: 2026-05-13 12:11 WIB  
File: `database/seeders/EmployeeBankAccountSeeder.php`

## Ringkasan
- Memperbaiki `EmployeeBankAccountSeeder` agar sesuai struktur tabel `employee_bank_accounts`.
- Menghapus field `uid` dari payload seeder karena kolom `uid` tidak ada pada tabel.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php artisan db:seed --class=EmployeeBankAccountSeeder --no-interaction` berhasil.
