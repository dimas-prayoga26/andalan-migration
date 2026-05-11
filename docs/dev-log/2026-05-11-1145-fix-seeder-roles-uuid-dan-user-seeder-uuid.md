# 2026-05-11 11:45 - Fix Seeder Roles UUID dan UserSeeder UUID

## Perubahan
- Memperbaiki `UserSeeder` agar membuat role lewat model `App\Models\Role` (bukan model Spatie default), sehingga UUID terisi otomatis.
- Menyesuaikan generator data profil/dokumen di `UserSeeder` karena `user_id` sekarang UUID string:
  - menghapus operasi aritmatika langsung pada `user->id`,
  - mengganti ke generator token numerik berbasis hash.
- Menambahkan konfigurasi key model UUID pada `Role` dan `Permission`:
  - `keyType = string`
  - `incrementing = false`

## File Terdampak
- `database/seeders/UserSeeder.php`
- `app/Models/Role.php`
- `app/Models/Permission.php`
