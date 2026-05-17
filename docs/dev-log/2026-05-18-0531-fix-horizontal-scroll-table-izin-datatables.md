# 2026-05-18 05:31 - Perbaikan Scroll Horizontal Tabel Izin (DataTables)

## Ringkasan
- Memperbaiki agar scroll horizontal benar-benar terjadi pada area tabel (kolom + nilai row), bukan pada card/container lain.

## File Diubah
- `resources/views/absensi/izin.blade.php`

## Detail
- Ubah DataTables config:
  - `scrollX: false` -> `scrollX: true`
- Sesuaikan CSS DataTables wrapper:
  - `.izin-table-scroll-area` dibuat `overflow: hidden`
  - `#tableLogs_wrapper .dt-scroll-body` dipaksa `overflow-x: auto` dan `overflow-y: hidden`
  - Tetapkan `min-width: 980px` pada tabel di `dt-scroll-head` dan `dt-scroll-body` supaya kolom tidak dipaksa mengecil.

## Dampak
- Pada viewport mobile, header kolom dan isi row bisa digeser horizontal bersama.
- Bagian atas card/title/filter tetap statis.
