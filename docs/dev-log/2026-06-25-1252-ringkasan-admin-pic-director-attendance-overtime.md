# Ringkasan Admin, PIC, Director Attendance dan Overtime

Tanggal: 2026-06-25 12:52 WIB

## Ringkasan

- Menambahkan modul **PIC Attendance** sebagai area terpisah dari Admin Attendance untuk supervisor/PIC.
- Menambahkan modul **Director Attendance** sebagai area terpisah untuk kebutuhan director approval.
- Menambahkan navbar Overtime pada PIC Attendance dan Director Attendance.
- Menghubungkan menu sidebar **PIC** dan **Director** ke permission berbasis position.
- Menyesuaikan summary Overtime agar mengambil data dari database, bukan angka statis.
- Menyesuaikan tabel Pending dan Approved Overtime untuk Admin, PIC, dan Director dengan rules berbeda sesuai lifecycle.
- Menambahkan filter bulan dan tahun pada tabel Overtime agar data mengikuti periode yang dipilih.
- Menyesuaikan seeder Overtime dan `overtime_lifecycle_logs` agar memiliki variasi status lifecycle yang lebih lengkap.

## Modul PIC Attendance

- Controller PIC berada pada namespace `App\Http\Controllers\PicAttendance`.
- View PIC berada pada folder `resources/views/pic_attendance`.
- Navbar PIC berisi menu:
  - Attendance
  - Leave
  - Overtime
- Route PIC berada pada prefix `/pic-attendance`.
- Middleware permission yang digunakan: `position.permission:view-pic-attendance`.
- Permission `view-pic-attendance` diberikan ke position **Supervisor** melalui seeder permission.
- Scope data PIC dibatasi berdasarkan company aktif dan relasi PIC/supervisor yang relevan.

## Modul Director Attendance

- Controller Director berada pada namespace `App\Http\Controllers\DirectorAttendance`.
- View Director berada pada folder `resources/views/director_attendance`.
- Navbar Director berisi menu:
  - Attendance
  - Overtime
- Route Director berada pada prefix `/director-attendance`.
- Middleware permission yang digunakan: `position.permission:view-director-attendance`.
- Permission `view-director-attendance` didaftarkan pada Authorization agar dapat dikelola dari menu permission.

## Summary Overtime

- Pending dihitung dari overtime yang sudah masuk step `overtime_session_ended` dengan status `clock_out`, dan step `task_hours_verification` masih `pending`.
- SPV ACC dihitung dari `task_hours_verification` yang sudah `verified`.
- Director ACC dihitung dari step HR/Finance atau payroll yang sudah `calculated_locked`.
- Total Hours dihitung dari overtime yang sudah verified dan memiliki `actual_start_time` serta `actual_end_time`.
- Median Hours dan Avg. Hours dihitung dari durasi aktual overtime yang verified.
- Top Overtime dihitung dari employee dengan total durasi overtime terbesar.
- Weekend dan Weekday hours dihitung dari tanggal overtime, lalu dipisah berdasarkan hari libur akhir pekan atau hari kerja.
- Est. Cost tetap ditampilkan statis sesuai instruksi.

## Tabel Pending dan Approved Overtime

### Admin

- Pending:
  - Menampilkan semua overtime dengan lifecycle `task_hours_verification` berstatus `pending`.
- Approved:
  - Menampilkan overtime dengan lifecycle `director_approval` berstatus `approved`.

### PIC

- Pending:
  - Menampilkan overtime dengan lifecycle `task_hours_verification` berstatus `pending`.
- Approved:
  - Menampilkan overtime dengan lifecycle `task_hours_verification` berstatus `verified`.
- Scope PIC dibatasi ke overtime yang berkaitan dengan PIC/supervisor login.

### Director

- Pending:
  - Menampilkan overtime dengan lifecycle `director_approval` berstatus `pending`.
- Approved:
  - Menampilkan overtime dengan lifecycle `director_approval` berstatus `approved`.

## Filter Overtime

- Filter bulan dan tahun memakai query string `month` dan `year`.
- Filter tersedia pada halaman:
  - Admin Attendance Overtime
  - PIC Attendance Overtime
  - Director Attendance Overtime
- Pilihan bulan tetap Januari sampai Desember.
- Pilihan tahun mengikuti tahun yang tersedia dari query dan periode default.

## Seeder Overtime

- Seeder overtime diperbarui agar lifecycle tidak hanya berhenti di assignment request.
- Data seeder mencakup variasi lifecycle sampai:
  - Overtime Session Ended
  - Task & Hours Verification
  - Director Approval
  - Payroll/Finance calculation
  - Payment Distribution
- Status lifecycle yang digunakan mencakup:
  - `waiting`
  - `complete`
  - `clock_in`
  - `clock_out`
  - `pending`
  - `verified`
  - `calculated_locked`
  - `approved`
  - `upcoming`
- Status `upcoming` hanya dipakai untuk step Payment Distribution.

## File Utama

- `app/Http/Controllers/AdminAttendance/AttendanceOvertimeController.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceController.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceLeaveController.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php`
- `app/Http/Controllers/DirectorAttendance/DirectorAttendanceController.php`
- `app/Http/Controllers/DirectorAttendance/DirectorAttendanceOvertimeController.php`
- `app/Support/Attendance/OvertimeSummaryMetricBuilder.php`
- `app/Support/Attendance/OvertimeReviewTableBuilder.php`
- `resources/views/admin_attendance/overtime/index.blade.php`
- `resources/views/pic_attendance/layout/navbar.blade.php`
- `resources/views/pic_attendance/attendance/index.blade.php`
- `resources/views/pic_attendance/attendance/detail-employees.blade.php`
- `resources/views/pic_attendance/leave/index.blade.php`
- `resources/views/pic_attendance/leave/detail.blade.php`
- `resources/views/pic_attendance/overtime/index.blade.php`
- `resources/views/pic_attendance/overtime/detail.blade.php`
- `resources/views/director_attendance/layout/navbar.blade.php`
- `resources/views/director_attendance/attendance/index.blade.php`
- `resources/views/director_attendance/overtime/index.blade.php`
- `resources/views/director_attendance/overtime/detail.blade.php`
- `database/seeders/OvertimeDeadlineTaskSeeder.php`
- `database/seeders/PositionPermissionSeeder.php`
- `routes/web.php`

## Test

- `tests/Feature/PicAttendanceModuleTest.php`
- `tests/Feature/DirectorAttendanceModuleTest.php`
- `tests/Feature/OvertimeDeadlineTaskSeederTest.php`
- `tests/Unit/OvertimeSummaryMetricBuilderTest.php`
- `tests/Unit/OvertimeReviewTableBuilderTest.php`

## Verifikasi Terakhir

- `php artisan test --compact tests/Unit/OvertimeReviewTableBuilderTest.php`
- `php artisan test --compact tests/Unit/OvertimeSummaryMetricBuilderTest.php`
- `php artisan test --compact tests/Feature/PicAttendanceModuleTest.php`
- `php artisan test --compact tests/Feature/DirectorAttendanceModuleTest.php`

