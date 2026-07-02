# Administrator Superadmin COO dan PIC Assignment

Tanggal: 2026-06-30 19:36 WIB

## Ringkasan

- Menambahkan posisi `Administrator` dan `Super Administrator`.
- Mengatur permission:
  - `Administrator` dan `System Administrator` dapat mengakses semua menu kecuali `PIC` dan `Director`.
  - `Super Administrator` dapat mengakses semua menu, termasuk `PIC` dan `Director`.
  - Role `superuser` tidak lagi dikecualikan dari menu `PIC` dan `Director`.
  - `Chief Operating Officer` memakai akses menu yang sama seperti `Director`.
- Menambahkan unique index `positions.name` langsung pada migration `create_positions`.
- Menyesuaikan `PositionSeeder` agar tidak membuat duplicate posisi lagi.
- Menambahkan posisi tambahan berdasarkan user:
  - Admin Andalan: `Super Administrator`.
  - Syafiq: `Supervisor`.
  - Leonie: `System Administrator`, `Supervisor`.
  - Erlin: `System Administrator`, `Accounting and Taxation`.
  - Rexy: `Director`, `Supervisor`.
  - Fuad: `Director`, `Supervisor`.
  - Fahmil: `Director`, `Supervisor`.
  - Lukman: `Chief Operating Officer`, `Supervisor`.
- Menambahkan explicit PIC assignment:
  - Leonie PIC untuk Mevia dan Erlin.
  - Syafiq PIC untuk Syarif, Rifka, dan Dimas.
  - Rexy PIC untuk Arum dan Dedy.
  - Fahmil PIC untuk Arya, Yusuf, Alfian, dan Aira.

## File Perubahan

- `database/migrations/2026_05_04_080748_create_positions_table.php`
- `database/seeders/PositionSeeder.php`
- `database/seeders/PositionPermissionSeeder.php`
- `database/seeders/LegacySqlUserSeeder.php`
- `database/seeders/NiskalaMultiPicLeaveSeeder.php`
- `database/seeders/EmployeePicAssignmentSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Models/User.php`
- `app/View/Composers/SidebarPermissionComposer.php`
- `tests/Feature/EmployeeMultiplePositionSupportTest.php`

## Verifikasi

- `php artisan migrate:fresh --seed --no-interaction`
- `php artisan db:seed --class=PositionSeeder --no-interaction`
- `php artisan db:seed --class=PositionPermissionSeeder --no-interaction`
- `php artisan db:seed --class=EmployeePicAssignmentSeeder --no-interaction`
- Query verifikasi `positions`, `position_has_permissions`, `employee_deployment_positions`, dan `employee_pic_assignments`.
- `php artisan test --compact tests\Feature\EmployeeMultiplePositionSupportTest.php`
- `php artisan test --compact tests\Feature\AuthorizationMenuRouteTest.php tests\Feature\AuthorizationEmployeeListScopeTest.php`
- `php artisan view:cache`
- `php artisan view:clear`
- `vendor\bin\pint --dirty --format agent`
- `git diff --check`

## Catatan

- Rully dan Hilmi belum ada pada data user aktif lokal, sehingga assignment Lukman untuk staff bawahnya belum menghasilkan row PIC assignment.
