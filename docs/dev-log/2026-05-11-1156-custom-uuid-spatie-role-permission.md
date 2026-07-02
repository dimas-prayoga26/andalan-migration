# 2026-05-11 11:56 - Custom UUID Spatie Role & Permission

## Perubahan
- Mengganti generator `uuid` untuk model Spatie (`Role` dan `Permission`) dari UUID default Laravel ke format custom:
  - `ddmmyyyy + urutan 4 digit`
  - contoh: `110520260001`
- Menambahkan trait reusable `GeneratesCustomSequenceUuid` dengan lock cache untuk mencegah bentrok sequence saat create paralel.
- Menonaktifkan pemakaian `HasUuids` pada `Role` dan `Permission`, lalu mengisi `uuid` custom saat event `creating`.

## Validasi
- Seeder berhasil dijalankan:
  - `php artisan db:seed --class=UserSeeder --no-interaction`

## File Terdampak
- `app/Models/Concerns/GeneratesCustomSequenceUuid.php`
- `app/Models/Role.php`
- `app/Models/Permission.php`
