# Update Cuti Khusus vs Cuti Tahunan

## Ringkasan
- Perhitungan saldo tahunan sekarang hanya untuk **Cuti Tahunan**.
- **Cuti Khusus** tidak lagi mengurangi saldo **Cuti Tahunan**.

## Perubahan Teknis
- File: `app/Http/Controllers/LeaveRequestController.php`
  - Validasi saldo tahunan di `store()`:
    - sebelumnya: berlaku untuk `cuti khusus` dan `cuti tahunan`
    - sekarang: hanya untuk `cuti tahunan`
  - Method `calculateStaffLeaveSummary()`:
    - `earned_quota` hanya dari leave type `cuti tahunan`
    - `used_annual_balance` hanya menjumlah request leave type `cuti tahunan`

## Dampak
- Request `Cuti Khusus` (contoh: melahirkan, menikah) tidak memotong jatah cuti tahunan.
- Card tahunan tetap merepresentasikan pemakaian cuti tahunan saja.
