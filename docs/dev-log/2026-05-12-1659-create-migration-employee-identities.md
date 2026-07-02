# Dev Log - Create Migration employee_identities

Tanggal: 2026-05-12  
File: `database/migrations/2026_05_12_095855_create_employee_identities_table.php`

## Ringkasan
- Menambahkan migration tabel `employee_identities` sesuai ERD.
- Struktur kolom:
  - `id` (uuid primary)
  - `uid` (string 12, nullable, unique)
  - `employee_uid` (foreignUuid -> `employees.id`)
  - `nik`
  - `kk`
  - `npwp`
  - `bpjs_ketenagakerjaan`
  - `bpjs_kesehatan`
  - `ptkp_status`
  - timestamps
