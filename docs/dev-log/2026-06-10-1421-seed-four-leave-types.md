# Seed Four Leave Types

## Ringkasan
- Mengubah `LeaveRequestHistorySeeder` agar membuat satu seed leave request approved untuk masing-masing tipe:
  - `SPECIAL`
  - `ANNUAL`
  - `SICK`
  - `UNPAID`
- Membersihkan dummy seed lama dengan prefiks `[Seeder] RNB dummy leave request%` supaya tidak tersisa annual pending/rejected lama.
- Mengambil satu subtype aktif untuk cuti khusus dari `leave_sub_types`.
- Karena `leave_requests` tidak memiliki kolom `special_leave_sub_type_id`, subtype cuti khusus disimpan di reason dan metadata history.

## File Terkait
- `database/seeders/LeaveRequestHistorySeeder.php`
- `tests/Feature/LeaveRequestHistorySeederCoverageTest.php`

## Verifikasi
- `php -l database/seeders/LeaveRequestHistorySeeder.php`
- `php -l tests/Feature/LeaveRequestHistorySeederCoverageTest.php`
- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/LeaveRequestHistorySeederCoverageTest.php`
- `git diff --check`
