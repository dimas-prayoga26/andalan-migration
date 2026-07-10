# Ringkasan Perubahan Overtime, Attendance, Project Task, dan Kanban

Tanggal: 2026-07-09 15:30 WIB

## Ringkasan

- Menambahkan informasi `Staff submitted` pada detail overtime PIC untuk menampilkan rentang clock-in dan clock-out staff.
- Mengubah pembuatan overtime PIC agar otomatis membuat `project_tasks` untuk staff yang di-assign.
- Menyesuaikan default task dari overtime: `status = pending`, `priority = high`, `start_date` dan `due_date` dari tanggal overtime, serta `assigned_by` dari PIC yang membuat overtime.
- Menghubungkan task overtime dengan `overtime_id` agar list task pada detail staff hanya menampilkan task yang sesuai sesi overtime terkait.
- Menambahkan modal detail task ketika item task staff diklik pada antarmuka PIC.
- Menjaga tombol `+ Add Task` tetap aktif pada detail overtime staff setelah clock-out dan setelah overtime berstatus `cancelled`, selama staff sudah clock-in.
- Menjaga step `Task & Hours Verification` tetap boleh `waiting` atau `pending` meskipun overtime sudah `cancelled`, sehingga data cancelled tetap masuk alur review PIC.
- Menampilkan task yang berasal dari overtime pada menu Timesheet & Reporting Task List dengan badge `Overtime`.
- Mengizinkan task overtime di Task List untuk di-update dan di-mark as done, tetapi menghilangkan opsi delete task overtime.
- Mengubah field `Volume Workload` pada modal create/update task overtime menjadi `Priority` dengan opsi `low`, `medium`, dan `high`.
- Mengubah date picker task overtime menjadi range date dengan label `Date`, lalu menyimpan nilainya ke `start_date` dan `due_date`.
- Menyesuaikan Attendance Recap agar badge `Alpha` pada kolom note menjadi teks biasa.
- Menyamakan badge jam clock-in/clock-out di Attendance Recap staff dengan style admin attendance logs.
- Menyesuaikan tampilan Attendance superadmin agar mengikuti pola menu Attendance staff.
- Mengonversi note late dari total menit menjadi format jam dan menit.
- Memperbaiki batas keterlambatan clock-in agar jam `08:00` tetap dihitung on time, dan baru late mulai `08:01`.
- Mengubah modal keterlambatan agar tidak menampilkan total menit mentah, tetapi format jam dan menit.
- Mengubah flow verifikasi Attendance Confirmation dan End of Shift agar otomatis menampilkan status `Please wait`, tanpa tombol `Mulai Verifikasi`.
- Menonaktifkan tombol clock-in/clock-out sampai status verifikasi berubah menjadi `Verification successful`.
- Mengubah timezone aplikasi ke `Asia/Jakarta`.
- Menyesuaikan `happened_at` untuk `Overtime Assignment Submitted` agar mengambil waktu dari `planned_start_time`.
- Membatasi list `Today's Early Birds` dan `Today's Running Late` maksimal 5 item.
- Mengurutkan `Today's Running Late` descending sehingga urutan pertama adalah staff dengan absensi paling akhir.
- Menyesuaikan total employee dan ikon metrik pada Admin Attendance Overview agar mengikuti scope list attendance.
- Menyesuaikan ikon metrik Timesheet & Reporting agar lebih merepresentasikan masing-masing metrik.
- Mengganti konten tab `#project-grid-pane` dari kartu project statis menjadi board kanban statis bergaya Trello.
- Menyamakan ukuran card kanban agar layout rapi dan konsisten.
- Mengaktifkan drag and drop card kanban dengan library `assets-workload/vendor/draggable/draggable.js`.
- Memperbaiki posisi mirror/clone drag agar tidak muncul dari sidebar dan lebih mengikuti cursor.
- Memisahkan header kolom kanban dari `.dropzoneContainer` agar card dapat dipindahkan ke area kolom/card, bukan harus diarahkan ke teks `To-Do List`, `In Progress`, `Review`, atau `Done`.

## Overtime dan PIC

- Detail overtime PIC menampilkan `Staff submitted` untuk membantu PIC membandingkan jadwal approved/planned dengan waktu real staff.
- Saat PIC membuat overtime, sistem membuat task otomatis pada `project_tasks`:
  - `title` dan `description` diisi dari instruction overtime.
  - `start_date` dan `due_date` diisi dari overtime date.
  - `priority` otomatis `high`.
  - `status` otomatis `pending`.
  - `assigned_by` diisi PIC yang meng-assign.
  - `overtime_id` diisi agar task bisa difilter berdasarkan sesi overtime.
- Task pada detail overtime staff/PIC dikondisikan berdasarkan `overtime_id`, sehingga task dari sesi lain tidak ikut tampil.
- Task yang dibuat/ditambahkan dalam konteks overtime tetap bisa dikelola oleh staff:
  - `Update Task` tetap tersedia.
  - `Mark as Done` tetap tersedia.
  - `Delete Task` disembunyikan untuk task overtime.
