# Hapus User Documents Gunakan Employee Identities

Tanggal: 2026-06-30 18:36 WIB

## Ringkasan

- Menghapus migration `user_documents` dari schema fresh karena data identitas user sudah dipusatkan di `employee_identities`.
- Menghapus dependency seeder aktif terhadap `user_documents`.
- Mengarahkan data dokumen seed demo ke `employee_identities`.
- Menyesuaikan `EmployeeIdentitySeeder` agar tidak lagi membaca dari `user_documents` dan tidak menimpa nilai identitas yang sudah ada dengan data kosong.

## File Perubahan

- Migration lama `user_documents` dihapus dari schema fresh.
- `database/seeders/UserSeeder.php`
- `database/seeders/LegacySqlUserSeeder.php`
- `database/seeders/NiskalaMultiPicLeaveSeeder.php`
- `database/seeders/EmployeeIdentitySeeder.php`
- `tests/Feature/UserDocumentsRetirementTest.php`
- `tests/Feature/UserDocumentsSourceCleanupTest.php`

## Catatan Database

- Table `user_documents` tidak dibuat lagi pada `migrate:fresh --seed`.
- Table `employee_identities` tetap berisi 26 row setelah migration drop.

## Verifikasi

- `php artisan migrate:fresh --seed --no-interaction`
- `php artisan db:seed --class=EmployeeIdentitySeeder --no-interaction`
- `php artisan test --compact tests\Feature\UserDocumentsSourceCleanupTest.php`
- `php artisan test --compact tests\Feature\UserDocumentsRetirementTest.php`
- `vendor\bin\pint --dirty --format agent`
- `git diff --check`

## Catatan Verifikasi

- `UserDocumentsRetirementTest` ter-skip di environment lokal karena extension `pdo_sqlite` tidak tersedia.
- `UserSeeder` belum bisa diverifikasi langsung di database lokal karena data legacy sudah memiliki username `superuser` dengan email berbeda, sehingga terkena unique constraint `users_username_unique`.
- `NiskalaMultiPicLeaveSeeder` belum bisa diverifikasi langsung di database lokal karena company `Niskala` tidak tersedia pada data aktif.
