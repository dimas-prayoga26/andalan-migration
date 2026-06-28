# Project Management, Director Attendance, dan Overtime Follow-up

Tanggal: 2026-06-28 WIB

## Ringkasan

- Menyesuaikan layout Project Management agar tidak lagi terasa seperti layout Attendance biasa.
- Menambahkan composer khusus Project Management untuk memisahkan data header project dari `AttendanceProfileComposer`.
- Menyesuaikan Director Attendance agar mengikuti pola tampilan Attendance admin/supervisor.
- Merapikan detail Overtime pada Admin dan Director agar kontrol yang tidak boleh diedit dibuat readonly.
- Menyesuaikan alur card, tabel, dan detail Overtime berdasarkan role Admin, PIC/Supervisor, dan Director.

## Project Management

- Header Project Management dipisahkan dari header Attendance.
- Navbar Project Management dibatasi menjadi:
  - Overview
  - Task List
  - Project
- Top metric card diubah menjadi konteks project:
  - Tasks Completed
  - In Progress
  - Total Tasks
  - Daily | Project
  - Workload
- Icon pada metric card disesuaikan agar relevan dengan pekerjaan project/task.
- Profile header Project Management diarahkan untuk menampilkan:
  - nama employee
  - posisi
  - lokasi
  - email
- Data profile Project Management tidak lagi ideal jika terus bergantung penuh pada `AttendanceProfileComposer`, sehingga disiapkan composer khusus `ProjectManagementProfileComposer`.
- Folder layout Project Management ditata ulang di `resources/views/project_management/layouts`.
- Folder `resources/views/project_management/task_list` mulai disiapkan untuk pemisahan halaman Task List.
- Style chart Project Management disesuaikan agar tidak konflik dengan style chart Attendance, terutama pada area `Task Overview`.

## Attendance Profile Composer

- `AttendanceProfileComposer` tetap dipakai untuk halaman Attendance.
- Untuk Project Management, data header perlu dikontrol oleh composer terpisah agar label, card, dan metadata profile tidak ikut aturan Attendance.
- Pemisahan ini menjaga agar perubahan di Project Management tidak merusak Attendance Overview.

## Director Attendance

- Menu Director Attendance tetap memakai navbar:
  - Attendance
  - Overtime
- Halaman Attendance Director disesuaikan agar mengikuti pola admin/supervisor:
  - Attendance Logs harian
  - Attendance Logs Monthly
  - filter bulan dan tahun
  - tombol View More ke detail employee
- Scope data Director Attendance diarahkan agar dapat menampilkan employee dalam company/scope Director, bukan kosong.
- Detail employee Director Attendance disiapkan sebagai file terpisah.

## Admin Attendance Overtime

- Detail Overtime Admin difungsikan dari card Overtime.
- Detail Admin dibuat readonly:
  - tombol Approve Overtime Session tidak bisa dipakai untuk update
  - checkbox task tidak bisa diubah
  - tombol edit task tidak aktif
  - tombol delete task tidak aktif
- Card Overtime Admin menampilkan status lifecycle dari `Task & Hours Verification` sampai `Payment Distribution`.
- Tabel kanan pada Admin Overtime diubah dari Approved menjadi Complete.
- Complete hanya berisi data dengan lifecycle `Payment Distribution` berstatus complete.
- Pending Admin menampilkan data yang sudah masuk range lifecycle Admin tetapi belum selesai Payment Distribution.
- Status Payment Distribution complete diperbaiki agar tidak tampil sebagai waiting.

## PIC Attendance Overtime

- Card PIC Overtime menampilkan satu lifecycle status aktif saja, bukan seluruh daftar log.
- Lifecycle yang ditampilkan di PIC berhenti sampai `Task & Hours Verification`.
- Urutan lifecycle PIC:
  - Overtime Assignment Submitted
  - Overtime Session Started
  - Overtime Session Ended
  - Task & Deliverables Submitted
  - Task & Hours Verification
