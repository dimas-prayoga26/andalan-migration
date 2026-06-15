# 2026-06-15 07:30 - Ringkasan Final Perubahan Overtime dan Project Task

## Ringkasan
- Menyelesaikan integrasi detail overtime dengan `project_tasks`, termasuk daily task, project task, create/update/delete task, dan tampilan task pada panel `My Task Items`.
- Menyesuaikan kartu `Overtime Confirmation` dan `End of Overtime` agar memakai jam aktual overtime, bukan nilai statis.
- Menyambungkan modal clock-in dan clock-out overtime ke penyimpanan data `overtimes` dan `overtime_lifecycle_logs`.
- Merapikan approval trail, sumber approver director dari role `board_of_rector`, serta status pending dalam bentuk badge.
- Mengganti notifikasi JavaScript native menjadi SweetAlert2 pada flow overtime/task.
- Menambahkan dokumentasi ini sebagai ringkasan lanjutan setelah dev-log `2026-06-12-1558-ringkasan-perubahan-overtime-project-task.md`.

## Perubahan Data dan Schema
- `project_tasks.project_id` dibuat nullable.
- `project_id = null` dipakai sebagai penanda daily task.
- Project task tetap memakai `project_id` berisi id project yang diikuti staff.
- `project_tasks.overtime_id` tetap nullable.
- Task dengan `overtime_id = null` tetap ditampilkan pada detail overtime staff sebagai task yang belum terikat sesi overtime.
- Ketika staff melakukan update task dari detail overtime, `overtime_id` diisi dengan id overtime yang sedang dibuka.
- `project_tasks` memiliki kolom:
  - `blockers` untuk mencatat hambatan task.
  - `attachment_path` untuk menyimpan link/path attachment sementara.
- `department_id` tidak lagi dipakai di `project_tasks`.
- Department task tidak disimpan di task, tetapi dibaca dari deployment employee bila dibutuhkan.

## Perubahan Model dan Relasi
- `ProjectTask` memakai relasi:
  - `project()`
  - `employee()`
  - `overtime()`
- Relasi task ke department dihapus dari desain task.
- `EmployeeDeployment::department()` dipakai untuk membaca department dari `current_department_id`.
- Project membership dipakai untuk menentukan daftar project yang boleh dipilih staff pada form task.

## Route Overtime Task
- Route create task:
  - `POST /attendance/overtimes/{attendanceOvertime}/tasks`
  - Name: `attendance.overtimes.tasks.store`
- Route update task:
  - `PUT /attendance/overtimes/{attendanceOvertime}/tasks/{projectTask}`
  - Name: `attendance.overtimes.tasks.update`
- Route delete task:
  - `DELETE /attendance/overtimes/{attendanceOvertime}/tasks/{projectTask}`
  - Name: `attendance.overtimes.tasks.destroy`
- Route detail overtime tetap memakai:
  - `GET /attendance/overtimes/{attendanceOvertime}`
  - Name: `attendance.overtimes.detail`

## Controller Overtime
- `AttendanceOvertimeController::detail()` mengirim data tambahan untuk detail overtime:
  - `taskProjectOptions`
  - `taskStoreUrl`
  - `taskDefaultDate`
- `storeTask()` menambahkan task baru dari modal `Create a Task`.
- `updateTask()` memperbarui task dari modal `Update a Task`.
- `destroyTask()` menghapus task dari tombol delete di `My Task Items`.
- `canUpdateOvertimeTask()` dipakai untuk memastikan task hanya bisa diubah/dihapus oleh user yang punya akses ke overtime terkait.
- `employeeIsProjectMember()` memastikan project yang dipilih memang project yang diikuti staff.
- `buildTaskProjectOptions()` membangun daftar project aktif berdasarkan `project_members`.
- `overtimeTaskItemValue()` sekarang mengirim data task lengkap ke Blade:
  - title, description, start date, due date, status, priority.
  - blockers, attachment path.
  - task category daily/project.
  - project id.
  - update URL dan delete URL.

