# Dev Log - Ubah Formula Working Days ke Weekday Minus Cuti Bersama

Tanggal: 2026-05-13 14:09 WIB  
File: `app/Http/Controllers/AttendanceController.php`

## Ringkasan
- Formula `working days` diubah sesuai klarifikasi:
  - hitung Senin-Jumat (weekday)
  - Sabtu/Minggu tidak dihitung
  - hanya tanggal `cuti bersama` yang dikurangi dari weekday
- Hari libur nasional selain cuti bersama tidak lagi dikurangi.
- Parser API `libur.deno.dev` difilter untuk mengambil tanggal `cuti bersama` saja.
