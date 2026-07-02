# Dev Log - Duplikasi Statistik Header Berdasarkan Role

Tanggal: 2026-05-13 14:47 WIB  
File:
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/absensi/layouts_absensi/profileHeader.blade.php`

## Ringkasan
- Membagi statistik header menjadi 2 blok dengan pengkondisian role.
- Blok `staff` tetap menampilkan:
  - `Attendance Days`
  - `On Going Task`
  - `Task Complete`
- Blok `board of directors` dan `superuser` menampilkan statistik berbeda:
  - `Staff Presence (Today)`
  - `On Going Attendance (Today)`
  - `Attendance Complete (Today)`

## Detail Data Management
- Data management dihitung di controller berdasarkan scope:
  - `board of directors`: hanya company aktif user
  - `superuser`: seluruh employee
- Variabel baru view:
  - `profileStatsMode`
  - `managementTotalEmployeesCount`
  - `managementPresentTodayCount`
  - `managementOnGoingTodayCount`
  - `managementCompletedTodayCount`

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php -l app/Http/Controllers/AttendanceController.php` passed.
