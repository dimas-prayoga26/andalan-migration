# Admin Attendance Authorization Overview

Tanggal: 2026-06-22 WIB

## Ringkasan

- Menambahkan pengaturan authorization berbasis position untuk akses menu.
- Menyesuaikan halaman Authorization agar list employee dan assign permission menjadi halaman/tab terpisah secara file, bukan sub-menu sidebar.
- Mengubah Admin Attendance Overview agar data daily, weekly, monthly, dan year-to-date berasal dari database secara dinamis.
- Merapikan tampilan chart dan kartu overview agar konsisten, tidak memakai data dummy, dan tidak menampilkan axis decimal.
- Menambahkan route recap attendance dan menghubungkan tab Attendance pada navbar Admin Attendance ke halaman recap.
- Menambahkan route detail employee recap dan menghubungkan tombol View More pada recap attendance ke halaman tersebut.
- Membersihkan asset, variabel, dan script chart Overview yang tidak dipakai dari halaman recap attendance.

## Authorization

- List employee sekarang menggunakan scope company user yang sedang login.
- User dengan position administrator pada company tertentu hanya melihat employee pada company tersebut.
- Super user tetap dapat melihat semua employee lintas company.
- Tabel list employee disederhanakan menjadi `Name`, `Position`, `Company`, `Status`, dan `Action`.
- Email tidak lagi ditampilkan pada kolom name.
- Assignment permission diubah menjadi berbasis position, bukan department.
- Halaman assign permission menampilkan daftar menu dan multiple select position.
- Kolom `Section` di assign permission dihapus.
- Select position dibuat satu field multiple select per menu.
- Position yang sudah terpilih diberi marker pada dropdown.
- Permission dapat diubah melalui menu Authorization.
- Sidebar menu disesuaikan agar menu yang tidak punya permission tidak muncul.
- Direct URL access disesuaikan agar tetap melewati middleware permission, bukan hanya disembunyikan di sidebar.
- Table `sidebar_menus` tidak dipakai lagi dan dibuat migration drop.

## Seeder Dan Data Authorization

- Menambahkan data user Administrator pada setiap company yang tersedia.
- Menambahkan position `Supervisor` pada seeder position.
- Menyesuaikan seed permission menu agar cocok dengan akses berbasis position.
- Menambahkan mapping permission-position awal untuk menu yang dibutuhkan.

## Admin Attendance Overview - Daily

- Total staff dihitung dari employee aktif pada company akun yang sedang login.
- Attendance overview donut menggunakan data real:
  - On Time
  - Late
  - Leave
  - Deviation
- Progress daily menggunakan data real dari attendance, leave request, attendance exception, dan business trip.
- Donut tetap memiliki border/shape meskipun semua data bernilai 0.
- Card `Today's Early Birds` mengambil data clock-in paling awal dari database.
- Card `Today's Early Birds` hanya menampilkan attendance dengan status `masuk` atau on-time.
- Card `Today's Running Late` mengambil data attendance terlambat dari database.
- Card `On Business Trip` mengambil business trip approved yang aktif pada tanggal tersebut.
- Card `Days on Leave (Time Off)` mengambil leave request approved yang aktif pada tanggal tersebut.
- Tombol Details pada card Days on Leave membuka modal `sick` untuk Sick Leave dan modal `annualLeave` untuk semua leave type lainnya.
- Modal Details menampilkan type, reason, duration, status, approval date, dan medical note untuk Sick Leave bila attachment tersedia.
- Dummy staff seperti `John Doe`, image broken, dan tombol `View more` dihapus dari kartu daily.
- Empty state ditambahkan untuk setiap kartu ketika tidak ada data.
- Avatar inisial pada kartu daily dibuat lingkaran penuh 48x48px dengan border tipis.
- Ukuran kartu `Attendance Overview` disamakan dengan kartu `Progress`.
- Arrow pada kartu Early Birds dan Running Late dihapus, sementara nomor urut rank tetap ditampilkan.

## Admin Attendance Overview - Weekly

- Weekly chart memakai range minggu berjalan Senin sampai Jumat.
- Untuk 22 June 2026, weekly range menjadi `22 - 26 June 2026`.
- Jika tanggal jatuh pada Sabtu/Minggu, chart tetap memakai Senin-Jumat pada minggu yang sama.
- Attendance weekly chart mengambil data real:
  - On Time
  - Late
- Out of Office weekly chart mengambil data real:
  - Leave
  - Business Trip
  - Deviation
- Overtime weekly chart mengambil total `calculated_hours` dari tabel `overtimes`.
- Label tanggal weekly tidak lagi hardcoded.
- Data dummy weekly chart dihapus.

## Admin Attendance Overview - Monthly

- Monthly chart memakai hari kerja pada bulan berjalan.
- Weekend tidak ditampilkan pada label chart.
- Attendance monthly chart mengambil data real:
  - On Time
  - Late
- Out of Office monthly chart mengambil data real:
  - Leave
  - Business Trip
  - Deviation
- Business trip dan leave yang overlap dengan tanggal kerja akan dihitung pada tanggal tersebut.
- Label tanggal monthly tidak lagi hardcoded `01-30 June 2026`.

