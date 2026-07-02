# Modal Detail Annual dan Sick Leave dari Card

## Ringkasan

- Menambahkan trigger modal pada kartu `Annual Leave` dan `Sick Leave` di halaman Leave Request.
- Mengubah modal `annualLeaveLabel` dan `sickLabel` agar menampilkan detail dari `$leaveTracker`, termasuk total tahunan, pemakaian bulan berjalan, breakdown tanggal, dan limit bulanan annual leave.
- Menghapus data contoh hardcoded pada modal annual/sick leave.

## Verifikasi

- Menambahkan assertion view untuk trigger modal dan data tracker pada modal detail leave.
