# Leave Seeder Working Days

## Perubahan
- Mengubah `LeaveRequestHistorySeeder` agar tanggal seed cuti dipilih dari hari kerja berurutan.
- Seeder sekarang melewati Sabtu/Minggu dan tanggal yang ada di tabel `attendances_holidays`.
- Menyelaraskan `happened_at` history dengan tanggal request masing-masing agar data demo tetap konsisten.

## Verifikasi
- `php -l database/seeders/LeaveRequestHistorySeeder.php`
- `php -l tests/Feature/LeaveRequestHistorySeederCoverageTest.php`
- `php artisan test --compact tests/Feature/LeaveRequestHistorySeederCoverageTest.php`
- `php artisan db:seed --class=LeaveRequestHistorySeeder --no-interaction`
- Cek hasil seed: 2026-06-08, 2026-06-09, 2026-06-10, 2026-06-11.
