# Tambah Additional Position Erlin Mevia Leonie

Tanggal: 2026-06-30 19:02 WIB

## Ringkasan

- Menambahkan mapping additional position pada seeder untuk akun Erlin, Mevia, dan Leonie.
- Erlin dipastikan memiliki posisi aktif `System Administrator` dan `Accounting and Taxation`.
- Mevia dipastikan memiliki posisi aktif `System Administrator`.
- Leonie dipastikan memiliki posisi aktif `System Administrator`.
- Mapping ditambahkan pada `NiskalaMultiPicLeaveSeeder` untuk akun demo dan `LegacySqlUserSeeder` untuk data legacy berdasarkan email.
- Database lokal aktif juga sudah diperbarui melalui insert ke pivot `employee_deployment_positions`.

## Hasil Database Lokal

- `Leonie Putri Andhari`: primary `Finance and Administration Coordinator`, active positions `Finance and Administration Coordinator`, `System Administrator`.
- `Mevia Dikta Namira`: primary `System Administrator`, active positions `System Administrator`.
- `Tsabita Anisa Erliana`: primary `Accounting and Taxation`, active positions `Accounting and Taxation`, `System Administrator`.

## File Perubahan

- `database/seeders/NiskalaMultiPicLeaveSeeder.php`
- `database/seeders/LegacySqlUserSeeder.php`
- `tests/Feature/EmployeeMultiplePositionSupportTest.php`

## Verifikasi

- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests\Feature\EmployeeMultiplePositionSupportTest.php`
- Query verifikasi pivot `employee_deployment_positions` untuk email:
  - `halloerlin@gmail.com`
  - `diktanamira@gmail.com`
  - `leonieputri7@gmail.com`
