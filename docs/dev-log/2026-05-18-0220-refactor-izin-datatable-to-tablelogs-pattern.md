# Refactor Izin DataTable to tableLogs Pattern

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Ganti inisialisasi langsung DataTable ke pola fungsi `tableLogs()` agar konsisten dengan halaman `index`.
  - Script lama inisialisasi langsung dinonaktifkan (diganti dengan komentar penjelasan + refactor).
  - Tambah guard `$.fn.DataTable.isDataTable('#tableLogs')` untuk mencegah inisialisasi ganda.
  - Panggil `tableLogs();` setelah definisi fungsi.
  - Filter karyawan (`attendanceStaffFilter`) sekarang reload tabel dengan guard `if (leaveRequestTable)`.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
