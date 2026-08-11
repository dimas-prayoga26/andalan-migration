# Catatan Dev Log

Aturan log:
- Setiap perubahan dibuatkan file baru di `docs/dev-log/`.
- Nama file: `YYYY-MM-DD-jam-singkat-topik.md`.
- `docs/catatan-dev-log.md` dipakai sebagai ringkasan harian + index file detail.

## Ringkasan Harian

### 2026-08-11 (Selasa)
- Mengubah perhitungan attendance agar posisi `Driver` dan `Executive Assistant` tidak terkena pengurangan rest 1 jam.
- Pengecualian ini berdasarkan posisi employee, bukan berdasarkan `attendance_type = flexible`.
- Menyamakan hasil perhitungan pada clock-out, attendance exception, recap Admin, recap PIC, Staff Attendance Report, dan profile weekly worked hours.
- Detail: `docs/dev-log/2026-08-11-0909-driver-tanpa-pengurangan-rest-attendance.md`.

### 2026-08-06 (Kamis)
- Menambahkan `office_reset_time` pada `rules_of_attendaces` dengan default `00:00:00`, serta set awal `04:00:00` untuk rules yang mulai jam `09:00:00`.
- Mengubah reset attendance agar mengikuti alur lokasi aktif employee: `current_office_location_id` ke `office_locations`, lalu ke `rules_of_attendaces.office_reset_time`.
- Mengubah validasi clock-out agar mengikuti `office_end_time`, sehingga staff tidak bisa clock-out sebelum jam selesai kerja lokasi aktifnya.
- Menyamakan logic reset attendance dan clock-out guard pada menu Dashboard serta menu Attendance.
- Menambahkan notifikasi `Swal.fire` saat tombol Clock Out diklik sebelum waktunya, dengan fallback browser alert.
- Menambahkan `attendance_type` dan pivot rule-position agar satu office dapat memiliki rule fixed untuk staff biasa dan flexible untuk Driver.
- Mengubah Driver/flexible attendance menjadi open-session based: Clock In bebas, Clock Out bebas termasuk setelah tengah malam, dan Clock In terkunci selama sesi belum clock-out.
- Memperbaiki dependency dan parsing `LegacySqlUserSeeder` agar data company, position, employee, dan assignment dari SQL legacy terbaca lengkap.
- Mengganti delete confirmation settings dari partial Bootstrap modal menjadi `Swal.fire` dengan fallback `window.confirm`.
- Detail: `docs/dev-log/2026-08-06-1438-ringkasan-perubahan-attendance-seeder-settings.md`.

### 2026-07-09 (Kamis)
- Menambahkan `Staff submitted` pada detail overtime PIC serta menghubungkan task overtime dengan `overtime_id`.
- Membuat overtime PIC otomatis membuat `project_tasks` untuk staff yang di-assign dengan status `pending`, priority `high`, dan assigned by PIC.
- Menambahkan modal detail task PIC, menjaga `+ Add Task` tetap aktif setelah clock-out/cancelled, dan membatasi delete untuk task overtime.
- Menyesuaikan Attendance: verifikasi otomatis `Please wait`, tombol clock-in/out aktif setelah `Verification successful`, timezone `Asia/Jakarta`, late mulai `08:01`, serta format late jam-menit.
- Merapikan Attendance Recap, Admin Attendance Overview, Timesheet & Reporting icons, dan limit `Today's Early Birds`/`Today's Running Late` maksimal 5.
- Mengubah `#project-grid-pane` menjadi kanban statis, menyeragamkan ukuran card, mengaktifkan drag and drop, serta memperbaiki dropzone/mirror drag agar perpindahan card lebih natural.
- Detail: `docs/dev-log/2026-07-09-1530-ringkasan-perubahan-overtime-attendance-project-task.md`.

### 2026-07-08 (Rabu)
- Menyesuaikan hitungan cuti bersama 2026 agar memakai tanggal sistem dan diterapkan pada Staff, Admin, serta PIC Leave.
- Merapikan Leave Summary: tombol request time off, ikon Eligibility/Tracker, week start Senin, dan margin bawah kartu.
- Mengubah field Business Trip booking agar kondisional berdasarkan opsi booking/self managed.
- Memperbaiki Attendance Overview: `Attendance Rate`, `On Time Rate`, `Lateness Rate`, dan `Days Worked` memakai logika dinamis sesuai kalender kerja.
- Mengubah summary card Overtime menjadi slider satu baris, memperbarui ikon/warna, dan menahan `Estimated Extra Earnings` sementara di `Rp 0`.
- Menambahkan approved time pada overtime serta mengubah flow PIC verification agar jam final tersimpan di `approved_start_time`/`approved_end_time`.
- Mengatur `Completed & Locked` dan filter `Completed` overtime agar berbasis lifecycle `task_hours_verification = verified`.
- Menambahkan toleransi clock-in/clock-out overtime 30 menit dari jadwal planned.
- Menampilkan planned time/duration sebagai strikethrough dan approved time/duration sebagai nilai normal pada detail overtime completed.
- Detail: `docs/dev-log/2026-07-08-1515-ringkasan-perubahan-attendance-leave-business-trip-overtime.md`.

