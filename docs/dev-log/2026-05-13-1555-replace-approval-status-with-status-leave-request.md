# Dev Log - Replace approval_status with status on Leave Request

Tanggal: 2026-05-13 15:55 WIB  
File:
- `app/Models/LeaveRequest.php`
- `app/Http/Controllers/LeaveRequestController.php`
- `database/seeders/LeaveBalanceSeeder.php`
- `resources/views/absensi/izin.blade.php`

## Ringkasan
- Mengganti pemakaian field `approval_status` menjadi `status` pada modul izin/cuti.
- Menghapus `approval_status` dari fillable model `LeaveRequest`.
- Menyesuaikan seluruh query, mapping datatable, detail, dan update status di `LeaveRequestController` agar memakai kolom `status`.
- Menyesuaikan payload AJAX di view izin dari `approval_status` ke `status`.
- Menyesuaikan perhitungan sisa cuti di `LeaveBalanceSeeder` agar filter status menggunakan kolom `status`.

## Catatan
- `approval_status` pada modul lembur (`AttendanceOvertime`) belum diubah karena konteks tabel/fitur berbeda.

## Validasi
- `vendor/bin/pint --dirty --format agent` passed.
- `php -l` untuk file controller/model/seeder terkait passed.
