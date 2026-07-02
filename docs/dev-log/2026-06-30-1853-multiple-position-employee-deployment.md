# Multiple Position Employee Deployment

Tanggal: 2026-06-30 18:53 WIB

## Ringkasan

- Menambahkan tabel pivot `employee_deployment_positions` agar satu deployment employee dapat memiliki beberapa jabatan aktif.
- Mempertahankan `employee_deployments.current_position_id` sebagai primary position untuk kompatibilitas flow lama.
- Backfill pivot dari `employee_deployments.current_position_id` saat migration dijalankan.
- Mengubah pengecekan permission agar membaca permission dari primary position dan semua posisi aktif tambahan.
- Mengubah form Data Employee agar field Jabatan bisa memilih lebih dari satu posisi.
- Mengubah tampilan detail Data Employee dan profile composer agar menampilkan gabungan posisi.
- Mengubah grouping Leave Admin/PIC agar employee multi-position masuk ke setiap group jabatan aktifnya.
- Menyesuaikan seeder user/deployment agar sync ke pivot `employee_deployment_positions`.

## File Perubahan

- `database/migrations/2026_06_30_114352_create_employee_deployment_positions_table.php`
- `app/Models/EmployeeDeployment.php`
- `app/Models/Position.php`
- `app/Models/User.php`
- `app/Http/Controllers/AuthorizationController.php`
- `app/Http/Controllers/AdminAttendance/AttendanceLeaveController.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceLeaveController.php`
- `app/View/Composers/AttendanceProfileComposer.php`
- `app/View/Composers/ProjectManagementProfileComposer.php`
- `resources/views/authorization/form.blade.php`
- `resources/views/authorization/show.blade.php`
- `database/seeders/UserSeeder.php`
- `database/seeders/LegacySqlUserSeeder.php`
- `database/seeders/NiskalaMultiPicLeaveSeeder.php`
- `tests/Feature/EmployeeMultiplePositionSupportTest.php`

## Bug yang Dicek

- Permission route/sidebar tidak hanya membaca primary position.
- Form update employee tidak menghapus posisi tambahan.
- Tampilan employee tidak misleading dengan hanya satu posisi.
- Grouping Leave Admin/PIC tidak menghilangkan employee yang punya lebih dari satu posisi.
- Seeder tidak meninggalkan pivot posisi kosong setelah data deployment dibuat/diupdate.

## Verifikasi

- `php artisan migrate --no-interaction`
- Query database lokal:
  - `employee_deployments` dengan `current_position_id` terisi: 26
  - `employee_deployment_positions`: 26
  - `employee_deployment_positions` primary: 26
- `php artisan test --compact tests\Feature\EmployeeMultiplePositionSupportTest.php tests\Feature\UserDocumentsSourceCleanupTest.php`
- `php artisan view:cache`
- `vendor\bin\pint --dirty --format agent`

## Catatan Verifikasi

- `tests\Feature\AuthorizationMenuRouteTest.php tests\Feature\AuthorizationEmployeeListScopeTest.php` belum hijau penuh karena `DatabaseSeeder.php` pada worktree aktif tidak memanggil `PositionPermissionSeeder::class`. Ini terdeteksi oleh test lama dan bukan berasal dari perubahan multi-position.
- Percobaan verifikasi runtime via `php artisan tinker --execute` tidak dilanjutkan karena quoting PowerShell memotong ekspresi PHP.
