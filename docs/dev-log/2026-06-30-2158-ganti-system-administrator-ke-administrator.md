# Ganti System Administrator ke Administrator

Tanggal: 2026-06-30 21:58 WIB

## Ringkasan

- Menghapus `System Administrator` dari master position aktif pada seeder.
- Menggunakan `Administrator` sebagai pengganti akses admin biasa, yaitu semua menu kecuali `PIC` dan `Director`.
- Menggunakan `Super Administrator` sebagai posisi permission tertinggi yang dapat mengakses semua menu.
- Memastikan data fresh seed tidak lagi memakai `System Administrator`.
- Menyesuaikan seed multiple position untuk Mevia, Erlin, dan Leonie agar memakai `Administrator`.
- Menutup sumber reintroduksi dari dump legacy dengan mapping `System Administrator` ke `Administrator` pada normalizer posisi legacy.
- Memastikan Leonie, Mevia, dan Erlin tidak memiliki posisi `Super Administrator` baik sebagai primary maupun additional position dari seeder.
- Menghapus `Super Administrator` dari assignment permission manual dan legacy admin; akses tertinggi tetap lewat role `superuser` atau posisi `Super Administrator` secara implisit.
- Merapikan label create data employee dari `Create Users`/`Create Staff` menjadi `Create User`.
- Mengaktifkan seed pending leave request untuk akun legacy Mevia dan Erlin agar muncul di PIC Leave Leonie.
- Menambahkan fallback approval cuti untuk employee tanpa PIC valid dan bukan supervisor aktif: Supervisor Review otomatis approved dan request masuk HR Verification/Admin.
- Mengembalikan request cuti milik supervisor/PIC aktif ke jalur menu PIC, sehingga tidak auto-approved dan bisa diproses dari PIC Leave.
- Menambahkan guard agar Admin tidak bisa approve pengajuan cuti milik sendiri; PIC tetap dapat memproses request miliknya sendiri saat user tersebut supervisor/PIC aktif.
- Menyesuaikan tampilan statis dan test agar tidak lagi mengandalkan posisi `System Administrator`.

## File Perubahan

- `app/Http/Controllers/AuthorizationController.php`
- `app/Models/User.php`
- `database/seeders/PositionSeeder.php`
- `database/seeders/MetaDataPositionSeeder.php`
- `database/seeders/PositionPermissionSeeder.php`
- `database/seeders/UserSeeder.php`
- `database/seeders/LegacySqlUserSeeder.php`
- `database/seeders/NiskalaMultiPicLeaveSeeder.php`
- `resources/views/employee_data/index.blade.php`
- `resources/views/employee_data/authorization.blade.php`
- `resources/views/authorization/index.blade.php`
- `resources/views/authorization/form.blade.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/NiskalaMultiPicLeaveSeeder.php`
- `app/Http/Controllers/StaffAttendance/AttendanceLeaveRequestController.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceLeaveController.php`
- `app/Http/Controllers/AdminAttendance/AttendanceLeaveController.php`
- `tests/Feature/AuthorizationEmployeeListScopeTest.php`
- `tests/Feature/AuthorizationMenuRouteTest.php`
- `tests/Feature/EmployeeMultiplePositionSupportTest.php`
- `tests/Feature/PicAttendanceModuleTest.php`
- `tests/Feature/RnbStaffSeederTest.php`
- `tests/Feature/UserSeederAdministratorTest.php`

## Verifikasi

- `php artisan migrate:fresh --seed --no-interaction`
- `php artisan db:seed --class=PositionSeeder --no-interaction`
- `php artisan db:seed --class=MetaDataPositionSeeder --no-interaction`
- `php artisan db:seed --class=PositionPermissionSeeder --no-interaction`
- Query verifikasi `positions` dan `employee_deployment_positions`.
- Query verifikasi `roles`, `positions`, dan `position_has_permissions` untuk memastikan `System Administrator` bernilai 0.
- Query verifikasi primary dan additional position Leonie, Mevia, dan Erlin untuk memastikan `Super Administrator` bernilai 0.
- Query verifikasi `position_has_permissions` untuk memastikan assignment menu `Super Administrator` bernilai 0.
- Query verifikasi history request cuti Leonie kembali hanya pending Supervisor Review agar muncul di PIC Leave.
- `php artisan test --compact tests\Feature\LeaveHistoryYearFilterTest.php tests\Feature\PicAttendanceModuleTest.php tests\Feature\AdminAttendanceOverviewStructureTest.php`
