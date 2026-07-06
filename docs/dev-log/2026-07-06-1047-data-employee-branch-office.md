# Data Employee Branch / Office

Tanggal: 2026-07-06 10:47 WIB

## Ringkasan

- Mengganti input bebas `Workplace / Domicile` pada form create/update Data Employee menjadi dropdown `Branch / Office`.
- Pilihan branch difilter berdasarkan perusahaan yang dipilih.
- Menambahkan nama pada `office_locations`, termasuk `RNB Branch Jakarta` dan `RNB Branch Jogja`.
- Menyinkronkan pilihan branch ke `employee_deployments.current_office_location_id` dan `workplace` agar geofencing mengikuti kantor yang benar.
- Menolak branch yang tidak aktif atau berasal dari perusahaan lain melalui validasi backend.

## Verifikasi

- `php artisan test --compact tests/Feature/DataEmployeeBranchOfficeTest.php`
- `vendor/bin/pint --dirty --format agent`
