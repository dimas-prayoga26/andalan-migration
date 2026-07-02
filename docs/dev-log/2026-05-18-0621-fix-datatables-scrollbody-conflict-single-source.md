# 2026-05-18 06:21 - Fix Error dataTables_scrollBody (Single DataTables Source)

## Ringkasan
- Menyelesaikan error/bentrok pada `dataTables_scrollBody` dengan menormalkan sumber DataTables menjadi satu versi.

## Akar Masalah
- Aplikasi memuat **dua library DataTables** bersamaan:
  - CDN DataTables v2 (`dataTables.js` + `dataTables.dataTables.css`)
  - Vendor bundle lama (`jquery.dataTables.bundle.min.js` + style theme)
- Akibatnya class wrapper campur (`dt-scroll-*` dan `dataTables_scroll*`) serta behavior scroll/header duplikat tidak konsisten.

## Perubahan
1. Hapus include DataTables CDN JS:
   - `resources/views/layouts/commonjs.blade.php`
2. Hapus include DataTables CDN CSS:
   - `resources/views/layouts/mainhead.blade.php`
3. Update CSS `izin.blade.php` agar kompatibel untuk kedua nama wrapper scroll:
   - `.dt-scroll*` dan `.dataTables_scroll*`

## Dampak
- Tidak ada lagi konflik class wrapper DataTables.
- Scroll tabel horizontal lebih stabil.
- Error terkait `dataTables_scrollBody` berkurang/hilang.
