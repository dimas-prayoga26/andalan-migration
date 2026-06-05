# Seeder Business Trip RNB Staff Lifecycle

Tanggal: 2026-06-05 14:45 WIB

## Ringkasan

- Menambahkan `BusinessTripSeeder` untuk membuat 1 data business trip pada masing-masing staff RNB: `staff31`, `staff32`, `staff33`, dan `staff34`.
- Semua trip memakai rentang tanggal dinas `2026-06-10` sampai `2026-06-15`.
- Skenario lifecycle:
  - `staff31`: sampai `Trip Request Submitted`.
  - `staff32`: sampai Phase 2 step 3 `Cash Advance Submitted`.
  - `staff33`: sampai Phase 4 step 6 `Trip Report & Task Submitted` dan step 7 masih `waiting`, dengan cash advance kategori `Transportation`, `Accommodation`, `Meals & Entertaintment`, dan `Local Transport`.
  - `staff34`: sampai step 7 `Reimbursement Submitted`, dengan cash advance `Local Transport` dan `Meals & Entertaintment`, lalu reimbursement `Transportation` dan `Accommodation`.
- Seeder membuat placeholder attachment/receipt di disk `public` agar path lampiran tidak kosong.
- Mendaftarkan `BusinessTripSeeder` ke `DatabaseSeeder` setelah `EmployeePicAssignmentSeeder`.

## File Perubahan

- `database/seeders/BusinessTripSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `tests/Feature/BusinessTripSeederTest.php`

## Verifikasi

- Formatter:
  - `vendor/bin/pint --dirty --format agent`
- Test terfokus:
  - `php artisan test --compact tests/Feature/BusinessTripSeederTest.php`
- Verifikasi database lokal:
  - `php artisan db:seed --class=BusinessTripSeeder --no-interaction`
  - Query ringkasan menghasilkan `staff31` sampai step 1, `staff32` sampai step 3, `staff33` sampai step 6, dan `staff34` sampai step 7.
