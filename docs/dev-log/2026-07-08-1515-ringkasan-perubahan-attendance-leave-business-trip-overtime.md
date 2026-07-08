# Ringkasan Perubahan Attendance, Leave, Business Trip, dan Overtime

Tanggal: 2026-07-08 15:15 WIB

## Ringkasan

- Menyesuaikan perhitungan `Joint Holiday (2026)` agar jumlah cuti bersama yang sudah terlewati dihitung berdasarkan tanggal sistem saat ini.
- Menyamakan tampilan dan logika cuti bersama pada Staff Attendance Leave, Admin Attendance Leave detail, dan PIC Attendance Leave detail.
- Merapikan tombol `Request Time Off` pada form leave staff agar posisinya proporsional dan center terhadap layout form.
- Mengganti ikon `Eligibility` dan `Tracker` pada Leave Summary agar lebih sesuai dengan fungsi masing-masing metrik.
- Membesarkan ikon Leave Summary agar lebih terbaca pada tab Eligibility dan Tracker.
- Mengatur date/time picker agar awal minggu dimulai dari hari Senin.
- Menambahkan jarak bawah pada kartu `Leave Summary` agar list di bawahnya tidak terlalu rapat.
- Mengatur form Business Trip agar field `transportation mode`, `departure time`, dan `accommodation check in/out` hanya tampil saat opsi booking membutuhkan input tersebut, dan disembunyikan untuk `self managed`.
- Menyesuaikan ikon dan warna pada kartu summary Business Trip agar lebih representatif terhadap metriknya.
- Memperbaiki logika Attendance Overview agar `Attendance Rate`, `On Time Rate`, dan `Lateness Rate` tidak lagi memakai angka statis.
- Membulatkan persentase attendance/overtime agar tidak menampilkan desimal panjang di UI.
- Memisahkan makna `Attendance Rate` dan `Days Worked`:
  - `Attendance Rate` merepresentasikan kualitas presensi berdasarkan alpha.
  - `Days Worked` merepresentasikan jumlah hari kerja yang sudah berjalan terhadap total hari kerja bulan berjalan.
- Menghitung hari kerja bulan berjalan berdasarkan kalender Senin-Jumat dan tanggal sistem saat ini.
- Menyesuaikan card progress `Days Worked` agar tidak bergantung pada data presensi aktual user.
- Mengubah kartu summary Overtime staff menjadi satu baris slider:
  - Desktop menampilkan 4 kartu per viewport.
  - Mobile menampilkan 1 kartu full per viewport.
  - Kartu bawah digabung ke slider yang sama dengan kartu atas.
- Mempertahankan style kartu Overtime lama, lalu hanya mengganti ikon dan warna agar lebih sesuai dengan metrik.
- Menyesuaikan ukuran ikon, alignment, dan jarak kartu Overtime agar lebih rapi dan tidak terlalu rapat.
- Mengubah `Estimated Extra Earnings` sementara menjadi `Rp 0`.
- Mengubah perhitungan `Completed & Locked` agar dihitung dari approval PIC/SPV pada lifecycle `task_hours_verification` dengan status `verified`.
- Menambahkan kolom approved time pada tabel `overtimes` untuk menyimpan jam final hasil revisi/approval PIC.
- Mengubah proses approval PIC Overtime agar menyimpan `approved_start_time`, `approved_end_time`, dan `calculated_hours`.
- Menjaga `actual_start_time` dan `actual_end_time` tetap sebagai jam clock-in/clock-out staff, bukan ditimpa oleh hasil approval PIC.
- Menambahkan toleransi clock-in dan clock-out lembur selama 30 menit:
  - Staff dapat clock-in dari 30 menit sebelum sampai 30 menit setelah `planned_start_time`.
  - Staff dapat clock-out dari 30 menit sebelum sampai 30 menit setelah `planned_end_time`.
- Memperbarui halaman detail overtime staff agar `Overtime Log (Assigned)` berubah menjadi `Overtime Log (Completed)` setelah PIC verified.
- Menampilkan jam dan durasi overtime completed dengan dua nilai:
  - Nilai planned ditampilkan sebagai strikethrough.
  - Nilai approved PIC ditampilkan normal.
- Memperbarui filter/list overtime staff `Completed` agar hanya menampilkan data yang sudah disetujui PIC melalui `task_hours_verification = verified`.

## Leave

