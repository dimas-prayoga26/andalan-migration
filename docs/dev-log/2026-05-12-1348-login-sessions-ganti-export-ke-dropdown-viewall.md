# Dev Log - Login Sessions Ganti Export ke Dropdown + View All

Tanggal: 2026-05-12  
File: `resources/views/absensi/index.blade.php`

## Ringkasan
- Header card `Login Sessions` diubah:
  - Hapus slot tombol `Export Report`.
  - Tambah dropdown periode (`All Time`, `Week`, `Month`).
  - Tambah tombol `View All`.
- Konfigurasi DataTable `#tableLogs` disederhanakan:
  - Hapus fitur `buttons` (Excel export).
  - Hapus `initComplete` yang memindahkan tombol export ke header.
  - `dom` diubah dari `ZBfrltip` menjadi `frltip`.
