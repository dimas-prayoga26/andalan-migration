# Try-Catch Controller dan Seeder

Tanggal: 2026-05-13 02:35 WIB

## Ringkasan
- Menambahkan proteksi `try-catch` untuk alur request controller melalui middleware global.
- Menambahkan `try-catch` pada seluruh method `run()` di folder `database/seeders`.

## Detail Perubahan
- Tambah middleware:
  - `app/Http/Middleware/HandleControllerExceptions.php`
  - Menangkap exception request, `report($e)`, lalu:
    - response JSON 500 untuk request JSON/AJAX
    - abort 500 untuk request web biasa
- Registrasi middleware di:
  - `bootstrap/app.php` pada stack `web`.
- Seeder dibungkus `try-catch`:
  - `CompanySeeder`
  - `DatabaseSeeder`
  - `EmployeeIdentitySeeder`
  - `EmployeeProfileSeeder`
  - `LeaveBalanceSeeder`
  - `MetaDataDivisionSeeder`
  - `MetaDataDomiciliSeeder`
  - `MetaDataGenderSeeder`
  - `MetaDataLeaveCompanySeeder`
  - `MetaDataMaritalStatusSeeder`
  - `MetaDataPermissionTypeSeeder`
  - `MetaDataPositionSeeder`
  - `PositionSeeder`
  - `RulesOfAttendacesSeeder`
  - `UserSeeder`

## Validasi
- `vendor/bin/pint --dirty --format agent` -> passed
- `php artisan db:seed --no-interaction` -> seluruh seeder berhasil
