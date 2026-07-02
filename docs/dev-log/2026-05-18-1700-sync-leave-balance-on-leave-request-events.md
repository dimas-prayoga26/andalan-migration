# Sync Leave Balance on Leave Request Events

## Ringkasan
- Menambahkan sinkronisasi otomatis tabel `leave_balances` untuk `Cuti Tahunan` setiap ada perubahan request izin.

## Perubahan
- File: `app/Http/Controllers/LeaveRequestController.php`
  - Setelah `store()` sukses untuk `Cuti Tahunan`, panggil sinkronisasi saldo tahunan.
  - Setelah `updateStatus()`, panggil sinkronisasi saldo tahunan.
  - Setelah `destroy()`, panggil sinkronisasi saldo tahunan.
  - Tambah helper `syncAnnualLeaveBalance(string $employeeId, int $year)`:
    - Ambil `leave_type_id` untuk `Cuti Tahunan`.
    - Hitung `used_quota` dari `leave_requests` tahun berjalan dengan status selain `rejected/refused`.
    - Update `remaining_quota = earned_quota - used_quota` (minimal 0).
    - `updateOrCreate` pada `leave_balances` berdasarkan `employee_id + leave_type_id + period_year`.

## Dampak
- Nilai `used_quota` dan `remaining_quota` di `leave_balances` menjadi konsisten dengan data request aktual.
- Kasus seperti yang diminta (misalnya `used_quota=2` dan `remaining_quota=1`) akan tersinkron otomatis setelah aksi request/status.
