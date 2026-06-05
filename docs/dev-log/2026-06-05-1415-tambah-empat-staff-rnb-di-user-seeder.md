# Tambah Empat Staff RNB di User Seeder

Tanggal: 2026-06-05 14:15 WIB

## Ringkasan

- Menyesuaikan `UserSeeder` agar company `RNB` memiliki 4 akun role `Staff`.
- Company lain tetap dibuatkan 2 akun staff seperti pola seeder sebelumnya.
- Menambahkan test untuk memastikan hasil seeding membuat 4 akun staff aktif pada deployment company RNB.

## File Perubahan

- `database/seeders/UserSeeder.php`
- `tests/Feature/RnbStaffSeederTest.php`

## Verifikasi

- Formatter:
  - `vendor/bin/pint --dirty --format agent`
- Test terfokus:
  - `php artisan test --compact tests/Feature/RnbStaffSeederTest.php`
  - Di environment lokal saat ini test otomatis `skipped` karena PHP CLI belum memuat `pdo_sqlite`.
- Verifikasi database lokal:
  - `php artisan db:seed --class=UserSeeder --no-interaction`
  - `php artisan db:seed --class=EmployeeProfileSeeder --no-interaction`
  - Query role Staff + deployment company RNB menghasilkan 4 akun aktif: `staff31`, `staff32`, `staff33`, `staff34`.
