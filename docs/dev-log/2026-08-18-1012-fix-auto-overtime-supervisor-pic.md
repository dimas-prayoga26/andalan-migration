# Fix Auto Overtime Supervisor PIC

Tanggal: 2026-08-18 10:12 WIB

## Ringkasan

- Memperbaiki auto overtime 12 jam agar `assigned_by` mengambil PIC/supervisor aktif dari `employee_pic_assignments`.
- Sebelumnya auto overtime memakai user staff yang melakukan clock-out sebagai `assigned_by`.
- Dampaknya halaman detail overtime menampilkan staff sebagai `Supervisor` dan fallback `Approved by Supervisor`, karena tampilan overtime membaca supervisor dari `overtimes.assigned_by`.
- Jika staff tidak memiliki PIC/supervisor aktif, sistem tetap fallback ke actor clock-out atau user employee agar auto overtime masih bisa dibuat.

## File yang Berubah

- `app/Services/Attendance/TwelveHourAutoOvertimeService.php`
- `tests/Feature/TwelveHourAutoOvertimeCreationTest.php`

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/TwelveHourAutoOvertimeCreationTest.php tests/Unit/TwelveHourAutoOvertimeServiceTest.php`
- `vendor/bin/pint --dirty --format agent`

Hasil terakhir: `4 passed (13 assertions)`.
