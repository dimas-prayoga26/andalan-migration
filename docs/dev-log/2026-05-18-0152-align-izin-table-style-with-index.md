# Samakan Style Tabel Izin dengan Index

## Tanggal
- 2026-05-18

## Perubahan
- Update `resources/views/absensi/izin.blade.php` untuk menyamakan karakter style tabel dengan `index.blade.php`.
- Menghapus CSS kustom DataTable yang membuat tampilan tabel `izin` berbeda jauh dari `index`, meliputi:
  - custom ukuran font/padding header-body `#myTable`
  - custom layout wrapper DataTable (`#myTable_wrapper .dt-*`)
  - custom styling search/length input
  - custom styling tombol pagination
- Menambahkan style kosong yang konsisten dengan `index`:
  - `#myTable.dataTable tbody td.dataTables_empty { text-align: center !important; }`

## Validasi
- `php artisan view:cache --no-interaction` -> sukses.
