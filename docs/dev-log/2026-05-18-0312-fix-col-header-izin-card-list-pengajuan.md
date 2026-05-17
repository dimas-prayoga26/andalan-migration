# Fix Col Header Izin Agar Card dan List Pengajuan Tidak Tabrakan

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Ubah layout header section logs:
    - `Logs` tetap di `col-lg-2`
    - area kanan jadi `col-lg-10`
    - dalam area kanan pakai flex (`carousel` fleksibel + text `List Pengajuan` di sisi kanan)
  - `List Pengajuan` dibuat `white-space: nowrap` agar tidak pecah baris aneh.
  - Tambah `style="min-width:0;"` pada wrapper carousel agar konten flex tidak overflow.

## Hasil
- Card summary tidak lagi ketabrak/terpotong oleh text `List Pengajuan`.
- Posisi title `List Pengajuan` tetap rapi di kanan desktop dan turun natural di mobile.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
