# Normalisasi Label Office ke Kota

Tanggal: 2026-07-06 10:53 WIB

## Ringkasan

- Mengubah label dropdown Branch / Office dari nama perusahaan menjadi nama lokasi kota.
- Office di Sleman/Daerah Istimewa Yogyakarta ditampilkan sebagai `Yogyakarta`.
- Office di Jakarta Selatan ditampilkan sebagai `Jakarta`.
- Menyinkronkan nilai `employee_deployments.workplace` dengan nama lokasi office.
- Menyesuaikan seeder agar data baru memakai label kota yang sama.

## Verifikasi

- `php artisan migrate --no-interaction`
- `php artisan test --compact tests/Feature/DataEmployeeBranchOfficeTest.php`
- `vendor/bin/pint --dirty --format agent`
