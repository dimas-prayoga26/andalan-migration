# Business Trip Calendar Action Buttons

## Perubahan
- Menambahkan lifecycle props pada event calendar Business Trip untuk mengatur tombol modal.
- Tombol `Submit Task` aktif hanya ketika lifecycle `supervisor_review` berstatus `complete`.
- Tombol `Reimbursement` aktif hanya ketika lifecycle `reimbursement_submitted` berstatus `pending`.
- Tombol modal Business Trip sekarang memiliki URL dinamis ke form Cash Advance/Task dan Reimbursement, serta disabled state saat belum waktunya.

## Verifikasi
- `php -l app/Http/Controllers/AttendanceController.php`
- `php -l tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- Cek DB staff31:
  - `TRP-RNB-STAFF31`: Submit Task disabled, Reimbursement disabled.
  - `TRP-RNB-STAFF31-APPROVED`: Submit Task enabled, Reimbursement disabled.
