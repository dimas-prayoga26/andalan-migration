# Fix Ambiguous companies_id on activeAttendanceRule eager-load

## Tanggal
- 2026-05-18

## Perubahan
- Update eager-load relasi `employee.deployment.company.activeAttendanceRule` di `AttendanceController::resolveOfficeContext()` dari format singkat kolom (`relation:id,...`) menjadi closure `with()`.
- Kolom select pada relasi dibuat fully-qualified ke tabel `rules_of_attendaces`:
  - `rules_of_attendaces.id`
  - `rules_of_attendaces.companies_id`
  - `rules_of_attendaces.radius`
  - `rules_of_attendaces.ip_range`
  - `rules_of_attendaces.office_start_time`
  - `rules_of_attendaces.office_end_time`
  - `rules_of_attendaces.late_grace_minutes`

## Alasan
- Query `latestOfMany` membuat join/subquery yang menyebabkan nama kolom `companies_id` menjadi ambigu ketika tidak di-qualify.

## Validasi
- `php -l app/Http/Controllers/AttendanceController.php` -> OK.
- `vendor/bin/pint --dirty --format agent` -> passed.
