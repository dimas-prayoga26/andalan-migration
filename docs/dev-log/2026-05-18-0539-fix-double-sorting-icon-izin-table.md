# 2026-05-18 05:39 - Hilangkan Icon Sorting Dobel di Tabel Izin

## Ringkasan
- Memperbaiki icon sorting ascending/descending yang tampil dobel pada header tabel `tableLogs`.

## Penyebab
- CSS DataTables legacy (selector `.sorting:before/:after` dll) ikut aktif bersamaan dengan DataTables v2.

## Perubahan
- Menambahkan override CSS khusus `#tableLogs_wrapper` untuk menonaktifkan pseudo-element sorting legacy:
  - `.sorting:before/:after`
  - `.sorting_asc:before/:after`
  - `.sorting_desc:before/:after`
  - varian disabled.

## File
- `resources/views/absensi/izin.blade.php`