- Detail card Overtime PIC diarahkan ke route detail memakai UID.
- Add Overtime pada PIC memakai modal dengan field:
  - Instruction
  - Start Date
  - End Date
  - Start Time
  - End Time
  - Assign Staff
- Assign Staff dibatasi ke staff yang berada di bawah PIC/Supervisor melalui relasi PIC assignment/management.
- Date input pada modal Add Overtime memakai datepicker agar trigger tanggal tidak melebar ke area yang salah.
- Setelah PIC membuat overtime, sistem membuat log `Overtime Assignment Submitted` dengan status complete.

## Director Overtime

- Card Director Overtime hanya menampilkan lifecycle `Director Approval`.
- Status yang tampil pada card Director dibatasi ke pending atau approved/rejected sesuai data director approval.
- Detail Director Overtime difungsikan memakai UID.
- Director hanya dapat melakukan update pada panel Confirm Overtime.
- Saat Director mengubah status:
  - `Director Approval` pending -> approved/rejected
  - jika approved, `HR / Payroll Processing` berubah menjadi waiting
- Tombol edit/delete task tidak ditampilkan/dipakai pada Director.
- Button Approve Overtime Session pada Director dibuat readonly.
- Layout detail Director dirapikan:
  - `Overtime Verification` dan `Review Overtime Session` berada pada baris kanan atas.
  - `Task Items` dan `Confirm Overtime` berada tepat di bawah dua panel tersebut.
  - Kolom kanan memakai pembagian 6/6 untuk panel kecil.
- Icon pada `Overtime Verification` diperbaiki agar tidak terlihat gepeng.

## Overtime Detail Data

- Nama pada judul detail Overtime diambil dari `employee_profiles.name` dan ditampilkan tebal.
- Detail Overtime mengikuti referensi dari detail overtime staff.
- Data yang dipakai pada detail meliputi:
  - Record ID
  - Full Name
  - Supervisor
  - tanggal overtime
  - scheduled time
  - approved time
  - total duration
  - instruction
  - compensation/payroll detail
  - approval trail
  - task items
- Approved by Director ditampilkan dari lifecycle director approval jika datanya tersedia.

## Seeder dan Data Role

- Seeder employee/user disesuaikan agar setiap company memiliki:
  - 1 Admin
  - 1 Director
  - 1 Supervisor
  - maksimal 5 Staff
- Seeder overtime dan lifecycle dibuat lebih lengkap agar ada data untuk:
  - assignment submitted
  - session started
  - session ended
  - task deliverables submitted
  - task hours verification
  - director approval
  - HR/payroll processing
  - payment distribution

## Tests dan Verifikasi

- Test Director Attendance module disesuaikan dengan perubahan route/view terbaru.
- Test `OvertimeReviewTableBuilder` disesuaikan dengan perubahan URL detail dan status table.
- Catatan ini hanya menambahkan dokumentasi. Tidak ada test yang dijalankan untuk perubahan dokumen ini.

## File Terkait

- `app/View/Composers/AttendanceProfileComposer.php`
- `app/View/Composers/ProjectManagementProfileComposer.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceController.php`
- `app/Http/Controllers/DirectorAttendance/DirectorAttendanceController.php`
- `app/Http/Controllers/DirectorAttendance/DirectorAttendanceOvertimeController.php`
- `resources/views/project_management/index.blade.php`
- `resources/views/project_management/layouts/profile-header.blade.php`
- `resources/views/project_management/layouts/profile-index.blade.php`
- `resources/views/project_management/layouts/profile-navbar.blade.php`
- `resources/views/project_management/task_list`
- `resources/views/admin_attendance/overtime/detail.blade.php`
- `resources/views/director_attendance/attendance/index.blade.php`
- `resources/views/director_attendance/attendance/detail-employees.blade.php`
- `resources/views/director_attendance/overtime/index.blade.php`
- `resources/views/director_attendance/overtime/detail.blade.php`
- `routes/web.php`
- `tests/Feature/DirectorAttendanceModuleTest.php`
- `tests/Unit/OvertimeReviewTableBuilderTest.php`
