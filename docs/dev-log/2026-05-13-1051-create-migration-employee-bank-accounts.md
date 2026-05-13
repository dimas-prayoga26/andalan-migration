# Dev Log - Create Migration Employee Bank Accounts

Tanggal: 2026-05-13 10:51 WIB  
File: `database/migrations/2026_05_13_035015_create_employee_bank_accounts_table.php`

## Ringkasan
- Membuat migration tabel `employee_bank_accounts` sesuai ERD.
- Struktur kolom:
  - `id` UUID primary key
  - `uid` (unique, nullable)
  - `employee_uid` foreign UUID ke `employees.id`
  - `bank_name`
  - `branch`
  - `account_number`
  - `account_holder_name`
  - `is_primary`
  - timestamps
