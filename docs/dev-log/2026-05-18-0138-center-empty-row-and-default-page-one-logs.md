# Center Empty Row + Keep Pagination Page 1 for Logs Table

## Tanggal
- 2026-05-18

## Perubahan
- Update `resources/views/absensi/index.blade.php` pada tabel logs (`#tableLogs`).
- Menambahkan CSS agar baris `No data available in table` berada di tengah:
  - selector: `#tableLogs.dataTable tbody td.dataTables_empty`
- Menambahkan fungsi JS `ensureEmptyPageOne(datatableApi)`.
- Saat data kosong (`recordsTotal === 0`), pagination tetap menampilkan halaman `1` (disabled/current), baik untuk struktur paging DataTables modern (`.dt-paging`) maupun legacy (`.dataTables_paginate`).
- Fungsi dipanggil pada `drawCallback` dan setelah inisialisasi DataTable.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
- `vendor/bin/pint --dirty --format agent` -> passed.
