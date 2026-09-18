# Project Location Laravolt Indonesia

Tanggal: 2026-08-18 15:24 WIB

## Ringkasan

- Menambahkan dependency `laravolt/indonesia` sebagai sumber data wilayah Indonesia lokal.
- Menambahkan tabel referensi `indonesia_provinces`, `indonesia_cities`, `indonesia_districts`, dan `indonesia_villages` agar command seed Laravolt dapat berjalan lengkap.
- Menambahkan kolom lokasi pada tabel `projects`: `province_code`, `city_code`, dan `address`.
- Menambahkan select Provinsi, select Kabupaten/Kota, dan textarea Alamat pada modal Add/Update Project.
- Dropdown Kabupaten/Kota difilter otomatis berdasarkan Provinsi yang dipilih.
- Select Provinsi dan Kabupaten/Kota memakai Select2 single-select agar bisa search, dengan tinggi dropdown dibatasi sekitar 5 item.
- Menyimpan lokasi project pada create/update dan mengisi ulang nilai tersebut saat edit project.

## Catatan Operasional

- Setelah deploy, jalankan `php artisan migrate`.
- Setelah migration selesai, jalankan `php artisan laravolt:indonesia:seed` untuk mengisi data wilayah.

## File yang Berubah

- `composer.json`
- `composer.lock`
- `app/Http/Controllers/ProjectManagement/ProjectController.php`
- `resources/views/project_management/projects/index.blade.php`
- `database/migrations/2026_08_18_151027_create_indonesia_provinces_table.php`
- `database/migrations/2026_08_18_151028_create_indonesia_cities_table.php`
- `database/migrations/2026_08_18_151029_add_location_fields_to_projects_table.php`
- `database/migrations/2026_08_18_151628_create_indonesia_districts_table.php`
- `database/migrations/2026_08_18_151629_create_indonesia_villages_table.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`

## Test dan Verifikasi

- `php artisan list laravolt --no-interaction`
- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=project`
- `composer validate --strict --no-check-publish`
- `vendor/bin/pint --dirty --format agent`

Hasil terakhir: `2 passed, 5 skipped (694 assertions)`.