### 2026-07-07 (Selasa)
- Merombak office location menjadi global location yang tidak lagi terikat company.
- Mengalihkan geofencing attendance agar membaca `employee_deployments.current_office_location_id`.
- Menyesuaikan attendance rule, seeder, Data Employee, Admin Attendance, dan PIC Attendance dengan konsep lokasi kerja per employee.
- Menyamakan Attendance Confirmation pada Dashboard dengan menu Attendance: lokasi di luar radius tidak memblokir Clock In/Clock Out setelah GPS dan Telegram valid.
- Merapikan scope staff aktif, hide super admin, urutan company/PIC/nama, profile default, dan tampilan attendance logs harian dengan fallback `-`.
- Detail: `docs/dev-log/2026-07-07-0930-ringkasan-perubahan-office-location-attendance-employee.md`.

### 2026-07-06 (Senin)
- Menambahkan dropdown Branch / Office pada create/update Data Employee dan menyinkronkannya dengan office location untuk geofencing.
- Menyelaraskan 18 staff aktif hasil import legacy dengan company, branch/workplace, office location, deployment, dan position terbaru.
- Menonaktifkan akun legacy yang tidak lagi termasuk daftar staff terbaru beserta assignment PIC aktifnya.
- Memperbaiki cakupan PIC Overtime agar assignment supervisor dapat berlaku lintas company.
- Merapikan email profile dan chart Attendance serta Project Management pada tampilan mobile.
- Membuat attachment Sick Leave opsional dan menghapus konflik handler yang menyebabkan file picker diproses dua kali.
- Mencatat bahwa branch saat ini memakai `employee_deployments.workplace`; form Branch terpisah pada create/update employee belum diimplementasikan.
- Detail: `docs/dev-log/2026-07-06-1018-sinkronisasi-staff-pic-chart-dan-leave.md`.
- Detail: `docs/dev-log/2026-07-06-1047-data-employee-branch-office.md`.
- Detail: `docs/dev-log/2026-07-06-1050-fix-parse-error-branch-office.md`.
- Detail: `docs/dev-log/2026-07-06-1053-normalisasi-label-office-ke-kota.md`.
- Detail: `docs/dev-log/2026-07-06-1105-admin-attendance-overview-18-staff.md`.

### 2026-07-02 (Kamis)
- Menyempurnakan Data Employee dan Authorization dengan pencarian employee, pagination server-side, scope akses administrator/COO, serta assignment permission berbasis position.
- Menambahkan menu Task pada PIC Attendance untuk melihat task staff berdasarkan assignment PIC aktif.
- Merapikan multiple position, assignment PIC, akun RNB tambahan, import user legacy, foto profil employee, dan header profile aplikasi.
- Menyesuaikan geofencing agar memakai office location employee serta menambahkan titik kantor RNB Jakarta beserta rules attendance-nya.
- Menambahkan branding logo berdasarkan host/domain untuk aplikasi dan halaman login.
- Merapikan Dashboard, Attendance Overview, Overtime, dan Project Management agar lebih responsif pada mobile.
- Memperbaiki denominator Working Days bulanan pada Admin dan PIC Attendance Recap.
- Menambahkan service accrual saldo cuti tahunan, pengurangan kuota berdasarkan cuti bersama, validasi masa kerja, dan scheduler sinkronisasi bulanan.
- Mengganti README bawaan Laravel dengan dokumentasi setup project dan menambah test untuk seluruh area terkait.
- Detail: `docs/dev-log/2026-07-02-1545-ringkasan-perubahan-harian.md`.

### 2026-07-01 (Rabu)
- Mengubah `+ Add Overtime` PIC agar tidak otomatis membuat data `project_tasks`; staff yang di-assign wajib membuat task lemburnya sendiri.
- Membatasi `+ Add Task` staff hanya setelah `Overtime Clock In` dan sebelum `Overtime Clock Out`, termasuk validasi backend.
- Membuat window `Overtime Clock In` mengikuti jadwal PIC dari `planned_start_time` sampai `planned_end_time`, tanpa toleransi clock-in lebih awal.
- Menyeragamkan urutan lifecycle overtime agar `task_deliverables_submitted` step 3 dan `session_ended` step 4, sehingga tidak bentrok unique key lifecycle log.
- Menahan fase review/approval tetap `waiting` selama sesi berjalan; `task_hours_verification` baru menjadi `pending` setelah staff clock-out.
- Mengganti copy "task sudah disubmit" menjadi "task sudah dikerjakan".
- Detail: `docs/dev-log/2026-07-01-0126-perbaikan-flow-overtime-task-clock-in.md`.

