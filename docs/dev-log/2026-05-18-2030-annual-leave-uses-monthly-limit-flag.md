# 2026-05-18 20:30 - Cuti Tahunan Pakai Limit Bulanan dan Update Flag

## Perubahan
- Mengubah perhitungan kartu bulanan di `LeaveRequestController@index`:
  - `Cuti {Bulan Tahun}` sekarang dihitung dari `montly_leave_limit - total_hari_cuti_tahunan_approved_bulan_ini`.
- Mengubah validasi approval di `LeaveRequestController@validateLeaveApprovalLimit`:
  - Untuk `Cuti Tahunan`, approval sekarang juga memvalidasi limit bulanan perusahaan (`meta_data_leave_companies.montly_leave_limit`).
  - Jika durasi pengajuan melebihi sisa limit bulanan, approval ditolak.
- Mengubah sinkronisasi `leave_balances` di `syncAnnualLeaveBalance`:
  - Menambahkan parameter bulan.
  - Menyetel `is_monthly_limit_used = true` jika ada `Cuti Tahunan` berstatus `approved` pada bulan tersebut.
- Memperbarui pemanggilan sinkronisasi tahunan setelah `destroy` dan `updateStatus` agar mengirim tahun + bulan.

## Dampak
- `is_monthly_limit_used` tidak lagi hanya relevan untuk cuti khusus, tetapi ikut mencerminkan penggunaan `Cuti Tahunan` pada bulan berjalan (sesuai requirement baru).
- Pengurangan quota tahunan tetap terjadi saat status sudah `approved`.
