# Project Detail Division Event Cleanup

Tanggal: 2026-08-21 10:53 WIB

## Ringkasan

- Memastikan tampilan detail Project Management memakai istilah dan selector `division`, bukan `department`, karena scope project sekarang berasal dari `event_divisions`.
- Mengganti class CSS, ID modal Google Drive, selector JS, dan fallback error message yang masih memakai nama `project-department` / `projectDepartment`.
- Mengubah copy summary dari `Track departmental tasks` menjadi `Track division tasks`.
- Query detail project tetap memakai `project_division_event`, `event_divisions`, `project_tasks.event_division_id`, dan `employee_deployments.current_event_division_id`.

## File yang Berubah

- `resources/views/project_management/projects/detail.blade.php`
- `resources/views/project_management/projects/index.blade.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=project`

Hasil terakhir: `2 passed, 5 skipped (687 assertions)`.
