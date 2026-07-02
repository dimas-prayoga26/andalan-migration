# Fix Konsistensi ID Tabel Izin (myTable -> tableLogs)

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`:
  - Ubah selector CSS empty state dari `#myTable` ke `#tableLogs`.
  - Ubah delegated event status select dari `$('#myTable').on(...)` ke `$('#tableLogs').on(...)`.
  - Ubah reload DataTable setelah delete dari `$('#myTable').DataTable().ajax.reload(...)` ke `leaveRequestTable.ajax.reload(...)`.

## Alasan
- Tabel utama sudah memakai ID `tableLogs`, tetapi masih ada referensi lama `myTable`.
- Ketidaksinkronan ini menyebabkan style/behavior DataTable tidak konsisten dan tampak berantakan.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
