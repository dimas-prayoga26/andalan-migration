# PIC Attendance Supervisor Approval Module

Tanggal: 2026-06-25 04:27 WIB

## Ringkasan

- Menambahkan modul **PIC** terpisah dari Admin Attendance untuk kebutuhan supervisor/PIC melakukan review pengajuan cuti staf yang menjadi tanggung jawabnya.
- Modul menggunakan controller, route, dan view sendiri agar perubahan pada PIC tidak bercampur dengan alur Admin Attendance.
- Menu PIC hanya menampilkan navigasi **Attendance** dan **Leave**.

## Struktur Modul

- Controller:
  - `app/Http/Controllers/PicAttendance/PicAttendanceController.php`
  - `app/Http/Controllers/PicAttendance/PicAttendanceLeaveController.php`
- View:
  - `resources/views/pic_attendance/layout/navbar.blade.php`
  - `resources/views/pic_attendance/attendance/index.blade.php`
  - `resources/views/pic_attendance/attendance/detail-employees.blade.php`
  - `resources/views/pic_attendance/leave/index.blade.php`
  - `resources/views/pic_attendance/leave/detail.blade.php`
- Test:
  - `tests/Feature/PicAttendanceModuleTest.php`

## Route dan Akses

- Route PIC berada pada prefix `/pic-attendance` dan memakai middleware `position.permission:view-pic-attendance`.
- Route mencakup attendance summary, DataTable bulanan, detail attendance karyawan, daftar leave, DataTable pending/approved, detail leave, dan update supervisor review.
- Sidebar menambahkan menu **PIC** pada bagian HR Management.
- Permission `view-pic-attendance` didaftarkan di menu Authorization sehingga tetap dapat dikelola dari menu Authorization.

## Otorisasi PIC

- Seeder memberikan permission `view-pic-attendance` kepada position **Supervisor**.
- System Administrator dan role Superuser tidak mendapat bypass untuk permission PIC secara otomatis.
- Konsekuensinya, akses menu dan URL PIC hanya tersedia jika position user benar-benar memiliki permission PIC. Permission dapat diberikan ke position lain melalui Authorization bila diperlukan di masa depan.

## Scope Data

- PIC Attendance mengambil company aktif dari deployment user yang login.
- Data attendance dan leave dibatasi ke karyawan aktif pada company tersebut.
- Untuk Leave, daftar karyawan juga dibatasi berdasarkan relasi aktif pada tabel `employee_pic_assignments`, dengan supervisor yang sama dengan employee dari user yang login.
- Supervisor tidak dapat mengakses detail leave atau attendance karyawan di luar scope PIC-nya.

## Attendance PIC

- Halaman attendance menampilkan log harian, rekap bulanan, dan detail attendance karyawan yang berada dalam scope PIC.
- Rekap menggunakan hari kerja dan hari libur dari `attendance_holidays`, join date deployment, attendance, leave approved, business trip approved, attendance exception approved, dan overtime.
- Tabel bulanan dan detail attendance memakai endpoint JSON untuk DataTable.

## Leave PIC dan Alur Supervisor Review

- Tabel pending hanya menampilkan leave request yang masih menunggu review supervisor.
- Tabel approved hanya menampilkan request yang mempunyai history `supervisor_review` dengan `to_status = approved`.
- Pada halaman detail, supervisor dapat melakukan keputusan **Approve** atau **Reject**.
- Approval supervisor membuat atau memperbarui history `supervisor_review` menjadi `approved`, tetapi leave request tetap berstatus `pending` agar dapat diteruskan ke proses HR verification/final decision.
- Rejection supervisor memperbarui history `supervisor_review` dan status leave request menjadi `rejected`.
- Riwayat, attachment, leave type, PIC/supervisor, serta data identitas staff ditampilkan dari relasi data yang relevan.

## File Pendukung yang Diubah

- `routes/web.php`
- `resources/views/layouts/sidebar.blade.php`
- `app/Http/Controllers/AuthorizationController.php`
- `app/Models/User.php`
- `app/View/Composers/SidebarPermissionComposer.php`
- `database/seeders/PositionPermissionSeeder.php`

## Verifikasi

- Test modul tersedia pada `tests/Feature/PicAttendanceModuleTest.php` untuk memastikan controller PIC terpisah, route benar, view tersedia, navbar PIC tidak memuat Business Trip/Overtime, dan permission PIC terdaftar.
- Perubahan berikutnya pada endpoint approved DataTable perlu memastikan filter approved diterapkan pada Eloquent Builder yang sudah terbentuk, bukan mengirim Builder ke helper yang mengharapkan daftar employee ID.
