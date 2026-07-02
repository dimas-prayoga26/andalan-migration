# Dev Log - Create Migration Employee Organization

Tanggal: 2026-05-13 10:46 WIB  
File: `database/migrations/2026_05_13_034414_create_employee_organization_table.php`

## Ringkasan
- Membuat migration tabel `employee_organization` sesuai ERD.
- Struktur kolom:
  - `id` UUID primary key
  - `employee_uid` foreign UUID ke `employees.id`
  - `organization_name`
  - `location`
  - `start_date`
  - `end_date`
  - `position`
  - `description`
  - `certificate_path`
  - `deleted_at` (soft deletes)
  - timestamps