### 2026-06-30 (Selasa)
- Menghapus schema akhir `user_documents` karena data identitas user sudah dipusatkan di `employee_identities`.
- Menghapus migration create/drop `user_documents` agar tabel tersebut tidak dibuat pada `migrate:fresh --seed`.
- Menghapus dependency seeder terhadap `user_documents` pada `UserSeeder`, `LegacySqlUserSeeder`, `NiskalaMultiPicLeaveSeeder`, dan `EmployeeIdentitySeeder`.
- Mengarahkan seed dokumen demo ke `employee_identities`, termasuk NIK, KK, NPWP, BPJS Ketenagakerjaan, BPJS Kesehatan, dan PTKP.
- Menambahkan dukungan multiple position pada `employee_deployments` melalui pivot `employee_deployment_positions` dengan `current_position_id` tetap sebagai primary position.
- Menyesuaikan permission route/sidebar, form Data Employee, detail employee, profile composer, grouping Leave Admin/PIC, dan seeder agar membaca posisi aktif tambahan.
- Menambahkan additional position untuk Erlin, Mevia, dan Leonie pada seeder serta database lokal aktif.
- Menambahkan posisi Administrator dan Super Administrator, akses COO setara Director, unique index posisi langsung di migration utama, serta explicit PIC assignment untuk Leonie, Syafiq, Rexy, dan Fahmil.
- Menghapus pemakaian `System Administrator`; admin biasa memakai `Administrator`, sedangkan akses tertinggi memakai `Super Administrator`.
- Detail: `docs/dev-log/2026-06-30-1836-hapus-user-documents-gunakan-employee-identities.md`.
- Detail: `docs/dev-log/2026-06-30-1853-multiple-position-employee-deployment.md`.
- Detail: `docs/dev-log/2026-06-30-1902-tambah-additional-position-erlin-mevia-leonie.md`.
- Detail: `docs/dev-log/2026-06-30-1936-administrator-superadmin-coo-pic-assignment.md`.
- Detail: `docs/dev-log/2026-06-30-2158-ganti-system-administrator-ke-administrator.md`.

### 2026-06-29 (Senin)
- Mengaktifkan menu Project Management Task List berbasis data `project_tasks`, menggantikan konten dummy template.
- Memisahkan controller Task List ke `ProjectManagement\TaskListController`, sehingga tidak lagi menumpuk di controller Overview.
- Menambahkan endpoint AJAX filter Task List agar perubahan periode, create/update, mark done, dan delete dapat memperbarui list tanpa reload penuh.
- Memisahkan render Task List menjadi partial untuk list task, week plan, dan project grid.
- Memisahkan filter Task menjadi Month dan Year, serta menambahkan sinkronisasi dengan kalender kanan.
- Mengaktifkan kalender kanan sebagai filter bulan/tahun dan memperbaiki highlight agar hanya satu tanggal aktif, dengan default tanggal hari ini saat reload.
- Mengganti date picker modal Task List ke `bootstrap-datetimepicker` agar bisa ditutup saat klik field lain tanpa wajib memilih tanggal.
- Mengatur date picker modal Task List agar muncul ke arah atas dekat field Date/Due Date.
- Mengembalikan dropdown titik tiga memakai SVG inline agar tetap tampil walau icon font gagal render.
- Mempertahankan alert `Pending Tasks Reminder!` dan `Ready for a Great Day?` sebagai reminder backlog dan CTA tambah task.
- Membuat menu Project menampilkan card project dinamis dari project yang diikuti staff login.
- Membuat detail project dinamis by project id, termasuk summary, department card dari `employee_deployments.current_department_id`, dan task yang hanya berasal dari project tersebut.
- Menambahkan `Live Event Dates` terpisah dari `Project Lifecycle` pada detail project melalui kolom `live_event_start_date` dan `live_event_end_date`.
- Mengubah tampilan Team pada detail project menjadi avatar bertumpuk dengan fallback angka dari nama/username staff.
- Membatasi checkbox/action task detail project hanya untuk department staff login, sementara department lain tetap view-only.
- Merapikan style detail project: jarak antar card, ring summary, department scope, dan alignment row task department.
- Mengganti card `Department Scope` menjadi `Tasks Over Time` dan membuat `Tasks Summary` memakai donut + legend department.
- Menambahkan seeder project lintas company `GROUP-COLLAB-2026` yang melibatkan 4 company (AndalanKu, KMA, RNB, Niskala) dan 5 department category; Marketing and Promotion, Information and Communications Technology, dan Project Planning and Development masing-masing memiliki 3 staff member.
- Menyesuaikan seeder project lintas company menjadi `Muktamar ke VI PKB 2024` dengan lokasi `Bali Nusa Dua Convention Center, Badung, Bali`, serta menambahkan action `+ Add Task`, update, dan delete task pada detail project untuk department staff login.
- Mengganti badge `Your Department` pada card department detail project menjadi tombol `Drive`.
- Menyamakan modal `+ Add Task` pada detail project dengan modal `Create New Task` di Task List, serta menghapus field Assignee karena task otomatis assigned ke staff login.
- Mengganti date picker modal detail project ke `bootstrap-datetimepicker` ter-scope agar klik field lain tidak memunculkan kalender.
- Menambahkan/memperbarui test struktur Task List, route, controller, partial, AJAX, dan calendar behavior.
- Detail: `docs/dev-log/2026-06-29-0142-project-management-task-list-ajax-filter-calendar.md`.
- Detail: `docs/dev-log/2026-06-29-0347-project-management-project-card-detail-dinamis.md`.

