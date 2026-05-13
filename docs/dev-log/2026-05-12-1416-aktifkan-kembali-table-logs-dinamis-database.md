# Dev Log - Aktifkan Kembali Table Logs Dinamis dari Database

Tanggal: 2026-05-12  
File: `resources/views/absensi/index.blade.php`

## Ringkasan
- Tabel `#tableLogs` dikembalikan menjadi dinamis dengan AJAX ke route `absensi.datatable`.
- Data statis pada `<tbody>` dihapus (sekarang `<tbody></tbody>`).
- Inisialisasi DataTable disesuaikan dengan kolom aktif:
  - `No`
  - `Nama Staff`
  - `Masuk`
  - `Pulang`
  - `Nama PT`
  - `Action`
- Kolom `Action` mempertahankan komponen dropdown `Options`, dengan menu `Detail`.
- Variabel `attendanceTable` diperbaiki agar tidak lagi ke-reset ke `null` setelah DataTable diinisialisasi.

## Dampak
- Data tabel sekarang diambil dari database sesuai filter (`company_id`, `month`, `year`) jika filter tersedia.
- Klik `Detail` pada dropdown action kembali memanggil modal detail berdasarkan row data dari DataTable.
