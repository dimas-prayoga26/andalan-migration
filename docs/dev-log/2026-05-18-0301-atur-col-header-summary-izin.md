# Atur Col Header Summary Izin (2-8-2)

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Ubah layout `card-header` section logs menjadi grid bootstrap:
    - kiri: `col-lg-2` -> `Logs`
    - tengah: `col-lg-8` -> carousel summary cards
    - kanan: `col-lg-2` -> text `List Pengajuan`
  - Tambahkan responsive behavior:
    - di mobile tetap stack (`col-12`)
    - `List Pengajuan` kanan di desktop (`text-lg-end`) dan kiri di mobile (`text-start`)

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
