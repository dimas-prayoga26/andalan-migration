# 2026-05-18 05:46 - Restore Single Sorting Icon Set di Tabel Izin

## Ringkasan
- Mengembalikan icon sorting asc/desc yang sempat hilang.
- Tetap mencegah icon dobel dengan cara: hide legacy pseudo + render icon DataTables v2 (`.dt-column-order`).

## File
- `resources/views/absensi/izin.blade.php`

## Detail
- Tetap mempertahankan override hide untuk pseudo legacy (`.sorting:before/:after`).
- Menambahkan style explicit untuk:
  - `span.dt-column-order::before` (ikon asc)
  - `span.dt-column-order::after` (ikon desc)
- Menambahkan highlight state aktif:
  - `th.dt-ordering-asc`
  - `th.dt-ordering-desc`