### 2026-06-25 (Kamis)
- Menambahkan modul PIC Attendance dengan menu Attendance, Leave, dan Overtime untuk supervisor/PIC.
- Menambahkan modul Director Attendance dengan menu Attendance dan Overtime.
- Menambahkan permission `view-pic-attendance` dan `view-director-attendance` agar menu sidebar mengikuti position.
- Menyesuaikan summary Overtime agar pending, SPV ACC, Director ACC, total hours, median, average, top overtime, dan weekend/weekday hours berbasis data database.
- Menyesuaikan tabel Pending dan Approved Overtime untuk Admin, PIC, dan Director dengan rules lifecycle yang berbeda per role.
- Menambahkan filter bulan dan tahun pada halaman Overtime Admin, PIC, dan Director.
- Menyesuaikan seeder Overtime dan `overtime_lifecycle_logs` agar memiliki variasi status sampai Payment Distribution.
- Detail: `docs/dev-log/2026-06-25-0427-pic-attendance-supervisor-approval-module.md`.
- Detail: `docs/dev-log/2026-06-25-1252-ringkasan-admin-pic-director-attendance-overtime.md`.

### 2026-06-15 (Senin)
- Menyelesaikan flow detail overtime dan project task: create, update, delete task, daily task `project_id = null`, dan project task berdasarkan membership staff.
- Menambahkan dukungan `blockers` dan `attachment_path` pada task, termasuk form create/update dan seeder.
- Menyesuaikan kartu dan modal overtime agar memakai actual start/end, duration aktual, jam berjalan, serta sinkronisasi lifecycle log saat clock-in/clock-out.
- Merapikan approval trail dengan badge `Pending` dan approver director dari role `board_of_rector`.
- Mengganti error notification flow overtime/task menjadi SweetAlert2 dan memastikan menu Overtime aktif pada route detail.
- Detail: `docs/dev-log/2026-06-15-0730-ringkasan-final-overtime-project-task.md`.

### 2026-06-12 (Jumat)
- Menyambungkan overtime list/detail ke data database, lifecycle log, dan project task.
- Mengubah route detail overtime dari query string `/attendance/overtimes/detail?id={id}` menjadi path `/attendance/overtimes/{id}`.
- Membuat card Overtime List menampilkan `instruction`, progress `Complete`, persentase dari lifecycle log, dan footer status `Pending`.
- Menyesuaikan panel `My Task Items` di detail overtime: pending dan finished task berasal dari `project_tasks`, tombol edit/delete tetap fixed, keterangan staff dan badge completed dihapus.
- Menambahkan seeder project RNB untuk `staff31` sampai `staff34`, masing-masing 5 project task.
- Menambahkan seeder overtime deadline: masing-masing staff punya 1 task pending deadline H+1 dan 3 completed task untuk histori.
- Menghapus `created_by` dan `department_id` dari `project_tasks`; department task sekarang dibaca dari `employee_deployments.current_department_id`.
- Menambahkan mapping deployment department/position RNB untuk `staff31` sampai `staff34` di `UserSeeder`.
- Menambahkan dan menyesuaikan test relasi, route, seeder, dan tampilan overtime.
- Detail: `docs/dev-log/2026-06-12-1558-ringkasan-perubahan-overtime-project-task.md`.

### 2026-06-11 (Kamis)
- Menambahkan fondasi project-task untuk overtime: tabel/model `projects`, `project_members`, `project_sections`, dan `project_tasks`.
- Menambahkan relasi project ke company, member, section, task, employee, user/PIC, dan overtime.
- Mengubah desain overtime agar 1 overtime dapat memiliki banyak task melalui `project_tasks.overtime_id` nullable.
- Menambahkan `project_sections` agar task dalam 1 project dapat dikelompokkan per kategori/section.
- Menghapus kolom `role` dari `project_members` karena membership cukup mencatat staff yang mengikuti project.
- Mengubah lifecycle status overtime menjadi `assigned`, `in_progress`, `completed`, dan `cancelled`.
- Merapikan migration agar perubahan status overtime dan relasi task-overtime berada langsung pada migration utama.
- Menambahkan test relasi/migration `ProjectOvertimeRelationTest`.
- Detail: `docs/dev-log/2026-06-11-1456-overtime-project-task-foundation.md`.

