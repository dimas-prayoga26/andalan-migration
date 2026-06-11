# Overtime Project Task Foundation

## Perubahan
- Menambahkan fondasi data project untuk kebutuhan overtime:
  - `projects`
  - `project_members`
  - `project_sections`
  - `project_tasks`
- Menambahkan model:
  - `Project`
  - `ProjectMember`
  - `ProjectSection`
  - `ProjectTask`
- Menambahkan relasi project:
  - `Company` memiliki banyak `projects`.
  - `Project` memiliki banyak `memberships`, `members`, `sections`, dan `tasks`.
  - `ProjectMember` terhubung ke `project` dan `employee`.
  - `ProjectSection` terhubung ke `project` dan memiliki banyak `tasks`.
  - `ProjectTask` terhubung ke `project`, `section`, `employee`, `creator`, dan optional `overtime`.
  - `Employee` memiliki banyak `projectMemberships`, `projects`, `projectTasks`, dan `overtimes`.
  - `User` memiliki `createdProjects` dan `createdProjectTasks`.
- Mengubah desain relasi task-overtime agar 1 overtime dapat memiliki banyak task:
  - Menghapus konsep final `overtimes.task_id`.
  - Menambahkan `project_tasks.overtime_id` nullable sebagai relasi optional ke `overtimes.id`.
- Menambahkan `project_sections` untuk pembagian task per kategori/section dalam satu project, misalnya Administration, Graphic Design, 3D Event Design, Documentation, dan Publication & Technology.
- Menghapus kolom `role` dari `project_members` karena keanggotaan project cukup mencatat staff yang ikut project.
- Mengubah status utama `overtimes.status` dari flow pengajuan menjadi flow assignment:
  - `assigned`
  - `in_progress`
  - `completed`
  - `cancelled`
- Menyesuaikan `AttendanceOvertimeController` agar:
  - Assignment overtime default menjadi `assigned`.
  - Overtime dengan actual start tanpa actual end menjadi `in_progress`.
  - Overtime dengan actual start dan actual end lengkap menjadi `completed`.
  - Overtime yang dibatalkan menjadi `cancelled`.
- Menyesuaikan label statis pada halaman overtime dari istilah lama `Pending`/`Approved`/`Rejected` ke `Assigned`/`In Progress`/`Completed`/`Cancelled`.
- Merapikan migration agar perubahan status overtime dan relasi task-overtime langsung berada di migration utama, bukan migration tambahan.
- Menambahkan test `ProjectOvertimeRelationTest` untuk mengunci struktur migration, relasi model, dan lifecycle status overtime.

## Catatan Desain
- `projects.created_by` dipakai sebagai PIC project sesuai keputusan diskusi.
- `project_members` hanya dipakai untuk daftar staff yang mengikuti project.
- `project_sections` menjadi pembagian kategori pekerjaan di dalam project.
- `project_tasks` adalah detail tugas, masing-masing memiliki satu staff owner dan wajib berada dalam satu section.
- `project_tasks.overtime_id` nullable memungkinkan task belum masuk overtime, atau beberapa task terkait ke satu overtime yang sama.

## Verifikasi
- `php -l database/migrations/2026_05_05_014427_create_overtimes_table.php`
- `php -l database/migrations/2026_06_11_042442_create_project_members_table.php`
- `php -l database/migrations/2026_06_11_042442_create_project_sections_table.php`
- `php -l database/migrations/2026_06_11_042442_create_project_tasks_table.php`
- `php -l app/Models/Project.php`
- `php -l app/Models/ProjectMember.php`
- `php -l app/Models/ProjectSection.php`
- `php -l app/Models/ProjectTask.php`
- `php artisan migrate --no-interaction`
- `php artisan test --compact tests/Feature/ProjectOvertimeRelationTest.php`
- `vendor\bin\pint --dirty --format agent`
- `php artisan view:cache --no-interaction`
- `php artisan view:clear --no-interaction`
- `git diff --check`
