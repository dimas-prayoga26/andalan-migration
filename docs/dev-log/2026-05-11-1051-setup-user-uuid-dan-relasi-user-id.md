# 2026-05-11 10:51 - Setup User UUID dan Relasi user_id

## Perubahan
- Mengubah primary key `users` dari auto increment ke UUID (`id` bertipe uuid primary key).
- Mengubah seluruh kolom relasi `user_id` utama di migration dari `foreignId` menjadi `foreignUuid`.
- Menyesuaikan `sessions.user_id` menjadi UUID nullable index.
- Mengaktifkan UUID di model `User` (`HasUuids`, `keyType=string`, `incrementing=false`).
- Menyesuaikan beberapa controller yang sebelumnya mengasumsikan `user_id` numerik (cast int) agar aman untuk UUID string.
- Menyesuaikan filter karyawan di halaman izin agar mendukung ID UUID string.

## File Terdampak
- `app/Models/User.php`
- `app/Http/Controllers/AttendanceController.php`
- `app/Http/Controllers/AttendancePermissionController.php`
- `app/Http/Controllers/AttendanceOvertimeController.php`
- `resources/views/absensi/izin.blade.php`
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/2026_05_04_065550_create_user_documents_table.php`
- `database/migrations/2026_05_04_065551_create_user_employments_table.php`
- `database/migrations/2026_05_04_065554_062904_create_user_profiles_table.php`
- `database/migrations/2026_05_04_082024_move_address_to_user_profiles_and_drop_user_addresses_table.php`
- `database/migrations/2026_05_05_014430_create_attendances_table.php`
- `database/migrations/2026_05_05_075122_create_attendances_overtime_table.php`
- `database/migrations/2026_05_06_072609_create_leave_balances_table.php`
- `database/migrations/2026_05_06_074434_create_attendance_permissions_table.php`
