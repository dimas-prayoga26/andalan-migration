# Pending Leave Request Seeds

## Ringkasan
- Mengubah empat seed Leave Request RNB menjadi status `pending`.
- Mengosongkan `approved_by` dan `approved_at` pada seed leave request.
- Menghapus history `Approved` dari seed plan agar status request dan timeline konsisten.
- Menambahkan cleanup untuk seed lama dengan reason `approved`.

## File Terkait
- `database/seeders/LeaveRequestHistorySeeder.php`
- `tests/Feature/LeaveRequestHistorySeederCoverageTest.php`

## Verifikasi
- `php -l database/seeders/LeaveRequestHistorySeeder.php`
- `php -l tests/Feature/LeaveRequestHistorySeederCoverageTest.php`
- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/LeaveRequestHistorySeederCoverageTest.php`
- `git diff --check`
