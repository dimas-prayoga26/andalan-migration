# 2026-05-18 06:07 - Sembunyikan Header Duplikat DataTables (scrollX) di Izin

## Ringkasan
- Menghilangkan baris icon sort kedua (header duplikat) yang muncul di bawah header utama tabel.

## Penyebab
- Saat `scrollX: true`, DataTables membuat header duplikat pada `dt-scroll-body` untuk sinkronisasi lebar kolom.
- CSS global theme membuat header duplikat itu masih terlihat.

## Perubahan
- Menambahkan override CSS khusus `#tableLogs_wrapper .dt-scroll-body`:
  - `thead` diset `visibility: hidden`
  - `tr` dan `th` header duplikat dicollapse (`height: 0`, `padding: 0`, `border: 0`)
  - `.dt-column-title` dan `.dt-column-order` di header duplikat disembunyikan.

## File
- `resources/views/absensi/izin.blade.php`