## Form Create Task
- Form `Create a Task` sekarang dapat menyimpan data ke `project_tasks`.
- Input `Date` dan `Due Date` memakai date picker native (`type="date"`).
- `Attachment` masih berupa input teks untuk link/path.
- `Blockers` disimpan ke kolom `blockers`.
- `Task Category` menentukan nilai `project_id`:
  - `Daily Task`: `project_id = null`.
  - `Project Task`: wajib memilih project.
- Select `Project Name` hanya aktif saat kategori `Project Task`.
- Daftar project diambil dari `project_members` staff tersebut.
- Jika staff tidak punya project aktif, select menampilkan `Belum ada pilihan project tersedia`.

## Form Update Task
- Modal `Update a Task` sekarang membaca data task yang dipilih dari payload `My Task Items`.
- Field yang bisa diperbarui:
  - title.
  - description.
  - date dan due date.
  - priority/volume workload.
  - attachment path.
  - blockers.
  - task category.
  - project name.
  - status.
- Saat task diupdate dari detail overtime, `overtime_id` akan terisi dengan overtime yang sedang dibuka.
- Jika kategori diubah menjadi `Daily Task`, `project_id` dikosongkan.
- Jika kategori diubah menjadi `Project Task`, project divalidasi terhadap project membership staff.

## Delete Task
- Tombol delete pada `My Task Items` sudah berfungsi.
- Tombol delete membawa:
  - task id.
  - task title.
  - delete URL.
- Modal delete menampilkan nama task yang dipilih.
- Submit delete dikirim melalui AJAX dengan method `DELETE`.
- Jika delete berhasil, halaman detail overtime reload agar list task terbaru tampil.
- Jika delete gagal, pesan error ditampilkan lewat SweetAlert2.

## Panel My Task Items
- Task ditampilkan dari `project_tasks` berdasarkan `employee_id`.
- Task yang ditampilkan meliputi:
  - task dengan `overtime_id` sama dengan overtime yang sedang dibuka.
  - task dengan `overtime_id = null`.
- Pending task masuk ke bagian `Latest to do's`.
- Completed task masuk ke bagian `Latest finished to do's`.
- Tombol edit dan delete dibuat stabil di sisi kanan item.
- Teks task panjang dibuat truncate agar tombol tidak berantakan.
- Bagian department dihilangkan dari task item.

## Kartu Overtime Confirmation dan End of Overtime
- Nilai duration default menjadi `--:--` jika belum ada actual start dan actual end.
- Nilai start default menjadi `--:--` jika belum ada `actual_start_time`.
- Nilai ended default menjadi `--:--` jika belum ada `actual_end_time`.
- Nilai time diambil dari jam berjalan di timezone `Asia/Jakarta`.
- Jika sudah ada actual start dan actual end, duration dihitung dari selisih `actual_start_time` dan `actual_end_time`.
- Kartu `Overtime Confirmation` memakai start dari `actual_start_time`.
- Kartu `End of Overtime` memakai ended dari `actual_end_time`.

## Modal Overtime Confirmation
- Modal clock-in menampilkan:
  - tanggal dan jam berjalan.
  - scheduled start time.
  - scheduled end time.
  - target duration.
- Saat staff klik `Overtime Clock In`, data actual start disimpan ke `overtimes`.
- Setelah clock-in, lifecycle log overtime disinkronkan sehingga fase execution bisa berubah sesuai kondisi data.

## Modal Overtime Completed
- Modal clock-out menampilkan:
  - scheduled start time.
  - scheduled end time.
  - actual start time.
  - actual end time.
  - target duration.
  - actual duration.
- Sebelum staff end session:
  - actual end time tampil `-`.
  - actual duration tampil `-`.
- Tombol `End Overtime Session` aktif setelah ada actual start dan belum ada actual end.
- Saat staff klik `End Overtime Session`, data actual end disimpan ke `overtimes`.
- Setelah clock-out, `overtime_lifecycle_logs` disinkronkan kembali.

