# Ringkasan Perubahan Office Location, Attendance, dan Data Employee

Tanggal: 2026-07-07 09:30 WIB

## Ringkasan

- Merombak konsep `office_locations` dari lokasi yang terikat company menjadi lokasi global.
- Lokasi kerja employee sekarang diambil dari `employee_deployments.current_office_location_id`.
- Geofencing attendance memakai office location milik employee, bukan office location dari company.
- Data master office location disederhanakan ke lokasi kota seperti `Jakarta` dan `Yogyakarta`.
- Form Data Employee bagian `Branch / Office` menampilkan nama lokasi dari `office_locations.name`.
- Attendance rule sekarang mengikuti `office_location_id` global, tidak lagi mengikuti `companies_id`.
- Dashboard Attendance Confirmation disamakan dengan menu Attendance: setelah GPS berhasil dibaca dan Telegram valid, staff tetap bisa Clock In/Clock Out walaupun berada di luar radius kantor.
- Backend attendance tetap menyimpan koordinat, IP, dan hasil radius untuk kebutuhan audit.
- List Data Employee dibatasi ke staff aktif dan menyembunyikan akun super admin.
- Urutan list Data Employee mengikuti company, PIC, lalu nama employee.
- Field PIC / Penanggung Jawab pada Update Employee diperbaiki agar employee yang menjadi PIC dirinya sendiri tetap terselect.
- Leonie Putri Andhari disesuaikan agar memiliki posisi `Administrator` dan bisa tampil sebagai PIC dirinya sendiri.
- Toggle Status Karyawan pada Update Employee diperbaiki agar bisa mengubah `users.is_active` dari aktif ke nonaktif.
- Admin Attendance dan PIC Attendance memakai scope staff aktif.
- Admin Attendance tidak menampilkan super admin, Lukman Prabowo, Rully Priyatno, dan Hilmi Ulwan pada Attendance Details/recap.
- Detail Admin Attendance memakai profile picture dengan fallback default jika tidak ada foto.
- PIC Attendance Logs harian menampilkan semua staff aktif di bawah PIC; staff yang belum absen tetap tampil dengan nilai `-` pada kolom clock in, clock out, note, working hours, dan attachment.
- Attendance Logs otomatis kembali `-` untuk hari baru sampai ada data attendance pada tanggal tersebut.
- Error View More PIC Attendance karena kolom `attachment_path` pada tabel `employees` diperbaiki.
- Tampilan chart/progress Attendance Detail diperbaiki agar tidak berantakan.
- Update Leave Request untuk izin sakit yang sudah final decision approved tetap bisa update attachment/image saja; field dari Leave Type sampai Handover Notes dibuat readonly.
- Seeder office location, attendance rule, dan user legacy disesuaikan dengan konsep global office location.

## Migration

- Menambahkan migration `2026_07_06_093538_consolidate_office_locations_into_global_locations`.
- Menambahkan migration `2026_07_06_093540_detach_office_locations_and_attendance_rules_from_companies`.
- Migration production sudah dijalankan dan statusnya `DONE`.

## Dampak Schema

- `office_locations.company_id` tidak lagi menjadi sumber relasi lokasi kerja.
- `rules_of_attendaces.companies_id` dilepas dari aturan absensi.
- Relasi `officeLocation.company`, `company.officeLocations`, `company.attendanceRules`, dan `rulesOfAttendace.company` tidak lagi dipakai.
- Sumber lokasi aktif employee menjadi `employee_deployments.current_office_location_id`.

## Dampak Geofencing

- Staff company KMA/RNB/Niskala/Trah tetap bisa memiliki lokasi kerja Jakarta atau Yogyakarta sesuai deployment masing-masing.
- Company tidak lagi menentukan titik absen.
- Case staff company KMA yang bekerja tetap di Jakarta dapat diselesaikan dengan mengatur `current_office_location_id` employee ke office location Jakarta.
- Radius dan titik kantor tetap dibaca dari `office_locations`.

## Catatan Production

- Sebelum deploy wajib backup database.
- Deploy production memakai:
  - `git pull origin develop`
  - `composer install --no-dev --optimize-autoloader`
  - `php artisan migrate --force`
  - `php artisan optimize:clear`
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
  - `php artisan queue:restart` jika queue aktif
- Jangan menjalankan `php artisan db:seed` di production kecuali sudah dipastikan aman/idempotent.
- Jika production memiliki local changes seperti `.agents`, `AGENTS.md`, `composer.lock`, atau `public/error_log`, bersihkan/restore dulu sebelum `git pull`.

## Verifikasi

- `php artisan test --compact tests/Feature/DashboardStructureTest.php tests/Feature/AttendanceCalendarModalRoutingTest.php tests/Feature/AttendanceLogLocationByCoordinatesTest.php`
- `php artisan view:cache --no-interaction`
- `vendor/bin/pint --dirty --format agent`
- `git diff --check`

