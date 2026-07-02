# Dev Log - Seed Default Departments Name di Migration

Tanggal: 2026-05-12  
File: `database/migrations/2026_05_04_080749_create_departments_table.php`

## Ringkasan
- Menambahkan default data untuk tabel `departments` langsung di migration (`up()`).
- Nilai `name` yang dimasukkan:
  - `Superuser`
  - `Administrator`
  - `Board of Directors`
  - `Administration, Finance and Legal`
  - `Operations`
  - `Project Planning and Development`
  - `Information and Communications Technology`
  - `Marketing and Promotion`
- Setiap row diinsert dengan:
  - `id` UUID (`Str::uuid()`)
  - `status` = `active`
  - `created_at` dan `updated_at`
