# Fix Layout Card Summary dan Judul List Pengajuan di Halaman Izin

## Tanggal
- 2026-05-18

## Perubahan
- `resources/views/absensi/izin.blade.php`
  - Merapikan struktur `card-header` pada section Logs.
  - Menampilkan kembali judul `Logs` di atas summary card.
  - Memisahkan `List Pengajuan` dari container flex card summary agar tidak terdorong ke kanan.
  - `List Pengajuan` diposisikan sebagai judul section di bawah summary card (`mt-3`, center).
  - Filter karyawan dipindahkan ke baris terpisah di bawah judul list agar layout tidak bentrok dengan carousel card.

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
