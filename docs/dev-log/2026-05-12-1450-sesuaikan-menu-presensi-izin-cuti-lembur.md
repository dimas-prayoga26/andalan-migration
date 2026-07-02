# Dev Log - Sesuaikan Menu Presensi, Izin/Cuti, dan Lembur

Tanggal: 2026-05-12  
File: `resources/views/absensi/index.blade.php`

## Ringkasan
- Menu yang difungsikan dulu sesuai request:
  - `Presensi` -> `route('absensi')`
  - `Izin / Cuti` -> `route('absensi.izin')`
  - `Lembur` -> `route('absensi.lembur')`
- `Overview` dan `List` sementara dibuat non-aktif (`disabled`) sambil menunggu route baru.
