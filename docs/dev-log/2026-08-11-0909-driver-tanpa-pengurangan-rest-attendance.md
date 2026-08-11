# Posisi Tertentu Tanpa Pengurangan Rest Attendance

Tanggal: 2026-08-11 09:09 WIB

## Ringkasan

- Mengubah perhitungan jam kerja attendance agar employee dengan posisi `Driver` dan `Executive Assistant` tidak terkena pengurangan rest 1 jam.
- Aturan ini berlaku berdasarkan posisi employee, bukan berdasarkan `attendance_type` pada attendance rule.
- `attendance_type = flexible` tetap hanya mengatur konteks absensi flexible seperti sesi terbuka, jam clock-in/clock-out, dan validasi jam pulang.
- Tidak ada perubahan database, migration, atau setting baru pada perubahan ini.

## Aturan Perhitungan

- Jika employee memiliki posisi aktif `Driver` atau `Executive Assistant`, durasi kerja dihitung dari selisih penuh `clock_in` sampai `clock_out`.
- Jika employee bukan posisi exempt tersebut, durasi kerja tetap memakai aturan lama: durasi minimal 6 jam dikurangi rest 1 jam.
- Contoh:
  - `Driver`: `08:00 - 17:00 = 9 hours`.
  - `Executive Assistant`: `08:00 - 17:00 = 9 hours`.
  - Non-driver: `08:00 - 17:00 = 8 hours`.

## Cara Sistem Membaca Posisi Exempt

- Helper `Employee::isExemptFromAttendanceRestDeduction()` membaca daftar posisi exempt:
  - `Driver`
  - `Executive Assistant`
- Helper posisi membaca data dari:
  - `employee_deployments.current_position_id` melalui relasi `deployment.position`.
  - Relasi multi-position aktif melalui `deployment.positions`.
- Jika model employee belum tersimpan dan tidak memiliki relasi deployment, helper langsung mengembalikan `false` agar tidak memicu query database tidak perlu pada test/unit context.
- Saat ini nama posisi exempt masih dibaca langsung dari nama position, sehingga jika nama position diubah, logic juga perlu disesuaikan.

## Area yang Disesuaikan

- Clock-out attendance menghitung `work_hours` tanpa pengurangan rest untuk posisi exempt.
- Pengajuan attendance exception yang menghitung ulang jam kerja juga mengikuti pengecualian posisi exempt.
- Recap Admin Attendance mengikuti aturan posisi exempt saat menghitung label jam kerja dan total bulanan.
- Recap PIC Attendance mengikuti aturan yang sama.
- Staff Attendance Report mengikuti aturan posisi exempt pada label jam kerja.
- Attendance profile composer mengikuti aturan posisi exempt untuk hitungan weekly worked hours, termasuk batas maksimum harian posisi exempt menjadi 540 menit agar tidak terpotong ke 480 menit.

## File yang Berubah

- `app/Models/Employee.php`
- `app/Support/Attendance/AttendanceWorkDurationCalculator.php`
- `app/Services/Attendance/AttendanceMutationService.php`
- `app/Http/Controllers/AdminAttendance/AttendanceRecapController.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceController.php`
- `app/Http/Controllers/StaffAttendance/AttendanceReportController.php`
- `app/View/Composers/AttendanceProfileComposer.php`
- `tests/Unit/AttendanceWorkDurationCalculatorTest.php`
- `tests/Feature/AdminAttendanceDailyLogRowsTest.php`
- `tests/Feature/AttendanceReportExcelExportTest.php`

## Test dan Verifikasi

- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Unit/AttendanceWorkDurationCalculatorTest.php tests/Feature/AdminAttendanceDailyLogRowsTest.php tests/Feature/AttendanceReportExcelExportTest.php`

Hasil terakhir: `20 passed (138 assertions)`.
