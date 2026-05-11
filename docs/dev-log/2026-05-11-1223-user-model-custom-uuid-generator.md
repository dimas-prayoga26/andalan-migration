# 2026-05-11 12:23 - User Model Pakai Custom UUID Generator

## Perubahan
- Mengganti `User` dari generator UUID default Laravel (`HasUuids`) ke generator UUID custom project.
- Menambahkan event `creating` di model `User` untuk mengisi kolom `id` dengan format custom:
  - `ddmmyyyy + urutan 4 digit`
- Menyamakan pola generator dengan model `Role` dan `Permission` agar konsisten.

## Validasi
- Seeder berhasil:
  - `php artisan db:seed --class=UserSeeder --no-interaction`

## File Terdampak
- `app/Models/User.php`
