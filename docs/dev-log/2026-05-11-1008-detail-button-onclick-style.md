# 2026-05-11 10:08 - Detail Button Pakai OnClick Style

## Perubahan
- Tombol `Detail` di DataTable absensi diubah ke pola `onclick` langsung:
  - `onclick="onClickAttendanceDetail(this)"`.
- Menambahkan handler global `onClickAttendanceDetail` untuk ambil row DataTable berdasarkan tombol yang diklik, lalu memanggil `showAttendanceDetail`.
- Menghapus event delegation lama `$('#myTable tbody').on('click', '.attendance-detail-btn', ...)`.

## File Terdampak
- `resources/views/absensi/index.blade.php`