### 2026-06-10 (Rabu)
- Mengubah link card Overtime agar membuka view detail Blade melalui route `attendance.overtimes.detail`.
- Membuat style export Excel Attendance Report menjadi polos tanpa background warna.
- Menghapus kolom `Attachment` dari hasil export Excel Attendance Report.
- Mengubah label tombol export Attendance Report menjadi `Export Report` dengan icon Excel.
- Menambahkan pengkondisian tombol `Submit Task` dan `Reimbursement` pada modal Business Trip calendar berdasarkan lifecycle log.
- Menambahkan skenario Business Trip approved kedua untuk `staff31` sampai lifecycle `supervisor_review` complete.
- Mengubah seeder Leave Request agar tanggal demo jatuh pada hari kerja dan tidak bertumpuk dengan weekend/holiday.
- Membuat modal label calendar Leave/Business Trip terbuka dan terisi dinamis dari event yang diklik.
- Menyamakan ukuran visual label Leave/Business Trip calendar dengan label calendar lainnya.
- Mengubah filter label calendar: Leave tampil untuk `pending`/`approved`, Business Trip hanya untuk `approved`.
- Mengubah seed Leave Request RNB untuk empat tipe cuti menjadi status `pending`.
- Mengubah seeder Leave Request agar membuat satu approved request untuk SPECIAL, ANNUAL, SICK, dan UNPAID.
- Menambahkan label calendar untuk Leave Request dan Business Trip dengan warna sesuai tipe.
- Mengubah label modal Attendance Exception agar memilih salah satu berdasarkan `attendance_exceptions.type`, bukan gabungan dua tipe.
- Mengubah label Request Type modal Attendance Exception menjadi `Permitted Late Arrival | Early Departure`.
- Mengubah title default modal Attendance Exception menjadi `Permitted Late Arrival / Early Departure`.
- Mengembalikan header dan copy pembuka modal Attendance Exception secara dinamis dari event calendar.
- Menghapus title statis lama dari modal Attendance Exception agar default-nya netral dan tetap diisi dinamis.
- Menyederhanakan modal Attendance Exception agar hanya menampilkan Request Type, Reason, Time Variance, dan Status.
- Menghapus copy statis pada modal Attendance Exception dan menambahkan detail date, location, clock in, serta clock out dinamis.
- Mengubah fallback clock out event calendar Attendance menjadi `-`.
- Mengembalikan label calendar Attendance ke format jam masuk-pulang dan menghapus link titik lokasi dari modal.
- Menambahkan runbook Attendance Report, calendar modal, dan lokasi absensi setelah rangkaian perubahan.
- Memecah plus code Attendance Log untuk mengisi kolom address component dengan fallback dari Google address components.
- Mengubah `attendance_logs.location` agar menyimpan plus code dari Google Geocoding API dengan fallback URL maps lat/long.
- Mengubah penyimpanan `attendance_logs.location` agar memakai URL Google Maps dari lat/long absensi.
- Mengubah bukti lokasi modal calendar Attendance agar memakai link Google Maps dari lat/long absen.
- Mengembalikan sumber lokasi modal calendar Attendance ke kolom `location` dengan sanitasi kode pos dan `Indonesia`.
- Mengubah sumber lokasi modal calendar Attendance agar hanya memakai kolom address geocoding tanpa kolom `location`.
- Membuat modal calendar Attendance `onTime`, `late`, dan `deviation` terisi dinamis dari event yang diklik.
- Menyesuaikan calendar Attendance agar event on-time, late, dan attendance exception membuka modal `onTime`, `late`, dan `deviation`.
- Menyesuaikan kolom `Note` Attendance Report agar attendance exception `late_arrival` dan `early_departure` tampil sebagai label izin dengan durasi.
- Membersihkan sisa kode `variance`/`notes` legacy di `ReportController` setelah kolom report disederhanakan.
- Menghapus kolom `Note` terpisah pada table Attendance Report dan mengganti label `Variance` menjadi `Note`.
- Mengubah export Attendance Report menjadi `.xlsx` valid dan title dinamis `company - staff`.
- Menghapus view PDF lama pada Attendance Report setelah export dipindahkan ke Excel.

### 2026-06-09 (Selasa)
- Mengubah Attendance Report agar memiliki kolom `Note` dan `Attachment`, serta mengganti export PDF menjadi Excel.
- Merapikan final Leave List: ikon sesuai tipe cuti, modal detail sick/non-sick, fallback Medical Notes, format periode singkat, dan dropdown `View` yang membuka modal detail tanpa memicu klik card.
- Mengubah modal detail Leave List agar non-sick memakai format Out of Office dan Sick Leave memakai format Attendance Sick dengan preview Medical Notes.
- Menghapus partial `history-cards` dan `request-cards`; filter AJAX Leave List sekarang mengirim data `cards` dan dirender langsung dari `index.blade.php`.
- Memindahkan balance analytic ke partial `history-list-cards` dan memindahkan fragment AJAX Leave List ke partial `request-cards`.
- Memindahkan isi balance card analytic ke partial `history-cards` dan memisahkan fragment AJAX Leave List ke partial khusus.
- Memindahkan partial Leave Request agar row analytic `leave-balance-mobile-slider` yang terpisah, sementara render awal Leave List kembali inline di index.

### 2026-06-05 (Jumat)
- Memindahkan modal detail Leave Request dari card analytic ke card Leave List/history pengajuan time off.
- Memperluas trigger modal detail Annual Leave dan Sick Leave agar seluruh area card bisa diklik.
- Menambahkan modal detail Annual Leave dan Sick Leave yang terbuka dari kartu summary leave.
- Mengubah hitungan kartu summary Business Trip agar berbasis lifecycle, tanggal trip, cash advance, dan reimbursement.
- Mengubah nominal `Trip Incentive` dan `Total Payment` pada Business Trip menjadi hijau.
- Mengubah attachment Business Trip agar preview tampil di modal, bukan membuka tab baru.
- Mengubah lifecycle seed Business Trip `staff32` agar step 3 `Cash Advance Submitted` berstatus pending.
- Mengubah lifecycle seed Business Trip `staff31` agar berhenti di step 2 `Supervisor Review` dengan status pending.
- Membatasi list dan detail Business Trip untuk role staff agar hanya menampilkan trip milik staff login.
- Menambahkan seeder Business Trip untuk 4 staff RNB dengan variasi lifecycle sampai step 1, step 3, step 6, dan step 7.
- Menambahkan 4 akun staff aktif untuk company RNB melalui `UserSeeder`.
- Merapikan detail Business Trip agar lifecycle tracker, cash advance, expense, summary pembayaran, dan status memakai data database.
- Mengaktifkan create/update cash advance staff, termasuk date range, amount realized, attachment report per item, dan update lifecycle saat submit.
- Menghapus konsep `business_trip_expense_items` karena tidak dipakai pada flow saat ini.
- Menambahkan command + scheduler cron untuk sinkronisasi otomatis lifecycle trip execution/trip report berdasarkan tanggal dinas, termasuk actor step 5 dari user staff.
- Menambahkan modal attachment pada Expense dan kalkulasi financial summary dari cash advance approved, reimbursement, serta incentive.
- Mengaktifkan form reimbursement staff untuk create/update data dan upload receipt, dengan tombol Reimbursement aktif setelah step 7 pending.

