# Adjust Seeder: Cuti Khusus vs Cuti Tahunan

## Ringkasan
- Seeder disesuaikan agar `Cuti Khusus` tidak ikut perhitungan saldo `Cuti Tahunan`.

## Perubahan
- File: `database/seeders/LeaveTypeSeeder.php`
  - `Cuti Khusus`:
    - `accrual_method` diubah menjadi `yearly`
    - `monthly_accrual_rate` diubah menjadi `0`

- File: `database/seeders/LeaveBalanceSeeder.php`
  - Hanya pakai leave type `Cuti Tahunan` untuk seed saldo tahunan.
  - Hapus fallback ke `Cuti Khusus` jika `Cuti Tahunan` tidak ditemukan.
  - Perhitungan bulan terpakai (`resolveUsedLeaveMonths`) sekarang hanya baca request `Cuti Tahunan`.

## Dampak
- Penggunaan `Cuti Khusus` (contoh: menikah/melahirkan) tidak memotong jatah `Cuti Tahunan`.
- Data seed saldo tahunan menjadi lebih konsisten dengan aturan bisnis terbaru.