- Staff Leave Summary sekarang menampilkan progres cuti bersama yang sudah terlewati secara aktual berdasarkan tanggal hari ini.
- Detail leave di Admin Attendance dan PIC Attendance memakai logika cuti bersama yang sama agar tidak terjadi selisih tampilan antar role.
- Tombol `Request Time Off` dirapikan agar align dengan layout form.
- Ikon `Eligibility` dan `Tracker` diganti agar tidak memakai simbol yang sama/kurang representatif.
- Date picker disesuaikan agar week start memakai Senin.
- Kartu `Leave Summary` diberi margin bawah untuk memperbaiki jarak ke area list.

## Attendance Overview

- `Attendance Rate` diinisialisasi dari 100% dan berkurang otomatis berdasarkan jumlah hari kerja alpha.
- `On Time Rate` diinisialisasi dari 100% dan berkurang otomatis berdasarkan jumlah record terlambat.
- `Lateness Rate` diinisialisasi dari 0% dan bertambah otomatis berdasarkan akumulasi keterlambatan.
- `Days Worked` tidak lagi memakai jumlah presensi aktual user, tetapi jumlah hari kerja yang sudah berjalan pada bulan aktif.
- Total hari kerja bulanan memakai hari Senin-Jumat, bukan jumlah seluruh hari kalender.
- Persentase ditampilkan sebagai angka bulat agar UI lebih bersih.

## Business Trip

- Field tambahan booking dibuat kondisional:
  - Ditampilkan saat opsi booking memang membutuhkan detail transportasi/jadwal/akomodasi.
  - Disembunyikan saat memilih `self managed`.
- Ikon dan warna summary card Business Trip disesuaikan agar membedakan total trip, days away, pending approval, upcoming, cash advance, reimbursement, overdue report, dan settled trip.

## Overtime

- Summary card Overtime staff diubah menjadi horizontal slider.
- Pada desktop, slider menjaga 4 kartu per baris/viewport.
- Pada mobile, slider menampilkan 1 kartu penuh per slide.
- Semua kartu summary Overtime disatukan dalam satu slider yang sama.
- Ikon Overtime diperbarui:
  - Total Logged Hours memakai ikon waktu.
  - Overtime Cap memakai ikon gauge.
  - Average Extra Hours memakai ikon chart.
  - Tasks Finalized memakai ikon checklist.
  - Pending SPV Approval memakai ikon user-clock.
  - Completed & Locked memakai ikon lock.
  - Estimated Extra Earnings memakai ikon coins.
  - Disputed Hours memakai ikon warning.
- `Estimated Extra Earnings` dipaksa `Rp 0` untuk sementara.
- `Completed & Locked` dihitung dari overtime yang sudah memiliki lifecycle `task_hours_verification` berstatus `verified`.
- Detail Admin, PIC, Director, dan Staff membaca approved time sebagai sumber jam final setelah PIC melakukan verifikasi.
- PIC approval menyimpan jam approved tanpa menimpa actual clock staff.
- Staff detail overtime completed menampilkan planned time/duration dengan strikethrough dan approved time/duration sebagai nilai normal.
- Filter status `Completed` pada list overtime staff sekarang berbasis lifecycle PIC verified, bukan hanya `overtimes.status = completed`.

## Schema dan Data

- Menambahkan migration `2026_07_08_074125_add_approved_times_to_overtimes_table`.
- Kolom baru pada `overtimes`:
  - `approved_start_time`
  - `approved_end_time`
- Migration sudah dijalankan pada database lokal.

## File Utama

- `app/Http/Controllers/AdminAttendance/AttendanceOvertimeController.php`
- `app/Http/Controllers/DirectorAttendance/DirectorAttendanceOvertimeController.php`
- `app/Http/Controllers/PicAttendance/PicAttendanceOvertimeController.php`
- `app/Http/Controllers/StaffAttendance/AttendanceOvertimeController.php`
- `database/migrations/2026_07_08_074125_add_approved_times_to_overtimes_table.php`
- `resources/views/admin_attendance/leave/detail.blade.php`
- `resources/views/admin_attendance/overtime/detail.blade.php`
- `resources/views/director_attendance/overtime/detail.blade.php`
- `resources/views/pic_attendance/leave/detail.blade.php`
- `resources/views/pic_attendance/overtime/detail.blade.php`
- `resources/views/staff_attendance/overtimes/detail.blade.php`
- `resources/views/staff_attendance/overtimes/index.blade.php`

## Test dan Verifikasi

- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests\Feature\ProjectOvertimeRelationTest.php`
- `php artisan view:cache --no-interaction`
- `git diff --check`

Catatan: command PHP menampilkan warning `Module "mysqli" is already loaded`, tetapi proses tetap berhasil.