### 2026-06-03 (Rabu)
- Merapikan struktur route/view Attendance ke nama Inggris dan memindahkan daily attendance ke `/attendance/today`.
- Mengaktifkan flow awal Business Trip: index dinamis, create/store, detail by id, link cash advance/reimbursement, serta proxy province/city dari `wilayah.id`.
- Menambahkan struktur tabel/model/controller pendukung Business Trip untuk expense items, cash advances, reimbursements, dan lifecycle logs.
- Memperbarui Leave Request: summary dipisah menjadi Eligibility dan Tracker, card mobile slider, `Unpaid Leave`, filter Leave List via AJAX, serta refresh card otomatis setelah `Request Time Off`.

### 2026-06-02 (Selasa)
- Mengubah sumber data hari libur pada Attendance Report dari API eksternal `libur.deno.dev` menjadi tabel lokal `attendances_holidays`.
- Menambahkan test terfokus untuk memastikan pemetaan libur nasional dan cuti bersama berasal dari database tanpa HTTP request eksternal.

### 2026-05-29 (Jumat)
- Penyelesaian modul `Leaves & Sick`: halaman izin memakai data database, form pengajuan aktif, validasi cuti khusus dan sakit ditambahkan, serta riwayat pengajuan dapat difilter berdasarkan tahun.
- Penyederhanaan konfigurasi cuti: hapus `meta_data_leave_companies` dan flag lama `is_monthly_limit_used`, tambah role `admin`, otorisasi hapus pengajuan, serta seeder histori cuti.
- Perbaikan horizontal scroll pada tabel report absensi.
- Penambahan kalender hari libur berbasis tabel `attendances_holidays`, termasuk seeder hari libur 2026 dan warna khusus untuk attendance exception.
- Statistik staff pada profile header dibuat dinamis dari database: `Late In`, `Leaves & Sick`, persentase kehadiran mingguan, `On-Time`, serta grafik progress bulanan.

### 2026-05-21 (Kamis)
- Penambahan halaman `Report` pada modul absensi beserta route dan controller terpisah.
- Revamp modal absensi: state `Clock In`/`Clock Out` dipisah, alur verifikasi onsite diperjelas, dan status tombol submit lebih ketat.
- Perubahan skema attendance: `distance` dipecah jadi `distance_in`/`distance_out` dan `late_grace_minutes` dihapus dari rules.
- Perbaikan logic backend: hitung `late_minutes`, hitung `work_hours` lebih akurat, serta update `location_out`/`distance_out` saat clock out.
- Perbaikan seeder agar kompatibel dengan skema baru.

### 2026-05-13 (Rabu)
- Perbaikan seeder pasca penghapusan tabel `meta_data_divisions` dan `meta_data_positions`.
- `DatabaseSeeder` dan `UserSeeder` disesuaikan ke `departments` + `positions` (UUID).
- Sinkronisasi `UserSeeder` dengan struktur `users` terbaru yang tidak lagi memakai kolom `name`.
- Tambah proteksi `try-catch` untuk proses controller (middleware global) dan seluruh `run()` seeder.

### 2026-05-12 (Selasa)
- Refactor UUID lanjutan: generator custom diubah ke format UUID style (`8-4-4-4-12`) dengan obfuscation.
- Tabel `companies` dipindah ke UUID dan seluruh foreign key terkait disesuaikan.
- Struktur `users` diselaraskan agar relasi perusahaan pakai `company_id` (`foreignUuid`) dan tanpa kolom `uid`.
- Tabel `rules_of_attendaces` dipindah ke UUID.
- Tabel `overtimes` disesuaikan agar `assigned_by` relasi ke `users.id` dan sinkron dengan model/controller.

### 2026-05-11 (Senin)
- Rapihin dan ubah flow modal absensi: hapus menu/tab yang tidak dipakai, title dinamis tanggal hari ini, spinner untuk loading IP, dan tombol detail pakai `onclick`.
- Ubah alur setelah absen sukses: redirect ke halaman project management dan perbaiki route error yang sempat terjadi.
- Mulai refactor UUID custom (`ddmmyyyy + urutan`) untuk model utama: `Role`, `Permission`, `User`, `Attendance`.
- Sinkronkan sebagian migration/FK dan seeder agar kompatibel dengan UUID custom.
- Buat checklist lanjutan area yang masih perlu dirombak.

