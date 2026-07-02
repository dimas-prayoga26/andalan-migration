# Dev Log - Nonaktifkan JS DataTable Sementara (Index Absensi)

Tanggal: 2026-05-12  
File utama: `resources/views/absensi/index.blade.php`

## Ringkas Perubahan
- Inisialisasi DataTable pada `#myTable` dinonaktifkan sementara.
- Seluruh event handler DataTable (`order.dt`, `search.dt`, `draw.dt`, dan `resize -> columns.adjust`) dilepas.
- Variabel `attendanceTable` diset jadi `null` agar script lain tetap aman.
- Listener filter company/bulan/tahun diberi guard `if (attendanceTable)` sebelum memanggil `ajax.reload()`.

## Tujuan
- Menghapus sementara semua JS yang terhubung langsung ke tabel absensi tanpa memutus fitur modal/flow JS lain.
