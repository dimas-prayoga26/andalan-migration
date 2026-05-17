# Fix Ambiguous employee_id pada latestAddress (AbsensiProfileComposer)

## Tanggal
- 2026-05-18

## Perubahan
- Ubah eager-load relasi `employee.latestAddress` di `AbsensiProfileComposer` dari format kolom singkat menjadi closure `with()`.
- Select kolom relasi dibuat fully-qualified ke tabel `employee_addresses`:
  - `employee_addresses.id`
  - `employee_addresses.employee_id`
  - `employee_addresses.village`
  - `employee_addresses.subdistrict`
  - `employee_addresses.created_at`

## Alasan
- Relasi `latestOfMany('created_at')` menghasilkan subquery+join. Pemilihan kolom tanpa prefix tabel membuat `employee_id` menjadi ambigu di MySQL.

## Validasi
- `php -l app/View/Composers/AbsensiProfileComposer.php` -> OK
- `vendor/bin/pint --dirty --format agent` -> passed
