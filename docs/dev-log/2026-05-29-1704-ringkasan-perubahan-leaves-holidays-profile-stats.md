# Ringkasan Perubahan Harian (29 Mei 2026)

## Commit Hari Ini

1. `c6ef7a0` - Leaves & Sick Done
2. `0834e99` - FIxing scroll
3. `0b644c1` - Fixed
4. `94e813e` - Fixed

---

## 1) Leaves & Sick

- Mengubah halaman izin agar menggunakan data database:
  - file utama: `resources/views/absensi/izin.blade.php`
- Menampilkan data eligibility staff secara dinamis:
  - nama staff dan supervisor,
  - tanggal bergabung dan masa kerja,
  - saldo cuti,
  - penggunaan cuti tahunan dan bulanan,
  - pengajuan pending, approved, dan rejected.
- Mengaktifkan form pengajuan izin melalui AJAX:
  - pilihan jenis izin,
  - pilihan subtipe untuk `Special Leave`,
  - rentang tanggal,
  - alasan,
  - handover notes,
  - lampiran maksimal 1 MB.
- Menambahkan validasi backend:
  - `Special Leave` wajib memilih subtipe yang aktif dan sesuai dengan jenis cuti,
  - `Sick Leave` wajib memiliki lampiran,
  - lampiran hanya menerima JPG, JPEG, PNG, atau PDF maksimal 1 MB.
- Menambahkan riwayat pengajuan berbentuk timeline dan filter tahun berdasarkan tanggal bergabung staff.
- Menambahkan seeder histori pengajuan:
  - file: `database/seeders/LeaveRequestHistorySeeder.php`

---

## 2) Penyederhanaan Konfigurasi Cuti

- Menghapus penggunaan konfigurasi cuti per perusahaan:
  - model dihapus: `app/Models/MetaDataLeaveCompany.php`
  - seeder dihapus: `database/seeders/MetaDataLeaveCompanySeeder.php`
  - migration baru: `database/migrations/2026_05_26_092149_drop_meta_data_leave_companies_table.php`
- Menghapus flag lama pada saldo cuti:
  - kolom: `leave_balances.is_monthly_limit_used`
  - migration baru: `database/migrations/2026_05_26_091108_drop_is_monthly_limit_used_from_leave_balances_table.php`
- Menyederhanakan `LeaveBalanceSeeder` agar kuota tahunan menggunakan nilai default.
- Menambahkan role `admin` pada `UserSeeder`.
- Membatasi endpoint hapus pengajuan izin agar hanya dapat digunakan oleh role `admin`.
- Menghapus endpoint lama izin yang tidak lagi dipakai UI:
  - datatable,
  - detail,
  - preview attachment,
  - update status.

---

## 3) Perbaikan Horizontal Scroll Report

- Menyesuaikan overflow tabel report agar dapat digeser secara horizontal pada desktop dan mobile:
  - file: `resources/views/absensi/report.blade.php`
- Menambahkan `-webkit-overflow-scrolling: touch` untuk interaksi scroll mobile.

---

## 4) Kalender Hari Libur Absensi

- Menambahkan tabel lokal hari libur:
  - migration: `database/migrations/2026_05_29_080932_create_attendances_holidays_table.php`
  - model: `app/Models/AttendanceHoliday.php`
  - seeder: `database/seeders/AttendanceHolidaySeeder.php`
- Mengisi data hari libur nasional dan cuti bersama tahun 2026 melalui seeder.
- Mengubah kalender absensi agar membaca hari libur dari database, tidak lagi mengambil data langsung dari API eksternal saat halaman dibuka.
- Menambahkan retry inisialisasi kalender apabila `FullCalendar` belum tersedia.
- Memberikan warna khusus pada event attendance yang memiliki exception:
  - `early_departure`,
  - `late_arrival`.
- Mengubah default status `attendance_exceptions` menjadi `approved`:
  - migration: `database/migrations/2026_05_29_091705_alter_default_status_on_attendance_exceptions_table.php`

---

## 5) Statistik Staff Profile Header

- Menambahkan perhitungan statistik staff dari database melalui:
  - file: `app/View/Composers/AbsensiProfileComposer.php`
- Menampilkan statistik dinamis:
  - total `Late In` bulan berjalan,
  - total approved `Leaves & Sick` bulan berjalan,
  - persentase kehadiran minggu berjalan,
  - persentase `On-Time` minggu berjalan,
  - progress kehadiran bulanan selama satu tahun,
  - selisih progress bulan berjalan terhadap bulan sebelumnya.
- Menggunakan tabel `attendances_holidays` untuk menghitung hari kerja efektif.
- Menyesuaikan profile header desktop dan mobile:
  - file: `resources/views/absensi/layouts_absensi/profileHeader.blade.php`
- Menyesuaikan grafik progress agar membaca data backend:
  - file: `public/assets/js/dashboard/profile.js`

---

## 6) Test Coverage

- Menambahkan feature test:
  - `tests/Feature/LeaveHistoryYearFilterTest.php`
  - `tests/Feature/LeaveRequestDestroyAuthorizationTest.php`
  - `tests/Feature/UserSeederRoleTest.php`
  - `tests/Feature/AttendanceExceptionStoreTest.php`
  - `tests/Feature/AbsensiProfileComposerStaffStatsTest.php`

---

## Catatan Operasional

- Jalankan migration terbaru setelah pull perubahan:
  - `php artisan migrate`
- Jalankan seeder apabila data hari libur 2026 belum tersedia:
  - `php artisan db:seed --class=AttendanceHolidaySeeder`
- Jika tampilan frontend belum berubah, lakukan hard refresh browser (`Ctrl + F5`).
