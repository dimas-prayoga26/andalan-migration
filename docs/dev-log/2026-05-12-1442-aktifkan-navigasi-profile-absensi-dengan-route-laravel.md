# Dev Log - Aktifkan Navigasi Profile Absensi dengan Route Laravel

Tanggal: 2026-05-12  
File:
- `resources/views/absensi/index.blade.php`
- `routes/web.php`

## Ringkasan
- Mengganti link statis HTML pada section profile navigation menjadi tombol `absensi-tab-btn` berbasis route Laravel (`data-href`).
- Menambahkan class `absensi-tabs` agar pola styling/scroll tab konsisten dengan halaman absensi lain.
- Menambahkan route baru untuk halaman Perjalanan Dinas:
  - `GET /absensi/dinas` -> `view('absensi.dinas')`
  - route name: `absensi.dinas`

## Dampak
- Semua item menu di section tersebut sekarang bisa diklik dan berpindah halaman via route aplikasi.
- Status active tab mengikuti `request()->routeIs(...)` sesuai route aktif.
