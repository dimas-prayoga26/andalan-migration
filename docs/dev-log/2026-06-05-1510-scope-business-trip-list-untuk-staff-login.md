# Scope Business Trip List untuk Staff Login

Tanggal: 2026-06-05 15:10 WIB

## Ringkasan

- Menyesuaikan `BusinessTripController@index` agar role `Staff` hanya melihat business trip milik employee login.
- Menambahkan guard pada `BusinessTripController@show` agar staff tidak bisa membuka detail business trip milik staff lain lewat URL langsung.
- Role non-staff tetap memakai akses list seperti sebelumnya.

## File Perubahan

- `app/Http/Controllers/BusinessTripController.php`
- `tests/Feature/BusinessTripStaffScopeTest.php`

## Verifikasi

- Formatter:
  - `vendor/bin/pint --dirty --format agent`
- Test terfokus:
  - `php artisan test --compact tests/Feature/BusinessTripStaffScopeTest.php`
- Verifikasi database lokal:
  - Total seed RNB tetap 4 trip.
  - Filter `employee_id` untuk login `staff31` hanya menghasilkan `TRP-RNB-STAFF31`.
