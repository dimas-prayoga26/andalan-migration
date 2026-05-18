# Add Monthly Limit Usage Flag on Leave Balances

## Ringkasan
- Menambahkan kolom boolean baru `is_monthly_limit_used` pada tabel `leave_balances` untuk menandai limit bulanan cuti khusus sudah terpakai (`true`) atau belum (`false`).

## Perubahan
- File: `database/migrations/2026_05_18_064319_add_is_monthly_limit_used_to_leave_balances_table.php`
  - Tambah kolom `is_monthly_limit_used` (`boolean`, default `false`) pada `leave_balances`.
  - Rollback menghapus kolom tersebut.

- File: `app/Models/LeaveBalance.php`
  - Tambah cast:
    - `is_monthly_limit_used => boolean`

- File: `app/Http/Controllers/LeaveRequestController.php`
  - Setelah `store()` untuk `Cuti Khusus`, sinkronisasi flag bulanan.
  - Setelah `destroy()` dan `updateStatus()`, jika tipe cuti adalah `Cuti Khusus`, sinkronisasi flag bulanan.
  - Tambah helper:
    - `syncMonthlySpecialLeaveLimitFlag(string $employeeId, int $year, int $month)`
    - `resolveSpecialLeaveTypeId(): ?string`
    - `isSpecialLeaveTypeId(string $leaveTypeId): bool`

- File: `database/seeders/LeaveBalanceSeeder.php`
  - Saat seed annual balance, set default `is_monthly_limit_used` ke `false`.

## Dampak
- Sistem punya indikator eksplisit di `leave_balances` untuk status penggunaan limit bulanan cuti khusus.
- Nilai flag akan ikut diperbarui otomatis mengikuti event utama request (`store`, `updateStatus`, `destroy`).
