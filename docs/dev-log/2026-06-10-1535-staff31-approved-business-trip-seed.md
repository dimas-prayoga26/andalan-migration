# Staff31 Approved Business Trip Seed

## Perubahan
- Menambahkan skenario Business Trip kedua untuk `staff31` dengan request number `TRP-RNB-STAFF31-APPROVED`.
- Skenario baru memakai `approval_status` `approved`.
- Lifecycle skenario baru berjalan sampai `submitted` complete dan `supervisor_review` complete, sementara step setelahnya tetap waiting.
- Seeder Business Trip sekarang mendukung beberapa skenario untuk satu staff tanpa mengubah skenario staff lain.

## Verifikasi
- `php -l database/seeders/BusinessTripSeeder.php`
- `php -l tests/Feature/BusinessTripSeederTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests/Feature/BusinessTripSeederTest.php`
- `php artisan db:seed --class=BusinessTripSeeder --no-interaction`
- Cek hasil seed:
  - `TRP-RNB-STAFF31` tetap `pending`, `supervisor_review` pending.
  - `TRP-RNB-STAFF31-APPROVED` menjadi `approved`, `supervisor_review` complete.