### 2026-05-08 (Jumat)
- Update besar fitur absensi: detail absensi, geocoding lokasi, dan tampilan peta pada modal detail.
- Revamp aturan absensi (status, jam kerja, grace period) termasuk migration pendukung.
- Perbaikan modul lembur (controller + view).
- Tambah otomasi sinkronisasi saldo cuti bulanan via command + scheduler.

## File Detail Entry

### 2026-07-08
- `2026-07-08-1515-ringkasan-perubahan-attendance-leave-business-trip-overtime.md`

### 2026-07-07
- `2026-07-07-0930-ringkasan-perubahan-office-location-attendance-employee.md`

### 2026-07-06
- `2026-07-06-1018-sinkronisasi-staff-pic-chart-dan-leave.md`

### 2026-07-02
- `2026-07-02-1545-ringkasan-perubahan-harian.md`

### 2026-07-01
- `2026-07-01-0126-perbaikan-flow-overtime-task-clock-in.md`

### 2026-06-30
- `2026-06-30-1836-hapus-user-documents-gunakan-employee-identities.md`
- `2026-06-30-1853-multiple-position-employee-deployment.md`
- `2026-06-30-1902-tambah-additional-position-erlin-mevia-leonie.md`
- `2026-06-30-1936-administrator-superadmin-coo-pic-assignment.md`
- `2026-06-30-2158-ganti-system-administrator-ke-administrator.md`

### 2026-06-29
- `2026-06-29-0142-project-management-task-list-ajax-filter-calendar.md`
- `2026-06-29-0347-project-management-project-card-detail-dinamis.md`

### 2026-06-10
- `2026-06-10-1514-dynamic-calendar-label-modals.md`
- `2026-06-10-1449-uniform-calendar-label-size.md`
- `2026-06-10-1435-calendar-label-status-filter.md`
- `2026-06-10-1424-pending-leave-request-seeds.md`
- `2026-06-10-1421-seed-four-leave-types.md`
- `2026-06-10-1412-calendar-leave-business-trip-labels.md`
- `2026-06-10-1347-type-specific-deviation-labels.md`
- `2026-06-10-1158-deviation-request-type-label.md`
- `2026-06-10-1152-deviation-modal-title.md`
- `2026-06-10-1148-dynamic-deviation-modal-intro.md`
- `2026-06-10-1140-remove-static-deviation-title.md`
- `2026-06-10-1137-simplify-deviation-modal-fields.md`
- `2026-06-10-1134-dynamic-deviation-modal-details.md`
- `2026-06-10-1119-clock-out-fallback-strip.md`
- `2026-06-10-1115-calendar-label-remove-location-link.md`
- `2026-06-10-1111-docs-attendance-report-calendar-location.md`
- `2026-06-10-1059-parse-plus-code-address-components.md`
- `2026-06-10-1052-attendance-log-location-plus-code.md`
- `2026-06-10-1045-attendance-log-location-coordinate-url.md`
- `2026-06-10-1040-calendar-modal-location-by-latlong.md`
- `2026-06-10-0952-calendar-modal-location-sanitized.md`
- `2026-06-10-0948-calendar-modal-location-address-fields.md`
- `2026-06-10-0939-dynamic-attendance-calendar-modals.md`
- `2026-06-10-0929-calendar-attendance-modal-routing.md`
- `2026-06-10-0853-report-note-attendance-exception-label.md`
- `2026-06-10-0849-cleanup-report-controller-variance-notes.md`
- `2026-06-10-0844-report-table-note-gantikan-variance.md`
- `2026-06-10-1715-export-attendance-report-xlsx-title-dinamis.md`
- `2026-06-10-1649-hapus-view-pdf-export-attendance-report.md`

### 2026-06-09
- `2026-06-09-1647-report-attendance-note-attachment-export-excel.md`
- `2026-06-09-1420-rangkuman-final-leave-history-card-modal-dropdown.md`
- `2026-06-09-1055-modal-detail-leave-history-sick-non-sick.md`
- `2026-06-09-1038-hapus-partial-leave-history-request-cards.md`
- `2026-06-09-1028-pindah-balance-ke-history-list-cards.md`
- `2026-06-09-1015-pindah-balance-card-ke-history-cards-partial.md`
- `2026-06-09-0945-pindah-partial-leave-balance-dan-inline-leave-list.md`

### 2026-06-05
- `2026-06-05-1642-pindah-modal-detail-leave-ke-card-history.md`
- `2026-06-05-1631-trigger-modal-leave-summary-di-seluruh-card.md`
- `2026-06-05-1624-modal-detail-annual-sick-leave-dari-card.md`
- `2026-06-05-1604-hitungan-summary-business-trip-berbasis-lifecycle.md`
- `2026-06-05-1550-trip-incentive-total-payment-hijau.md`
- `2026-06-05-1542-preview-attachment-business-trip-di-modal.md`
- `2026-06-05-1532-staff32-cash-advance-submitted-pending.md`
- `2026-06-05-1526-staff31-business-trip-supervisor-review-pending.md`
- `2026-06-05-1510-scope-business-trip-list-untuk-staff-login.md`
- `2026-06-05-1445-seeder-business-trip-rnb-staff-lifecycle.md`
- `2026-06-05-1415-tambah-empat-staff-rnb-di-user-seeder.md`
- `2026-06-05-1308-ringkasan-perubahan-business-trip-detail-cash-advance-lifecycle.md`

