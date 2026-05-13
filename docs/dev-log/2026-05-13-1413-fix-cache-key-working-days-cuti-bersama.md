# Dev Log - Fix Cache Key Working Days Cuti Bersama

Tanggal: 2026-05-13 14:13 WIB  
File: `app/Http/Controllers/AttendanceController.php`

## Ringkasan
- Memperbaiki nilai `Attendance Days` yang masih menampilkan hasil lama karena cache key lama.
- Mengganti cache key perhitungan libur menjadi khusus `cuti-bersama` dengan versi baru:
  - dari: `libur-deno:indonesia:{year}`
  - ke: `libur-deno:indonesia:cuti-bersama:v2:{year}`

## Dampak
- Perhitungan hari kerja bulan berjalan langsung memakai rumus terbaru:
  - weekday (Senin-Jumat)
  - dikurangi cuti bersama saja.
