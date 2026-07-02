# Force Summary Cards Below Card Title on Izin Header

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Tambah class CSS `.izin-card-header { display: block !important; }`.
  - Pasang class `izin-card-header` pada elemen `.card-header` section List Pengajuan.

## Alasan
- Theme global `card-header` menggunakan flex, sehingga title dan cards tetap sejajar.
- Override ke block memastikan cards selalu muncul di bawah title.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