### 2026-06-03
- `2026-06-03-1701-ringkasan-perubahan-attendance-business-trip-leave.md`

### 2026-06-02
- `2026-06-02-1005-report-holidays-gunakan-table-lokal.md`

### 2026-05-29
- `2026-05-29-1704-ringkasan-perubahan-leaves-holidays-profile-stats.md`

### 2026-05-21
- `2026-05-21-1015-revamp-absensi-report-modal-dan-skema-attendance.md`

### 2026-05-13
- `2026-05-13-0110-fix-seeder-metadata-ke-departments-positions.md`
- `2026-05-13-0235-try-catch-controller-seeder.md`

### 2026-05-12
- `2026-05-12-0617-refactor-uuid-company-users-overtimes.md`
- `2026-05-12-1321-fix-pagination-style-tablelogs.md`
- `2026-05-12-1329-hapus-section-table-di-index-absensi.md`
- `2026-05-12-1333-nonaktifkan-js-datatable-index-absensi.md`
- `2026-05-12-1345-pindah-init-tablelogs-dari-profile-ke-index-absensi.md`
- `2026-05-12-1348-login-sessions-ganti-export-ke-dropdown-viewall.md`
- `2026-05-12-1350-hapus-table-logs-dan-login-sessions-col12.md`
- `2026-05-12-1354-rename-login-sessions-jadi-logs-dropdown-2.md`
- `2026-05-12-1356-tambah-1-opsi-dropdown-logs.md`
- `2026-05-12-1358-ubah-view-all-jadi-dropdown-kedua.md`
- `2026-05-12-1400-kembalikan-button-setelah-dua-dropdown-logs.md`
- `2026-05-12-1406-ubah-kolom-table-logs-jadi-no-namastaff-masuk-pulang-namapt-action.md`
- `2026-05-12-1416-aktifkan-kembali-table-logs-dinamis-database.md`
- `2026-05-12-1420-rename-button-presensi-clock-in-ke-check-in.md`
- `2026-05-12-1423-aktifkan-button-presensi-check-in-buka-modal-absen.md`
- `2026-05-12-1427-pindah-button-masuk-keluar-ke-footer-modal.md`
- `2026-05-12-1430-button-masuk-keluar-dibikin-lebar.md`
- `2026-05-12-1442-aktifkan-navigasi-profile-absensi-dengan-route-laravel.md`
- `2026-05-12-1450-sesuaikan-menu-presensi-izin-cuti-lembur.md`
- `2026-05-12-1452-overview-dan-list-tetap-ditampilkan-normal.md`
- `2026-05-12-1520-avatar-index-dinamis-dari-user-profiles-fallback-default.md`
- `2026-05-12-1553-sesuaikan-attendance-logs-uuid-dan-order-controller.md`
- `2026-05-12-1608-create-migration-departments-positions-name-status.md`
- `2026-05-12-1625-fix-fk-employee-deployments-departments-positions.md`
- `2026-05-12-1636-positions-departments-pakai-uuid.md`
- `2026-05-12-1641-seed-default-departments-name-di-migration.md`
- `2026-05-12-1648-create-position-seeder-untuk-table-positions.md`
- `2026-05-12-1655-create-migration-employee-profiles.md`
- `2026-05-12-1659-create-migration-employee-identities.md`
- `2026-05-12-1704-fix-blueprint-unsigneddecimal-employee-profiles.md`

### 2026-05-11
- `2026-05-11-1244-ringkasan-harian-catatan-dev-log.md`
- `2026-05-11-1243-uuid-refactor-checklist-doc.md`
- `2026-05-11-1231-attendance-custom-uuid.md`
- `2026-05-11-1223-user-model-custom-uuid-generator.md`
- `2026-05-11-1156-custom-uuid-spatie-role-permission.md`
- `2026-05-11-1145-fix-seeder-roles-uuid-dan-user-seeder-uuid.md`
- `2026-05-11-1051-setup-user-uuid-dan-relasi-user-id.md`
- `2026-05-11-1008-detail-button-onclick-style.md`
- `2026-05-11-0944-hapus-badge-memuat-ip-modal-absensi.md`
- `2026-05-11-0940-spinner-ip-saat-loading-di-modal-absensi.md`
- `2026-05-11-0934-cleanup-script-redundan-index-absensi.md`
- `2026-05-11-0922-title-modal-absen-dinamis-tanggal-hari-ini.md`
- `2026-05-11-0919-hapus-menu-absen-onsite-dan-ubah-title-modal.md`
- `2026-05-11-0844-fix-route-redirect-project-management.md`
- `2026-05-11-0830-redirect-absen-ke-project-management.md`
- `2026-05-11-0823-remove-business-trip-modal-auto-refresh.md`
- `2026-05-11-1530-format-log-per-file.md`

### 2026-05-08
- `2026-05-08-1645-attendance-leave-geocoding.md`
