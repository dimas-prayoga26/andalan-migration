# Trip Incentive dan Total Payment Hijau

Tanggal: 2026-06-05 15:50 WIB

## Ringkasan

- Mengubah class nominal `Trip Incentive` pada summary Business Trip menjadi `text-success`.
- Mengubah class nominal `Total Payment` pada summary Business Trip menjadi `text-success`.
- Menambahkan assertion test agar dua row tersebut tetap tampil hijau.

## File Perubahan

- `app/Http/Controllers/BusinessTripController.php`
- `tests/Feature/BusinessTripPageCleanupTest.php`

## Verifikasi

- Formatter:
  - `vendor/bin/pint --dirty --format agent`
- Test terfokus:
  - `php artisan test --compact tests/Feature/BusinessTripPageCleanupTest.php --filter=business_trip_expense_summary_uses_cash_advance_reimbursement_and_incentive`
