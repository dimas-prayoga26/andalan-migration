# Task Monitoring Bulan Berjalan

Tanggal: 2026-08-19 16:30 WIB

## Ringkasan

- Membatasi tabel Task Monitoring pada menu PIC Attendance > Task dan Director Attendance > Task agar hanya menampilkan task di bulan berjalan (berdasarkan `due_date`, fallback ke `start_date` lalu `created_at` kalau `due_date` kosong).
- Mengubah urutan kolom Due Date dari terlama ke terbaru (`ASC`) menjadi terbaru ke terlama (`DESC`).
- Perubahan diterapkan di kedua controller: `PicAttendanceTaskController::datatable` dan `DirectorAttendanceTaskController::datatable`.

## File yang Berubah

- `app/Http/Controllers/PicAttendance/PicAttendanceTaskController.php`
- `app/Http/Controllers/DirectorAttendance/DirectorAttendanceTaskController.php`

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/PicAttendanceModuleTest.php tests/Feature/DirectorAttendanceModuleTest.php`
- `vendor/bin/pint --dirty --format agent`

Hasil terakhir: `5 passed (381 assertions)`.
