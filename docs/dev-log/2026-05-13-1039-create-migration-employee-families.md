# Dev Log - Create Migration Employee Families

Tanggal: 2026-05-13 10:39 WIB  
File: `database/migrations/2026_05_13_033733_create_employee_families_table.php`

## Ringkasan
- Membuat migration tabel `employee_families`.
- Kolom yang ditambahkan:
  - `id` UUID primary key
  - `uid` (unique, nullable)
  - `employee_uid` (foreign UUID ke `employees.id`)
  - `name`
  - `sibling_index`
  - `relationship`
  - `gender`
  - `place_of_birth`
  - `date_of_birth`
  - `occupation`
  - `bpjs_kesehatan_number`
  - `is_dependents`
  - `is_emergency_contact`
  - `phone_number`
  - timestamps