## Admin Attendance Overview - Year To Date

- Leave overview year-to-date mengambil data Jan-Dec berdasarkan `leave_types` aktif.
- Series leave tidak lagi hardcoded seperti `Annual Leave`, `Special Leave`, `Sick Leave`, atau `Unpaid Leave`.
- Overtime overview year-to-date mengambil total `calculated_hours` per bulan dari tabel `overtimes`.
- Label tahun mengikuti tahun berjalan.
- Data dummy year-to-date dihapus.

## Chart Dan UI

- Y-axis chart disesuaikan agar tidak menampilkan decimal seperti `0.2`, `0.4`, atau `1.0`.
- Label Y-axis ditampilkan sebagai bilangan bulat mulai dari `1`.
- Label `0` pada Y-axis disembunyikan untuk tampilan yang lebih bersih.
- Helper JavaScript ditambahkan untuk format axis integer pada ApexCharts dan Chart.js.
- Chart tetap memakai data asli; perubahan hanya pada format tampilan axis.

## Admin Attendance Recap - Attendance Logs

- Attendance Logs harian pada menu Attendance sekarang mengambil data aktual berdasarkan tanggal hari ini dan company akun yang sedang login.
- Baris attendance menampilkan clock in, clock out, note, dan working hours dari tabel `attendances`.
- Lokasi clock in diambil dari `attendance_logs` dengan `type = 1` dan ditampilkan pada modal detail beserta peta bila koordinat tersedia.
- Attendance exception berstatus approved memakai modal `attendanceDeviationModal` dan menampilkan type, reason, time variance, serta status dari tabel `attendance_exceptions`.
- Leave request approved yang aktif pada tanggal tersebut memakai `attendanceLeaveDetailModal`; Sick Leave memakai `attendanceSickLeaveDetailModal`.
- Modal leave menampilkan type, reason, duration, status, approval date, dan attachment bila tersedia.
- ID modal lama `clockInAdmin`, `deviation`, dan `annualLeave` pada halaman recap diganti dengan ID yang menjelaskan fungsinya.

## Admin Attendance Recap - Monthly

- Attendance Logs Monthly sekarang menampilkan rekap database per employee pada company akun yang login.
- Filter bulan dan tahun memakai query string pada halaman recap.
- Rekap mencakup working days, working hours, on time, late, leave, deviation, alpha, business trip, overtime, dan leave pada tahun terpilih.
- Hari kerja hanya Senin-Jumat serta mengecualikan holiday pada tabel `attendances_holidays`.
- Untuk bulan berjalan, rekap berhenti pada tanggal hari ini agar hari mendatang tidak dihitung sebagai Alpha.
- Tombol View More meneruskan employee, bulan, dan tahun ke detail attendance employee.
- Detail employee hanya dapat dibuka untuk employee pada company akun yang login.
- Profil, metric cards, chart Days Worked, On Time, Late, Monthly Hours, Overtime, dan tabel attendance detail mengambil data dari database untuk periode yang sama.

## File Perubahan Utama

- `app/Http/Controllers/AuthorizationController.php`
- `app/Http/Controllers/AdminAttendance/AttendanceOverviewController.php`
- `app/Http/Middleware/EnsurePositionPermission.php`
- `app/View/Composers/SidebarPermissionComposer.php`
- `app/Models/Permission.php`
- `app/Models/Position.php`
- `app/Models/User.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `database/migrations/2026_06_21_163635_create_position_has_permissions_table.php`
- `database/migrations/2026_06_21_165133_drop_sidebar_menus_table.php`
- `database/seeders/PositionPermissionSeeder.php`
- `database/seeders/MetaDataPositionSeeder.php`
- `database/seeders/PositionSeeder.php`
- `database/seeders/UserSeeder.php`
- `resources/views/authorization/index.blade.php`
- `resources/views/authorization/access-menus.blade.php`
- `resources/views/layouts/sidebar.blade.php`
- `resources/views/admin_attendance/overview/index.blade.php`
- `routes/web.php`

## Test Dan Verifikasi

- Formatter:
  - `vendor/bin/pint --dirty --format agent`
- Test terfokus:
  - `php artisan test --compact tests/Feature/AdminAttendanceOverviewStructureTest.php`
  - `php artisan test --compact tests/Feature/AuthorizationEmployeeListScopeTest.php`
  - `php artisan test --compact tests/Feature/AuthorizationMenuRouteTest.php`
- Render Blade:
  - Render controller `AttendanceOverviewController@index` berhasil `ok`.
- Cache view:
  - `php artisan view:clear`

## Catatan Perilaku

- Weekly overview saat ini memakai minggu kerja Senin-Jumat, bukan tujuh hari kalender.
- Monthly overview saat ini hanya menampilkan hari kerja.
- Business trip atau leave yang melewati weekend hanya terlihat pada label hari kerja yang ditampilkan.
- Jika weekly jatuh pada akhir bulan dan Jumat masuk bulan berikutnya, range weekly dapat melewati bulan berjalan.