- Overtime cancelled tidak memaksa step `Task & Hours Verification` menjadi cancelled. Step tersebut tetap boleh waiting/pending agar PIC masih bisa melihat dan memproses data cancelled pada menu PIC.

## Attendance

- Attendance Confirmation dan End of Shift tidak lagi memakai tombol manual `Mulai Verifikasi`.
- IP dan status verifikasi menampilkan `Please wait` selama proses berjalan.
- Tombol clock-in/clock-out baru dapat digunakan setelah status berubah menjadi `Verification successful`.
- Jam `08:00` dihitung masih on time. Keterlambatan baru dihitung saat lewat dari jam mulai kerja, misalnya `08:01`.
- Tampilan keterlambatan dikonversi dari menit mentah menjadi format jam dan menit.
- Timezone aplikasi diselaraskan ke `Asia/Jakarta` agar jam activity, overtime, dan attendance tidak tampil bergeser dari waktu lokal.
- Attendance Recap:
  - Badge `Alpha` pada kolom note diganti menjadi teks biasa.
  - Badge clock-in/clock-out disamakan dengan style admin attendance logs.
  - Superadmin memakai tampilan Attendance yang sama dengan staff.

## Admin Attendance Overview

- Total employee pada overview disesuaikan dengan scope list attendance yang aktif.
- Employee yang tidak masuk cakupan RNB Jakarta atau akun superadmin tidak ikut dihitung.
- Ikon pada metrik overview diganti agar lebih sesuai dengan makna metrik.
- `Today's Early Birds` dibatasi maksimal 5 item.
- `Today's Running Late` dibatasi maksimal 5 item dan diurutkan descending berdasarkan jam absensi, sehingga yang paling akhir tampil di nomor 1.

## Timesheet & Reporting Task List

- Task overtime ikut tampil di Task List.
- Badge `Overtime` ditampilkan di sebelah title task dengan style badge merah/pink.
- Task overtime tetap bisa di-update dan di-mark as done dari menu Task List.
- Delete task disembunyikan untuk task overtime agar data yang terkait sesi overtime tidak hilang dari audit.
- Ikon metrik Timesheet & Reporting diperbarui agar sesuai dengan fungsi masing-masing metrik.

## Modal Create/Update Task Overtime

- Label `Volume Workload` diganti menjadi `Priority`.
- Opsi priority diganti:
  - `light` menjadi `low`
  - `moderate` menjadi `medium`
  - `heavy` menjadi `high`
- Field `Date` dan `Due Date` diganti menjadi date range dengan label `Date`.
- Nilai range tetap disimpan ke dua kolom database:
  - tanggal awal ke `start_date`
  - tanggal akhir ke `due_date`

## Project Grid / Kanban

- Konten `#project-grid-pane` diubah menjadi board kanban statis dengan kolom:
  - `To-Do List`
  - `In Progress`
  - `Review`
  - `Done`
  - `Backlog`
- Card kanban dibuat seragam ukurannya agar layout lebih stabil.
- Drag and drop diaktifkan memakai library draggable existing.
- Mirror/clone drag diperbaiki:
  - clone ditempel ke `document.body`
  - posisi mirror dipaksa `position: fixed`
  - width/height mirror disamakan dengan card asli
  - posisi mirror dihitung dari `clientX/clientY`
  - transition/transform mirror dimatikan agar gerak lebih mulus
- Header kolom dipisahkan dari `.dropzoneContainer`.
- Dropzone sekarang hanya berisi card atau empty state, sehingga perpindahan card tidak harus diarahkan ke teks header kolom.

## File Utama

- `app/Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php`
- `app/Http/Controllers/ProjectManagement/TaskListController.php`
- `app/Http/Controllers/StaffAttendance/AttendanceOvertimeController.php`
- `app/Support/Attendance/OvertimeReviewTableBuilder.php`
- `config/app.php`
- `resources/views/project_management/task_list/index.blade.php`
- `resources/views/project_management/task_list/partials/project-grid.blade.php`
- `resources/views/project_management/task_list/partials/task-list-items.blade.php`
- `tests/Feature/PicAttendanceOvertimeStoreTest.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`
- `tests/Feature/ProjectOvertimeRelationTest.php`
- `tests/Unit/OvertimeReviewTableBuilderTest.php`

## Test dan Verifikasi

- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests\Feature\ProjectManagementOverviewLayoutTest.php`
- `php artisan test --compact tests\Feature\PicAttendanceOvertimeStoreTest.php`
- `php artisan test --compact tests\Feature\ProjectOvertimeRelationTest.php`
- `php artisan test --compact tests\Unit\OvertimeReviewTableBuilderTest.php`

Catatan: command PHP di environment lokal dapat menampilkan warning `Module "mysqli" is already loaded`, tetapi test tetap berjalan.

## Catatan Lanjutan

- Board kanban pada `#project-grid-pane` masih bersifat statis dan belum menyimpan perpindahan card ke database.
- Jika board kanban akan dibuat dinamis, perlu ditambahkan struktur status/kolom pada data project task serta endpoint update posisi/status card.
