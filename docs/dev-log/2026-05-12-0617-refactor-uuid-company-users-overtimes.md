# 2026-05-12 06:17 - Refactor UUID Company, Users, dan Overtimes

## Ringkasan Perubahan
- Generator custom ID diubah ke format UUID style `8-4-4-4-12` dengan obfuscation dari sequence harian.
- Model yang pakai UUID diseragamkan ke trait `GeneratesCustomSequenceUuid`.
- Tabel `companies` diubah ke primary key UUID, termasuk penyesuaian foreign key yang mengarah ke `companies`.
- Struktur `users` disesuaikan agar pakai `company_id` (`foreignUuid` ke `companies.id`) dan tanpa kolom `uid`.
- Tabel `rules_of_attendaces` diubah ke primary key UUID.
- Tabel `overtimes` disesuaikan: `assigned_by` relasi ke `users.id` (bukan `user_id` lama), dan sinkronisasi model/controller.

## File Utama yang Diubah
- `app/Models/Concerns/GeneratesCustomSequenceUuid.php`
- `app/Models/User.php`
- `app/Models/Company.php`
- `app/Models/Employee.php`
- `app/Models/EmployeeDeployment.php`
- `app/Models/AttendanceOvertime.php`
- `app/Http/Controllers/AttendanceController.php`
- `app/Http/Controllers/AttendanceOvertimeController.php`
- `app/Http/Controllers/LeaveRequestController.php`
- `database/migrations/0001_01_01_000000_create_companies_table.php`
- `database/migrations/0001_01_01_000001_create_users_table.php`
- `database/migrations/2026_04_28_071845_add_company_id_to_users_table.php`
- `database/migrations/2026_05_04_080751_create_employee_deployments_table.php`
- `database/migrations/2026_05_05_070254_create_rules_of_attendaces_table.php`
- `database/migrations/2026_05_05_014427_create_overtimes_table.php`
- `database/migrations/2026_05_06_071038_create_meta_data_leave_companies_table.php`
- `database/seeders/UserSeeder.php`
- `database/seeders/RulesOfAttendacesSeeder.php`
- `resources/views/absensi/index.blade.php`

## Catatan Eksekusi
- Setelah perubahan migration, disarankan jalankan `php artisan migrate:fresh --seed` agar skema dan data seeder sinkron penuh.
