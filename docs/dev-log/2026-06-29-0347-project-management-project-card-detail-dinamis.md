# Project Management Project Card dan Detail Dinamis

Tanggal: 2026-06-29 03:47 WIB

## Ringkasan

- Mengubah menu **Project Management > Projects** agar card project muncul dari project yang diikuti staff login melalui `project_members` aktif.
- Menambahkan `App\Http\Controllers\ProjectManagement\ProjectController` untuk memisahkan flow project list, project detail, fallback detail, dan toggle task department.
- Mengubah detail project agar menerima route berbasis project id, sehingga task yang tampil hanya task dari project yang sedang dibuka.
- Menambahkan field `live_event_start_date` dan `live_event_end_date` pada `projects` agar **Live Event Dates** terpisah dari **Project Lifecycle**.
- Mengubah kategori department pada detail project menjadi dinamis dari `employee_deployments.current_department_id` dan relasi `departments`.
- Membatasi action checkbox task hanya untuk department staff yang sedang login.
- Department lain tetap tampil sebagai view-only agar staff bisa melihat progress lintas department tanpa bisa mengubah tasknya.

## Route dan Controller

- Project list: `GET /project-management/projects` melalui `ProjectManagement\ProjectController@index`.
- Detail fallback lama: `GET /project-management/projects/detail` redirect ke project aktif pertama milik staff login.
- Detail project: `GET /project-management/projects/{project}` melalui `ProjectManagement\ProjectController@detail`.
- Toggle task department: `PATCH /project-management/projects/{project}/tasks/{projectTask}/toggle`.
- Route legacy `/project-management/detail` diarahkan ke fallback detail project.

## Scope Data

- Project card hanya mengambil project dengan membership staff login yang `status = active` dan `left_at = null`.
- Detail project hanya bisa dibuka oleh staff yang menjadi member aktif project tersebut.
- Query task detail memakai `where('project_id', $project->id)` agar daily task atau task project lain tidak ikut masuk.
- Department card dibentuk dari department member project dan department task berdasarkan `employee_deployments.current_department_id`.
- Permission action task dicek ulang di backend, bukan hanya dari checkbox frontend.

## View

- `resources/views/project_management/projects/index.blade.php` sekarang memakai `$projectCards` dari controller.
- `resources/views/project_management/projects/detail.blade.php` sekarang memakai `$projectDetail`, `$projectSummary`, dan `$projectDepartmentGroups`.
- Project detail menampilkan:
  - `Live Event Dates` dari `projects.live_event_start_date` sampai `projects.live_event_end_date`.
  - `Project Lifecycle` dari `projects.start_date` sampai `projects.end_date`.
- Team project pada card detail ditampilkan sebagai avatar bertumpuk dari `user_profiles.profile_picture`.
- Jika staff tidak memiliki profile picture, avatar fallback menampilkan angka dari nama/username staff seperti `staff31` menjadi `31`; jika tidak ada angka, fallback memakai inisial.
- Card atas detail project disesuaikan:
  - `Tasks Summary` memakai donut multi-department dan legend jumlah task per department.
  - `Department Scope` diganti menjadi `Tasks Over Time` berbasis Chart.js dengan series `Incomplete` dan `Complete` per minggu.
- Card department menampilkan action/status:
  - Tombol `Drive` untuk department staff login.
  - `View Only` untuk department lain.
- Checkbox hanya muncul pada task department staff login.
- Project folder di grid Task List sekarang membuka route detail project by id.
- Style detail project dirapikan ulang:
  - row overview dan row department dipisah agar card tidak menempel,
  - card diberi radius dan shadow yang konsisten,
  - summary task memakai ring progress,
  - department scope memakai list compact dengan color marker,
  - task row department diberi alignment checkbox/action yang lebih stabil.

## Verifikasi Terakhir

- `vendor\bin\pint --dirty --format agent`
- `php artisan route:list --path=project-management --except-vendor`
- `php artisan view:cache`
- `php artisan test --compact tests\Feature\ProjectManagementOverviewLayoutTest.php`
- `php artisan db:seed --class=ProjectTaskSeeder --no-interaction`
- `php artisan view:clear`
- `git diff --check`

## Seeder Demo Cross-Company

- Menambahkan project `Muktamar ke VI PKB 2024` dengan code `GROUP-COLLAB-2026` dan lokasi `Bali Nusa Dua Convention Center, Badung, Bali`.
- Project dimiliki company `AndalanKu`, tetapi member project lintas company:
  - `staff11`, `staff12`, dan `staff13` dari AndalanKu untuk department `Marketing and Promotion`,
  - `staff21`, `staff22`, dan `staff23` dari KMA untuk department `Information and Communications Technology`,
  - `staff31` dari RNB untuk department `Administration, Finance and Legal`,
  - `staff33` dari RNB, `staff14` dari AndalanKu, dan `staff45` dari Niskala untuk department `Project Planning and Development`,
  - `staff44` dari Niskala untuk department `Operations`.
- Department `Marketing and Promotion`, `Information and Communications Technology`, dan `Project Planning and Development` masing-masing disiapkan memiliki 3 staff member pada project demo lintas company.
- Task project dibuat untuk masing-masing member agar detail project membuktikan scope data mengikuti `project_members`, `project_id`, dan department dari `employee_deployments.current_department_id`, bukan company RNB saja.
- Menambahkan action `+ Add Task`, `Update Task`, dan `Delete Task` pada detail project untuk department staff login sendiri.
- Mengganti badge `Your Department` pada department staff login menjadi tombol `Drive`.
- Menyamakan modal `+ Add Task` pada detail project dengan modal `Create New Task` di Task List; field Assignee dihapus dan task otomatis assigned ke staff login.
- Date picker pada modal detail project memakai `bootstrap-datetimepicker` ter-scope ke field Date/Due Date dan otomatis hide saat klik field lain.

Catatan: test behavior database yang membutuhkan SQLite tetap skipped di environment lokal karena SQLite PDO driver tidak tersedia.
