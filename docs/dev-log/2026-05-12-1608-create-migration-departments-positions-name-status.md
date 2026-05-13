# Dev Log - Create Migration Departments & Positions (name, status)

Tanggal: 2026-05-12  
File:
- `database/migrations/2026_05_12_090531_create_departments_table.php`
- `database/migrations/2026_05_12_090531_create_positions_table.php`

## Ringkasan
- Menambahkan migration baru:
  - tabel `departments`
  - tabel `positions`
- Struktur keduanya:
  - `id`
  - `name` (string)
  - `status` (string, default `active`)
  - timestamps
