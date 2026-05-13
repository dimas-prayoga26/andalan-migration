# Dev Log - Fix Datatable Name dari Employee Profiles

Tanggal: 2026-05-13 10:12 WIB  
File: `app/Http/Controllers/AttendanceController.php`

## Ringkasan
- Sumber `staff_name` di endpoint `datatable()` dipindah dari `users` ke tabel `employee_profiles`.
- Menambahkan fallback nama ke `username`, lalu `email`, jika `employee_profiles.name` kosong.
- Menghapus `dd($attendanceLogsByAttendanceId)` yang menghentikan response JSON datatable.

## Dampak
- Data nama staff di tabel logs sekarang mengikuti data profil karyawan.
- Endpoint datatable kembali mengembalikan JSON normal (tidak berhenti di debug dump).
