# Dev Log - Create PositionSeeder untuk Table positions

Tanggal: 2026-05-12  
File:
- `database/seeders/PositionSeeder.php`
- `database/seeders/DatabaseSeeder.php`

## Ringkasan
- Menambahkan seeder baru `PositionSeeder` untuk tabel `positions`.
- Data `name` yang diinsert:
  - `System Administrator`
  - `Commissioner Independent`
  - `Commissioner`
  - `Chief Operating Officer`
  - `Director`
  - `Legal Officer & Partnership`
  - `Finance and Administration Coordinator`
  - `Accounting and Taxation`
  - `Operations Coordinator`
  - `Interior Design`
  - `Architecture Design`
  - `Web Developer`
  - `Documentation Event and Editor Video`
  - `Graphic Design`
  - `Branding Designer`
- Seeder menggunakan `upsert` berbasis `name`, dengan:
  - `id` UUID (`Str::uuid()`)
  - `status` = `active`
  - `created_at`, `updated_at`
- Mendaftarkan `PositionSeeder::class` di `DatabaseSeeder`.
