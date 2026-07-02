# 2026-05-18 05:21 - Batasi Scroll Hanya di Area Tabel Izin

## Ringkasan
- Mengatur agar scroll horizontal hanya terjadi di area tabel (kolom header + nilai row).
- Card/header/filter di luar tabel tidak ikut menjadi area scroll.

## File Diubah
- `resources/views/absensi/izin.blade.php`

## Detail Teknis
- Tambah class baru `.izin-table-scroll-area`:
  - `overflow-x: auto`
  - `overflow-y: hidden`
  - `-webkit-overflow-scrolling: touch`
- Menetapkan `min-width` tabel pada area tersebut agar horizontal scroll aktif saat viewport sempit:
  - `.izin-table-scroll-area #tableLogs { min-width: 980px; }`
- Mengganti wrapper tabel dari:
  - `table-responsive`
  - menjadi `table-responsive izin-table-scroll-area`
