# Project Management Task List AJAX Filter dan Kalender

Tanggal: 2026-06-29 01:42 WIB

## Ringkasan

- Mengubah halaman **Project Management > Task List** dari konten dummy template menjadi data dinamis dari `project_tasks`.
- Memisahkan controller Task List ke `App\Http\Controllers\ProjectManagement\TaskListController`.
- Menambahkan filter Task berbasis AJAX agar perubahan periode dan aksi task tidak perlu reload halaman penuh.
- Memecah render Task List menjadi partial Blade untuk area yang perlu diperbarui secara realtime.
- Mengaktifkan kalender kanan sebagai kontrol filter bulan/tahun.
- Merapikan highlight kalender agar hanya satu tanggal aktif dan default kembali ke tanggal hari ini saat halaman reload.

## Route dan Controller

- Route Task List tetap pada `/project-management/task-list`.
- Menambahkan route `GET /project-management/task-list/filter` dengan nama `project_management.task_list.filter`.
- Endpoint `filter()` mengembalikan JSON berisi:
  - status sukses,
  - selected month,
  - selected year,
  - label bulan,
  - fragment HTML untuk list, week plan, dan project grid.
- Aksi create, update, mark done, dan delete tetap memakai endpoint JSON terpisah.
- Scope task dibatasi ke employee login dan `overtime_id` null agar task overtime tidak ikut terubah dari menu Task List.

## View Task List

- Halaman utama: `resources/views/project_management/task_list/index.blade.php`.
- Render task dipisah ke:
  - `resources/views/project_management/task_list/partials/task-list-items.blade.php`
  - `resources/views/project_management/task_list/partials/week-plan.blade.php`
  - `resources/views/project_management/task_list/partials/project-grid.blade.php`
- Area yang di-refresh AJAX:
  - `#taskListItemsPanel`
  - `#taskListWeekPlanPanel`
  - `#taskListProjectGridPanel`
- Tab task tetap tersedia:
  - All
  - Ongoing
  - Done
- Project grid menampilkan folder Daily Tasks dan project aktif milik staff.

## Filter Task

- Modal filter diubah menjadi Month dan Year terpisah.
- Submit filter memakai AJAX ke `project_management.task_list.filter`.
- Response filter memasang ulang fragment list, week plan, dan project grid tanpa `window.location.reload()`.
- Label tombol filter ikut diperbarui sesuai periode yang dipilih.

## Kalender Kanan

- Kalender kanan menggunakan `bootstrap-datetimepicker` inline.
- Klik tanggal atau pindah bulan akan menyinkronkan filter month/year dan memanggil AJAX refresh.
- Saat halaman reload, tanggal aktif otomatis memakai tanggal hari ini berdasarkan `now('Asia/Jakarta')`.
- Jika periode yang tampil adalah bulan berjalan, kalender memilih tanggal hari ini.
- Jika periode yang tampil bukan bulan berjalan, kalender memilih tanggal 1 bulan tersebut sebagai anchor tampilan.
- CSS khusus `project-task-calendar-widget` ditambahkan agar `today` tidak ikut biru ketika bukan tanggal aktif, sehingga tidak ada dua tanggal biru.

## Interaksi Task

- Create task, update task, mark as done, dan delete task memakai AJAX.
- Date picker pada modal create/update task memakai `bootstrap-datetimepicker`, bukan native `showPicker()`, agar widget bisa ditutup saat user klik field lain tanpa wajib memilih tanggal.
- Date picker modal task diatur dengan `widgetPositioning.vertical = top` agar kalender muncul ke arah atas dekat field Date/Due Date.
- Setelah aksi sukses, modal ditutup dan Task List di-refresh melalui endpoint filter.
- Pesan sukses/error tetap memakai SweetAlert dengan message dari backend.
- Dropdown titik tiga memakai SVG inline supaya tetap tampil walau icon font bermasalah.

## Alert Task

- Alert `Pending Tasks Reminder!` dipertahankan dan hanya tampil jika ada task aktif dari bulan sebelumnya.
- Alert `Ready for a Great Day?` dipertahankan sebagai CTA untuk membuka modal tambah task.

## File Utama

- `app/Http/Controllers/ProjectManagement/TaskListController.php`
- `routes/web.php`
- `resources/views/project_management/task_list/index.blade.php`
- `resources/views/project_management/task_list/partials/task-list-items.blade.php`
- `resources/views/project_management/task_list/partials/week-plan.blade.php`
- `resources/views/project_management/task_list/partials/project-grid.blade.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`

## Verifikasi Terakhir

- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache`
- `php artisan test --compact tests\Feature\ProjectManagementOverviewLayoutTest.php`
- `php artisan view:clear`
- `git diff --check`

Catatan: test behavior database Task List masih skipped di environment lokal karena SQLite PDO driver tidak tersedia.
