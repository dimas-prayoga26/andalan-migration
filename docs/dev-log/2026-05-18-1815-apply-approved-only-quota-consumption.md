# Change Quota Consumption to Approved Only

## Ringkasan
- Mengubah logika kuota cuti agar status `pending` tidak mengurangi saldo.
- `used_quota`, `remaining_quota`, dan `is_monthly_limit_used` sekarang dihitung dari request berstatus `approved` saja.

## Perubahan
- File: `app/Http/Controllers/LeaveRequestController.php`
  - Ganti seluruh query perhitungan kuota dari `NOT IN (rejected, refused)` menjadi `status = approved`.
  - Hapus sinkronisasi kuota saat `store()` (karena request baru statusnya `pending`).
  - Tambah validasi saat `updateStatus()` ke `approved`:
    - Cek sisa `Cuti Tahunan` sebelum approval.
    - Cek `Cuti Khusus` bulanan agar tidak lebih dari 1 approval di bulan yang sama.
  - Tambah helper:
    - `validateLeaveApprovalLimit(LeaveRequest $leaveRequest)`

- File: `database/seeders/LeaveBalanceSeeder.php`
  - Perhitungan bulan terpakai hanya dari leave request berstatus `approved`.

## Dampak
- Request `pending` tidak lagi mengubah kuota di kartu ringkasan.
- Kuota baru terpotong saat director/superuser melakukan approval.
- Jika kuota tidak cukup saat approval, status tidak akan berubah dan API mengembalikan error validasi.
