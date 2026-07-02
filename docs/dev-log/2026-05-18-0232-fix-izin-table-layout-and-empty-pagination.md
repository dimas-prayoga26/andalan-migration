# Fix Layout Tabel Izin Setelah Refactor tableLogs

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Tambah CSS layout DataTables untuk `#tableLogs_wrapper` agar kontrol atas konsisten:
    - `Show entries` di kiri
    - `Search` di kanan
    - responsif untuk mobile
  - Nonaktifkan `scrollX` dan `scrollCollapse` pada DataTable izin untuk menghindari header dobel / row icon sort tambahan.
  - Tambah helper JS `ensureEmptyPageOne(datatableApi)` agar saat data kosong pagination tetap menampilkan halaman `1`.
  - Panggil helper di `drawCallback` dan setelah init.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
