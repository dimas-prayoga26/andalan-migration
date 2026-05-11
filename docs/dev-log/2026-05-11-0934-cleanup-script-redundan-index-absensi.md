# 2026-05-11 09:34 - Cleanup Script Redundan di Index Absensi

## Perubahan
- Membersihkan request payload IP refresh agar lebih ringkas (hapus variabel sementara yang tidak perlu).
- Menghapus langkah redundant setelah submit absen sukses:
  - reload DataTable lokal (karena sekarang langsung redirect),
  - fallback menutup modal (juga tidak dipakai setelah redirect aktif).

## File Terdampak
- `resources/views/absensi/index.blade.php`
