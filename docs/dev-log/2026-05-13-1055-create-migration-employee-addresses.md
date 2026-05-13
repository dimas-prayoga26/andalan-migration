# Dev Log - Create Migration Employee Addresses

Tanggal: 2026-05-13 10:55 WIB  
File: `database/migrations/2026_05_13_035430_create_employee_addresses_table.php`

## Ringkasan
- Membuat migration tabel `employee_addresses` sesuai ERD.
- Struktur kolom:
  - `id` UUID primary key
  - `employee_uid` foreign UUID ke `employees.id`
  - `type`
  - `address_line`
  - `village`
  - `subdistrict`
  - `regency`
  - `province`
  - `country`
  - `postal_code`
  - `deleted_at` (soft deletes)
  - timestamps
