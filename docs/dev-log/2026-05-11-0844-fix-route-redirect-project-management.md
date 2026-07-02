# 2026-05-11 08:44 - Perbaiki Route Redirect ke Project Management

## Perubahan
- Memperbaiki nama route redirect setelah absen berhasil di halaman absensi.
- Mengganti dari `project_management.index` (tidak ada) menjadi `project_management` (route yang terdaftar).

## Dampak
- Error `Route [project_management.index] not defined.` hilang.
- Halaman absensi bisa dirender normal, dan redirect setelah absen sukses berjalan.

## File Terdampak
- `resources/views/absensi/index.blade.php`
