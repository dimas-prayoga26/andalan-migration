# 2026-05-18 06:31 - Sinkronisasi Scroll Tabel Index Absensi

## Ringkasan
- Menerapkan pola scroll tabel yang sama seperti halaman izin ke halaman index absensi.
- Fokus: scroll horizontal hanya di area tabel (header+row), serta mencegah header duplikat saat `scrollX`.

## File
- `resources/views/absensi/index.blade.php`

## Perubahan
1. Tambah wrapper style khusus tabel:
   - `.attendance-table-scroll-area`
2. Tambah kompatibilitas scroll wrapper DataTables:
   - `.dt-scroll*` dan `.dataTables_scroll*`
3. Menetapkan `min-width` tabel di scroll head/body agar kolom tidak terpotong di mobile.
4. Menyembunyikan header duplikat pada body scroll.
5. Menonaktifkan `span.dt-column-order` agar tidak ada icon sorting dobel dari source berbeda.
6. Update DataTable init:
   - `scrollX: true`
7. Ubah wrapper HTML:
   - `table-responsive` -> `table-responsive attendance-table-scroll-area`
