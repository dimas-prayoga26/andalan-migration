# 2026-05-21 10:15 - Revamp Absensi Report, Modal Baru, dan Skema Attendance

## Perubahan
- Menambahkan alur halaman report absensi:
  - Route `GET /absensi/reports` diarahkan ke `ReportController@index` dengan nama route `absensi.reports`.
  - Menambahkan menu `Report` pada navbar absensi.
  - Menambahkan view `resources/views/absensi/report.blade.php` sebagai basis halaman report.
- Refactor tampilan dan alur modal absensi di `resources/views/absensi/index.blade.php`:
  - Memisahkan state `Clock In` dan `Clock Out` ke dua modal berbeda (`Attendance Confirmation` dan `End of Shift`).
  - Tombol `Mulai Verifikasi` dipindah di bawah status; setelah verifikasi sukses tombol hilang dan diganti pesan: `Verifikasi berhasil. Kamu bisa lanjut submit.`
  - Menghapus tombol lama `Sudah Pulang`; submit dilakukan dari modal masing-masing.
  - Menyesuaikan ikon untuk bagian `Attendance Confirmation`, `End of Shift`, dan `Time to Recharge!`.
  - Menonaktifkan tombol kartu `Clock In` jika sudah check in hari ini, dan `Clock Out` jika sudah check out hari ini.
- Menyesuaikan ringkasan kartu presensi:
  - Nilai `Distance` sebelum absen ditampilkan `- KM`.
  - Nilai `Distance` setelah check in diambil dari `attendance_logs.distance_in` (ditampilkan dalam KM).
  - Jam real-time dan validasi warna tetap aktif sesuai status keterlambatan.
- Perubahan skema dan penyimpanan log absensi:
  - Menambah migration `2026_05_21_094148_update_attendance_distance_columns_and_drop_late_grace_minutes.php`.
  - Mengubah `attendance_logs.distance` menjadi `distance_in`.
  - Menambahkan kolom `attendance_logs.distance_out`.
  - Menghapus kolom `rules_of_attendaces.late_grace_minutes`.
- Penyesuaian backend attendance:
  - `AttendanceController` menyimpan `late_minutes` berdasarkan selisih terhadap `office_start_time` (minimal `0`).
  - Saat `clock_out`, sistem mengisi `work_hours`, `attendance_logs.location_out`, dan `attendance_logs.distance_out`.
  - Data datatable attendance memakai `distance_in` dan `distance_out`.
  - `ReportController` ikut dibersihkan dari referensi `late_grace_minutes`.
- Perbaikan seeder:
  - Menghapus pengisian `late_grace_minutes` di `RulesOfAttendacesSeeder` agar seeder kompatibel dengan skema baru.
- Perbaikan bug perhitungan `work_hours`:
  - Perhitungan memakai nilai mentah `clock_in` dari database (`TIME`) + tanggal absensi.
  - Menjaga hasil tidak negatif untuk menghindari angka jam kerja tidak masuk akal.

## File Terdampak
- `routes/web.php`
- `app/Http/Controllers/AttendanceController.php`
- `app/Http/Controllers/ReportController.php`
- `resources/views/absensi/layouts_absensi/profileNavbar.blade.php`
- `resources/views/absensi/index.blade.php`
- `resources/views/absensi/report.blade.php`
- `database/migrations/2026_05_21_094148_update_attendance_distance_columns_and_drop_late_grace_minutes.php`
- `database/seeders/RulesOfAttendacesSeeder.php`

## Dampak
- Error seeder akibat kolom `late_grace_minutes` hilang terselesaikan.
- Alur verifikasi dan submit absensi menjadi lebih jelas karena dipisah per modal.
- Status tombol check in/check out lebih aman dari double-submit.
- Penyimpanan jarak masuk/keluar kini terpisah (`distance_in`/`distance_out`) untuk kebutuhan report yang lebih presisi.
