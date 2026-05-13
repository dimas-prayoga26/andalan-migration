# Dev Log - Fix EmployeeFamilySeeder Employee ID Column

Tanggal: 2026-05-13 11:42 WIB  
File: `database/seeders/EmployeeFamilySeeder.php`

## Ringkasan
- Menyesuaikan `EmployeeFamilySeeder` dengan struktur tabel `employee_families` terbaru.
- Perubahan:
  - Ganti referensi kolom `employee_uid` menjadi `employee_id`.
  - Menghapus field `uid` dari payload karena kolom `uid` tidak ada di tabel.
  - Menghapus helper `resolveUid()` yang sudah tidak dipakai.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php artisan db:seed --class=EmployeeFamilySeeder --no-interaction` berhasil.
