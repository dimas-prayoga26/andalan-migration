# 2026-05-18 05:56 - Sorting Icon Izin: Pakai Legacy Saja

## Ringkasan
- Mengatasi icon asc/desc yang hilang setelah override sebelumnya.
- Strategi final: gunakan icon sorting legacy dari theme (`plugins.css`) dan nonaktifkan icon DataTables v2 (`.dt-column-order`) agar tidak dobel.

## File
- `resources/views/absensi/izin.blade.php`

## Detail
- Menghapus override yang mematikan pseudo icon legacy.
- Menghapus style custom render icon `dt-column-order`.
- Menambahkan rule tunggal:
  - `#tableLogs_wrapper table.dataTable thead > tr > th span.dt-column-order { display: none !important; }`
