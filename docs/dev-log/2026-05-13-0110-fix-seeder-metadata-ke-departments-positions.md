# Fix Seeder Metadata ke Departments/Positions

Tanggal: 2026-05-13 01:10 WIB

## Ringkasan
- Perbaiki error seeding karena tabel `meta_data_divisions` dan `meta_data_positions` sudah tidak dipakai.
- Seeder utama disesuaikan ke tabel baru `departments` dan `positions`.
- Seeder user diselaraskan dengan struktur tabel `users` terbaru (tanpa kolom `name`).

## Perubahan
- Hapus pemanggilan seeder lama dari `DatabaseSeeder`:
  - `MetaDataDivisionSeeder::class`
  - `MetaDataPositionSeeder::class`
- Ubah query referensi di `UserSeeder`:
  - `meta_data_divisions` -> `departments`
  - `meta_data_positions` -> `positions`
- Ubah tipe id relasi deployment di `UserSeeder` dari integer ke string UUID untuk:
  - `current_department_id`
  - `current_position_id`
- Tambah helper `toNullableString()` di `UserSeeder`.
- Hapus assignment kolom `name` saat create/update user, dan ganti fallback nickname dari `username/email`.

## Validasi
- `php artisan db:seed --class=UserSeeder --no-interaction` -> berhasil.
- `php artisan db:seed --no-interaction` -> berhasil.

