# Admin Attendance Leave Route And Recap Cleanup

Tanggal: 2026-06-23 WIB

## Ringkasan

- Menambahkan route admin attendance untuk halaman Leave dan detail Leave.
- Menghubungkan tab `Leave` pada navbar Admin Attendance ke route Laravel, bukan file HTML statis.
- Menghubungkan tombol/detail pada halaman Leave admin ke route detail Leave.
- Membersihkan style yang tidak dipakai pada halaman detail employee recap attendance.
- Memperluas test struktur Admin Attendance agar route, view, navbar, dan cleanup style tetap terjaga.

## Admin Attendance Leave

- Menambahkan controller `AttendanceLeaveController`.
- Method `index()` menampilkan view `admin_attendance.leave.index`.
- Method `detail()` menampilkan view `admin_attendance.leave.detail`.
- Menambahkan route:
  - `admin-attendance.leave`
  - `admin-attendance.leave.detail`
- Kedua route memakai middleware `position.permission:view-admin-attendance`.
- Tab `Leave` pada `admin_attendance.layout.navbar` sekarang memakai `route('admin-attendance.leave')`.
- Active state tab Leave sekarang memakai `request()->routeIs('admin-attendance.leave*')`.
- Link detail pada halaman Leave admin diarahkan ke `route('admin-attendance.leave.detail')`.

## Admin Attendance Recap Detail

- Membersihkan CSS yang tidak lagi dipakai pada `recap_attendance/detail-employees.blade.php`.
- Style yang dipertahankan hanya style yang masih digunakan untuk avatar detail employee.
- Selector lama yang tidak dipakai lagi dihapus agar view detail tidak membawa sisa style dari copy-an halaman lain.

## Test Dan Verifikasi

- Test struktur Admin Attendance diperluas untuk memastikan:
  - Route Leave dan detail Leave terdaftar.
  - Route Leave menunjuk ke `AttendanceLeaveController`.
  - View Leave index dan detail tersedia.
  - Navbar Leave memakai named route dan active route yang benar.
  - Detail employee recap tidak membawa selector style lama yang sudah tidak dipakai.
- Verifikasi terakhir:
  - `vendor\\bin\\pint --dirty --format agent`
  - `php artisan route:list --name=admin-attendance.leave`
  - `php artisan view:cache`
  - `php artisan view:clear`
  - `php artisan test --compact tests\\Feature\\AdminAttendanceOverviewStructureTest.php`

## File Perubahan Utama

- `app/Http/Controllers/AdminAttendance/AttendanceLeaveController.php`
- `routes/web.php`
- `resources/views/admin_attendance/layout/navbar.blade.php`
- `resources/views/admin_attendance/leave/index.blade.php`
- `resources/views/admin_attendance/leave/detail.blade.php`
- `resources/views/admin_attendance/recap_attendance/detail-employees.blade.php`
- `tests/Feature/AdminAttendanceOverviewStructureTest.php`
