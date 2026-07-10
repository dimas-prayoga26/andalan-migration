# Admin Attendance Overview 18 Staff

Tanggal: 2026-07-06 11:05 WIB

## Ringkasan

- Mengubah scope Admin Attendance Overview dari company user login menjadi seluruh perusahaan.
- Mengecualikan akun role `superuser` dari denominator staff.
- Denominator overview sekarang memakai 18 employee aktif lintas perusahaan, bukan 6 akun aktif pada company RNB.
- Seluruh chart Daily, Weekly, Monthly, dan Year to Date memakai scope employee aktif yang sama.

## Verifikasi

- `php artisan test --compact tests/Feature/AdminAttendanceOverviewStructureTest.php`
- `vendor/bin/pint --dirty --format agent`
