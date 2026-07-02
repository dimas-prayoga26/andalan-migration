# Dev Log - Aktifkan Button Presensi Check in Buka Modal Absen

Tanggal: 2026-05-12  
File: `resources/views/absensi/index.blade.php`

## Ringkasan
- Tombol `Presensi Check in` sekarang difungsikan untuk membuka modal absen.
- Menambahkan atribut Bootstrap modal trigger pada tombol:
  - `data-bs-toggle="modal"`
  - `data-bs-target="#attendanceActionModal"`
