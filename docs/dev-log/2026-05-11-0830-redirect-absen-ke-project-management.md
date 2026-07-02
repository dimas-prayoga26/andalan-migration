# 2026-05-11 08:30 - Redirect Setelah Absen Berhasil

## Perubahan
- Menambahkan URL route `project_management.index` di script halaman absensi.
- Setelah submit absen berhasil (`done` AJAX), halaman sekarang langsung redirect ke `project_management.index`.

## File Terdampak
- `resources/views/absensi/index.blade.php`
