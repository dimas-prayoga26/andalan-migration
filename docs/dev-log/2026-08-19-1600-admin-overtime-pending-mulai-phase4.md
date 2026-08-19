# Admin Overtime Pending Mulai Phase 4

Tanggal: 2026-08-19 16:00 WIB

## Ringkasan

- Memperbaiki tabel Pending pada Admin Attendance > Overtime yang sebelumnya langsung menampilkan overtime sejak step `Task & Hours Verification` masih `pending` (Phase 3, belum diverifikasi PIC).
- `ADMIN_LIFECYCLE_RANGE` di `OvertimeReviewTableBuilder` sebelumnya menyertakan `task_hours_verification`, padahal step itu defaultnya sudah `pending` sejak overtime dibuat (bukan `waiting`), sehingga selalu dianggap "sudah mulai" dan langsung tampil di tabel Pending.
- Sekarang `ADMIN_LIFECYCLE_RANGE` hanya mencakup `payroll_processing`, `director_approval`, dan `payment_disbursement` (Phase 4: Payroll & Payment). Overtime baru dianggap "started" dan tampil di tabel Pending Admin setelah PIC memverifikasi `Task & Hours Verification` (status jadi `verified`) dan step `HR / Payroll Processing` mulai berjalan (`payroll_processing` berubah dari `waiting`).
- Overtime yang `payment_disbursement`-nya sudah `complete`/`completed` tetap dikecualikan dari tabel Pending (pindah ke tabel Complete), tidak berubah.

## File yang Berubah

- `app/Support/Attendance/OvertimeReviewTableBuilder.php`
- `tests/Unit/OvertimeReviewTableBuilderTest.php`

## Test dan Verifikasi

- `php artisan test --compact tests/Unit/OvertimeReviewTableBuilderTest.php`
- `php artisan test --compact --filter=Overtime`
- `vendor/bin/pint --dirty --format agent`

Hasil terakhir: `46 passed (1036 assertions)`.
