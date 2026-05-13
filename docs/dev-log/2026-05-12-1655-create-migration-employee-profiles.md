# Dev Log - Create Migration employee_profiles

Tanggal: 2026-05-12  
File: `database/migrations/2026_05_12_095327_create_employee_profiles_table.php`

## Ringkasan
- Menambahkan migration tabel `employee_profiles` sesuai struktur dari ERD.
- Kolom yang dibuat:
  - `id` (uuid primary)
  - `uid` (string 12, nullable, unique)
  - `employee_uid` (foreignUuid -> `employees.id`)
  - `name`
  - `nickname`
  - `gender`
  - `place_of_birth`
  - `date_of_birth`
  - `nationality`
  - `ethnicity`
  - `marital_status`
  - `religion`
  - `blood_type`
  - `height`
  - `weight`
  - `sibling_count`
  - `sibling_index`
  - `hobbies`
  - timestamps
