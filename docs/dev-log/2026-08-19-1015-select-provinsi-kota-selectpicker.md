# Select Provinsi/Kota Pakai Selectpicker

Tanggal: 2026-08-19 10:15 WIB

## Ringkasan

- Mengganti komponen dropdown Provinsi dan Kabupaten/Kota pada modal Add/Update Project dari Select2 menjadi `selectpicker` (bootstrap-select), menyamakan style dengan field Company yang sudah ada.
- Live search tetap tersedia lewat `data-live-search="true"`, menggantikan pencarian bawaan Select2.
- Logic filter Kabupaten/Kota berdasarkan Provinsi terpilih tetap jalan: option kota yang tidak sesuai provinsi di-disable/hidden lalu `selectpicker('refresh')`.
- Menghapus CSS dan JS yang khusus dipakai untuk Select2 pada dua field ini (`project-location-select2-dropdown`, `initializeProjectLocationSelect2`, dsb).
- Field Staff tetap memakai Select2 (multi-select tags), tidak diubah.
- Membatasi jumlah item yang tampil di dropdown menjadi maksimal 5 baris lewat `data-size="5"` (bawaan bootstrap-select) agar dropdown tidak berat saat daftar provinsi/kota panjang; sisanya bisa diakses lewat scroll atau live search.

## File yang Berubah

- `resources/views/project_management/projects/index.blade.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=project`
- `vendor/bin/pint --dirty --format agent`

Hasil terakhir: `2 passed, 5 skipped (688 assertions)`.
