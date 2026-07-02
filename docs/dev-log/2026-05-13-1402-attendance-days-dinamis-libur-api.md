# Dev Log - Attendance Days Dinamis dari API Libur Nasional

Tanggal: 2026-05-13 14:02 WIB  
File:
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/absensi/layouts_absensi/profileHeader.blade.php`

## Ringkasan
- Mengubah statistik header `5 / 22` menjadi dinamis:
  - angka kiri: jumlah hari hadir user pada bulan berjalan (`attendances` dengan `clock_in` terisi)
  - angka kanan: jumlah hari kerja pada bulan berjalan
- Perhitungan hari kerja mengecualikan:
  - Sabtu dan Minggu
  - hari libur/cuti bersama dari API `https://libur.deno.dev/api?year=YYYY`
- Menambahkan cache hasil API per tahun hingga akhir hari untuk mengurangi request berulang.
- Label diperbarui menjadi `Attendance Days (NamaBulan)`.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php -l app/Http/Controllers/AttendanceController.php` passed.
