# 2026-06-12 15:58 - Ringkasan Perubahan Overtime dan Project Task

## Ringkasan
- Menyambungkan kebutuhan overtime dengan project task, lifecycle log, dan tampilan list/detail overtime.
- Mengubah desain task overtime agar task disimpan di `project_tasks` dan dapat terhubung ke `overtimes` lewat `overtime_id`.
- Menghapus ketergantungan `project_tasks` ke `project_sections`, lalu menghapus juga `department_id` dari `project_tasks`.
- Department untuk task sekarang tidak disimpan di task, tetapi dibaca dari relasi employee: `projectTask -> employee -> deployment -> department`.
- Menambahkan seed data project RNB untuk `staff31` sampai `staff34`, masing-masing 5 task project.
- Menambahkan seed data overtime deadline: masing-masing staff punya 1 task pending deadline H+1 dan 3 task completed untuk histori.

## Perubahan Data Model
- `project_tasks.department_id` dihapus dari migration utama `create_project_tasks_table`.
- Index `project_tasks_department_status_index` ikut dihapus karena kolom `department_id` tidak dipakai lagi.
- Relasi `ProjectTask::department()` dihapus.
- Relasi `Department::projectTasks()` dihapus.
- Relasi `EmployeeDeployment::department()` ditambahkan agar department bisa diambil dari `current_department_id`.
- Relasi task yang dipakai sekarang:
  - `ProjectTask::project()`
  - `ProjectTask::employee()`
  - `ProjectTask::overtime()`
  - `ProjectTask -> employee -> deployment -> department`

## Keputusan Desain
- Daily task tidak selalu project task, jadi kategori task tidak dipaksa memakai project section.
- Project task tidak lagi memakai `project_sections`.
- `created_by` di `project_tasks` dihapus karena task dibuat/dipegang oleh staff terkait, bukan harus melekat ke supervisor.
- `department_id` di `project_tasks` dihapus karena department staff sudah tersedia dari `employee_deployments.current_department_id`.
- PIC project tetap diambil dari supervisor aktif melalui assignment staff ke supervisor.

## Project dan Staff Seeder
- `ProjectTaskSeeder` membuat project `RNB-EVENT-2026`.
- Project member diisi oleh `staff31`, `staff32`, `staff33`, dan `staff34`.
- Masing-masing staff mendapat 5 task project, total 20 base project task.
- Task project tidak lagi membawa field `department` atau `department_id`.
- `UserSeeder` ditambah mapping khusus RNB agar staff punya department dan position dari deployment:
  - `staff31`: `Administration, Finance and Legal` / `Finance and Administration Coordinator`
  - `staff32`: `Marketing and Promotion` / `Graphic Design`
  - `staff33`: `Project Planning and Development` / `Architecture Design`
  - `staff34`: `Operations` / `Documentation Event and Editor Video`

## Overtime Seeder
- `OvertimeDeadlineTaskSeeder` membuat overtime assignment untuk `staff31` sampai `staff34`.
- Tanggal overtime dan due date mengikuti tanggal sekarang di timezone `Asia/Jakarta`, lalu deadline dibuat H+1.
- Masing-masing overtime mendapat:
  - 1 task pending dengan deadline H+1.
  - 3 task completed sebagai histori.
- Completed task tidak lagi diberi suffix staff pada judul.
- Seeder juga membuat `overtime_lifecycle_logs` untuk fase assignment, execution, review, payroll, dan payment.

## Overtime Lifecycle dan Record Number
- Overtime memiliki `record_number` dengan format seperti `#OVT-2606-0001` pada tampilan.
- Lifecycle log dipakai untuk menghitung progress di overtime list.
- Progress card memakai jumlah lifecycle log yang sudah complete dibanding total lifecycle log.
- Status footer card diubah agar assigned/in progress tampil sebagai `Pending`, bukan `Assigned`.

## Overtime List
- Halaman `attendance/overtimes/index` sekarang menampilkan data list overtime dari database.
- Card list overtime menampilkan `instruction` sebagai teks utama.
- Judul task, nama staff, dan nama department di card list dihapus sesuai kebutuhan UI.
- Label progress bar menjadi `Complete`, sementara persentase tetap dihitung dari lifecycle log.
- Route detail pada card memakai named route `attendance.overtimes.detail`.

## Overtime Detail
- Route detail diubah dari query string:
  - Lama: `/attendance/overtimes/detail?id={id}`
  - Baru: `/attendance/overtimes/{id}`
- Route JSON data dipisahkan ke `/attendance/overtimes/{id}/data`.
- Panel `My Task Items` di detail overtime dihubungkan ke `project_tasks` milik overtime tersebut.
- Task dipisahkan menjadi:
  - `Latest to do's` untuk task pending.
  - `Latest finished to do's` untuk task completed.
- Tombol edit dan delete tetap tersedia dan dibuat fixed di sisi kanan item agar tidak bergeser karena panjang teks.
- Keterangan staff pada task item dihapus.
- Badge `Completed` pada finished task dihapus dari tampilan detail.

## File Utama Terdampak
- `app/Http/Controllers/AttendanceOvertimeController.php`
- `app/Models/ProjectTask.php`
- `app/Models/Department.php`
- `app/Models/EmployeeDeployment.php`
- `database/migrations/2026_06_11_042442_create_project_tasks_table.php`
- `database/seeders/ProjectTaskSeeder.php`
- `database/seeders/OvertimeDeadlineTaskSeeder.php`
- `database/seeders/UserSeeder.php`
- `resources/views/attendance/overtimes/index.blade.php`
- `resources/views/attendance/overtimes/detail.blade.php`
- `routes/web.php`

## Test dan Verifikasi
- `vendor\bin\pint --dirty --format agent` sudah dijalankan dan passed.
- Test yang passed:
  - `tests\Feature\BusinessTripSeederTest.php`
  - `tests\Feature\ProjectOvertimeRelationTest.php`
  - `tests\Feature\ProjectTaskSeederTest.php`
  - `tests\Feature\OvertimeDeadlineTaskSeederTest.php`
- Hasil test terkait: `6 passed (248 assertions)`.
- Test DB user seeder:
  - `tests\Feature\RnbStaffSeederTest.php`
  - `tests\Feature\UserSeederRoleTest.php`
  - Status: skipped karena environment lokal tidak memiliki `pdo_sqlite`.
- Verifikasi database lokal setelah seed ulang:
  - `project_tasks.department_id`: tidak ada.
  - Base project task: 20.
  - Overtime task: 16.
  - Deployment staff RNB:
    - `staff31`: `Administration, Finance and Legal` / `Finance and Administration Coordinator`
    - `staff32`: `Marketing and Promotion` / `Graphic Design`
    - `staff33`: `Project Planning and Development` / `Architecture Design`
    - `staff34`: `Operations` / `Documentation Event and Editor Video`

## Catatan Teknis
- Database lokal sudah disesuaikan langsung dengan drop kolom `project_tasks.department_id` karena keputusan sebelumnya tidak memakai migration transisi.
- `git diff --check` hanya memberi warning CRLF untuk `resources/views/attendance/overtimes/detail.blade.php`, bukan error whitespace.
- Full test suite belum dijalankan.
