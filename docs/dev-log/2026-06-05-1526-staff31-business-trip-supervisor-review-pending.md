# Staff31 Business Trip Supervisor Review Pending

Tanggal: 2026-06-05 15:26 WIB

## Ringkasan

- Menyesuaikan skenario `staff31` pada `BusinessTripSeeder`.
- Lifecycle `staff31` sekarang berhenti di step 2 `Supervisor Review` dengan status `pending`.
- Step 1 `Trip Request Submitted` tetap `complete`.
- Step 3 dan seterusnya tetap `waiting`.

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
  - `TRP-RNB-STAFF31` step 2 `supervisor_review` berstatus `pending`, `actor_id = null`, `happened_at = null`.
  - Step 3 `cash_advance_submitted` tetap `waiting`.
