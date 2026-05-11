# 2026-05-08 16:45 - Attendance, Leave, Geocoding

## Ringkasan
- Membuat otomasi sinkronisasi saldo cuti bulanan lewat command dan scheduler.
- Memperbaiki modul lembur (otorisasi aksi dan perapihan tampilan aksi).
- Merevisi aturan status absensi (jam kerja, grace period telat, normalisasi label status).
- Menambahkan modal detail absensi di halaman index.
- Menambahkan fitur geocoding absensi (alamat terstruktur, provider geocoding, dan tampilan peta di detail).

## Commit
- `449288f` - Sinkronisasi saldo cuti, scheduler command, update alur izin.
- `e625002` - Perubahan controller dan view lembur.
- `4ff675a` - Perubahan aturan absensi + migration terkait threshold/status.
- `d796241` - Penambahan tampilan detail absensi.
- `e0e3c3b` - Penambahan geocoding absensi end-to-end.

## Dampak
- Data absensi punya detail lokasi lengkap (alamat + koordinat + provider geocoding).
- Status absensi konsisten ke label bisnis (`Masuk`, `Terlambat`).
- Saldo cuti bisa disinkronkan terjadwal tanpa proses manual.
