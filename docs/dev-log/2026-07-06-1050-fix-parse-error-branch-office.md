# Fix Parse Error Branch / Office

Tanggal: 2026-07-06 10:50 WIB

## Ringkasan

- Memindahkan transformasi data office location dari directive `@json` di Blade ke `AuthorizationController`.
- Blade sekarang hanya melakukan encoding pada array `officeLocationOptions` yang sudah siap digunakan.
- Menambahkan verifikasi kompilasi Blade pada test Branch / Office agar parse error serupa terdeteksi otomatis.

## Verifikasi

- `php artisan view:cache --no-interaction`
- `php artisan test --compact tests/Feature/DataEmployeeBranchOfficeTest.php`
- `vendor/bin/pint --dirty --format agent`