## Approval Trail
- Bagian `Approved by Director` mengambil user dari role `board_of_rector`.
- Nilai kosong atau strip pada approval trail ditampilkan sebagai badge `Pending`.
- `Verified by System`, `Approved by Supervisor`, dan `Approved by Director` tetap menampilkan actor jika tersedia.
- Jika approval belum selesai, statusnya tetap dibuat jelas dengan badge pending.

## Navigation
- `profile-navbar.blade.php` sudah memakai `request()->routeIs('attendance.overtimes*')`.
- Menu Overtime tetap aktif ketika user berada di halaman detail overtime.

## Notifikasi
- Notifikasi error pada flow overtime/task memakai SweetAlert2.
- `window.alert` dan toastr tidak dipakai pada detail overtime.
- SweetAlert2 asset dimuat dari:
  - `assets/vendor/sweetalert2/sweetalert2.min.css`
  - `assets/vendor/sweetalert2/sweetalert2.min.js`

## Seeder
- `ProjectTaskSeeder` menyiapkan project RNB dan task untuk staff RNB.
- Seeder mendukung daily task melalui `project_id = null`.
- Seeder mengisi `blockers` dan `attachment_path`.
- `OvertimeDeadlineTaskSeeder` mengisi task overtime dengan `blockers` dan `attachment_path`.
- Seeder tidak lagi mengisi `department_id` pada `project_tasks`.

## Test dan Verifikasi
- Test route dan naming memastikan route task store/update/destroy tersedia.
- Test overtime-project memastikan:
  - schema `project_tasks` memuat nullable project, blockers, dan attachment path.
  - controller memiliki store/update/delete task.
  - view detail overtime memuat form create/update/delete task.
  - SweetAlert2 dipakai untuk error message.
  - navbar overtime aktif pada route detail.
- Test seeder memastikan daily task, blockers, attachment path, dan hilangnya department task tetap terjaga.
- Verifikasi terakhir yang sudah dijalankan:
  - `php -l app/Http/Controllers/AttendanceOvertimeController.php`
  - `php -l routes/web.php`
  - `php artisan route:list --name=attendance.overtimes.tasks --except-vendor`
  - `vendor\bin\pint --dirty --format agent`
  - `php artisan test --compact tests\Feature\AttendanceNamingConventionTest.php`
  - `php artisan test --compact tests\Feature\ProjectOvertimeRelationTest.php`

## File Utama Terdampak
- `app/Http/Controllers/AttendanceOvertimeController.php`
- `app/Models/ProjectTask.php`
- `app/Models/Department.php`
- `app/Models/EmployeeDeployment.php`
- `database/migrations/2026_06_11_042442_create_project_tasks_table.php`
- `database/migrations/2026_06_12_092359_make_project_tasks_project_id_nullable.php`
- `database/seeders/ProjectTaskSeeder.php`
- `database/seeders/OvertimeDeadlineTaskSeeder.php`
- `resources/views/attendance/layouts/profile-navbar.blade.php`
- `resources/views/attendance/overtimes/detail.blade.php`
- `resources/views/attendance/overtimes/index.blade.php`
- `routes/web.php`
- `tests/Feature/AttendanceNamingConventionTest.php`
- `tests/Feature/ProjectOvertimeRelationTest.php`
- `tests/Feature/ProjectTaskSeederTest.php`
- `tests/Feature/OvertimeDeadlineTaskSeederTest.php`

## Catatan
- File migration `2026_06_14_233219_add_blockers_and_attachment_path_to_project_tasks_table.php` tidak ditemukan pada state filesystem saat dokumentasi ini dibuat.
- State aktual menunjukkan `blockers` dan `attachment_path` sudah berada di migration utama `2026_06_11_042442_create_project_tasks_table.php`.
- `git diff --check` sebelumnya hanya mengeluarkan warning line ending CRLF untuk `resources/views/attendance/overtimes/detail.blade.php`, bukan error whitespace.
