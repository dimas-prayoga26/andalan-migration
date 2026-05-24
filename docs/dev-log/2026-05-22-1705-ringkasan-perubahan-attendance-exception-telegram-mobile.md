# Ringkasan Perubahan Harian (22 Mei 2026)

## Commit Hari Ini

1. `996d9c2` - Progress Attendance Exception
2. `ceabb71` - Report
3. `2b4dee5` - FIxed
4. `e518fec` - Fixed

---

## 1) Attendance Exception (Backend + Database)

- Menambahkan tabel baru `attendance_exceptions`:
  - file: `database/migrations/2026_05_22_025858_create_attendance_exceptions_table.php`
- Menambahkan model:
  - file: `app/Models/AttendanceException.php`
- Menyesuaikan alur `storeException` di `AttendanceController`:
  - Validasi `type`, `note`, `from_time`, `to_time`, `exception_date`.
  - Membuat/mengambil data `attendances` hari terkait.
  - Menyimpan exception dengan status `approved`.
  - Mengembalikan response summary (`summary_time_range`, `summary_variance`) untuk update UI.
- Menyesuaikan relasi/field pada model `Attendance` untuk kebutuhan exception.

---

## 2) Attendance Logs & Rules Attendance

- Refactor skema log absensi:
  - Dari pola `location_in/location_out` + `distance_in/distance_out`
  - Menjadi pola single row per event dengan `type` + `location` + `distance`.
  - file utama: `database/migrations/2026_05_05_065440_create_attendance_logs_table.php`
- Pembersihan migration terkait aturan lama `late_grace_minutes`:
  - file: `database/migrations/2026_05_21_094148_update_attendance_distance_columns_and_drop_late_grace_minutes.php`

---

## 3) Report Presensi

- Mengaktifkan kembali fungsi report melalui `ReportController`:
  - file: `app/Http/Controllers/ReportController.php`
- Menyesuaikan route report:
  - file: `routes/web.php`
- Menyesuaikan view report:
  - file: `resources/views/absensi/report.blade.php`

---

## 4) Verifikasi Telegram (Optimasi API Call)

- Menambahkan kolom `is_telegram_verified` pada tabel `users`:
  - file: `database/migrations/2026_05_22_092047_add_is_telegram_verified_to_users_table.php`
- Menambahkan cast boolean di model `User`:
  - file: `app/Models/User.php`
- Menyesuaikan flow verifikasi Telegram di `AttendanceController`:
  - Jika `is_telegram_verified = true`, tidak hit API `getUpdates` lagi.
  - Tetap cek relasi `employee.telegramUser`.
  - Jika flag true tapi data telegram user hilang, flag di-reset ke false dan minta verifikasi ulang.
- Pengiriman notifikasi Telegram saat clock in/out hanya jalan jika:
  - user staff,
  - `is_telegram_verified = true`,
  - data `telegram_users` tersedia.

---

## 5) Revamp UI Halaman Absensi (Presensi)

- Refactor besar `resources/views/absensi/index.blade.php`:
  - Penyesuaian modal Attendance Confirmation / End of Shift / Attendance Exception.
  - Penyesuaian state tombol clock in/clock out.
  - Integrasi feedback submit exception (termasuk alert sukses/gagal).
  - Penyesuaian summary Time/Variance Exception.
- Menambahkan logic disable `Clock Out` jika exception tipe `early_departure`.
- Menambahkan tampilan slider mobile (swipe) untuk 3 card presensi.
- Penyesuaian UX mobile:
  - Sebelum clock in: card End of Shift disembunyikan.
  - Setelah clock in: card Attendance Confirmation disembunyikan, End of Shift ditampilkan.

---

## 6) Kalender & Activity Schedule

- Penyesuaian inisialisasi calendar:
  - file: `public/assets/js/plugins-init/fullcalendar-init.js`
- Penyesuaian view Activity Schedule:
  - file: `resources/views/activity_schedule/index.blade.php`

---

## 7) Route dan Layout Pendukung

- Penyesuaian route absensi/report/exception:
  - file: `routes/web.php`
- Penyesuaian common JS include:
  - file: `resources/views/layouts/commonjs.blade.php`

---

## Catatan Operasional

- Jalankan migration terbaru setelah pull perubahan:
  - `php artisan migrate`
- Jika tampilan frontend belum update, lakukan hard refresh browser (`Ctrl + F5`).
- Token bot Telegram sempat terekspos di percakapan; disarankan rotate token bot untuk keamanan.
