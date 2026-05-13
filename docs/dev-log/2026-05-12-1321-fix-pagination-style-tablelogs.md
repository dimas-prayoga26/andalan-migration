# 2026-05-12 13:21 - Fix Pagination Style TableLogs

## Ringkasan
- Perbaiki styling pagination DataTables pada tabel `#tableLogs` agar tampil sesuai gaya template.
- Tambah style khusus wrapper `#tableLogs_wrapper` untuk:
  - baris kontrol atas (search + length),
  - baris bawah (info + paginate),
  - button pagination aktif/hover/disabled.
- Ganti icon paginator dari `fa-solid` ke `bootstrap-icons` (`bi-chevron-left/right`) agar konsisten dan pasti ter-render.

## File Diubah
- `resources/views/absensi/index.blade.php`
