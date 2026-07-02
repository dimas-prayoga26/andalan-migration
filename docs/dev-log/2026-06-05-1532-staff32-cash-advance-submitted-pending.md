# Staff32 Cash Advance Submitted Pending

Tanggal: 2026-06-05 15:32 WIB

## Ringkasan

- Menyesuaikan skenario `staff32` pada `BusinessTripSeeder`.
- Lifecycle step 3 `Cash Advance Submitted` sekarang berstatus `pending`, bukan `complete`.
- Datetime dan actor tetap terisi:
  - `happened_at`: `2026-06-05 11:00:00`
  - `actor`: `staff32`

## File Perubahan

- `database/seeders/BusinessTripSeeder.php`
- `tests/Feature/BusinessTripSeederTest.php`

## Verifikasi

- Formatter:
  - `vendor/bin/pint --dirty --format agent`
- Test terfokus:
  - `php artisan test --compact tests/Feature/BusinessTripSeederTest.php`
- Verifikasi database lokal:
  - `php artisan db:seed --class=BusinessTripSeeder --no-interaction`
  - `TRP-RNB-STAFF32` step 3 `cash_advance_submitted` berstatus `pending` dengan actor `staff32`.
