# Sinkronisasi Staff, PIC, Chart, dan Leave

Tanggal: 2026-07-06 WIB

## Ringkasan

- Menyelaraskan data staff aktif hasil import legacy dengan daftar employee terbaru per company dan branch.
- Memperbaiki cakupan PIC Overtime agar assignment supervisor dapat berlaku lintas company.
- Merapikan profile header dan chart Attendance serta Project Management pada tampilan mobile.
- Membuat attachment Sick Leave bersifat opsional dan menghilangkan pemanggilan file picker ganda.

## Sinkronisasi Employee Legacy

- Menambahkan pemetaan deployment terbaru untuk 18 staff aktif:
  - RNB Branch Jakarta: Lukman Prabowo, Rully Priyatno, dan Hilmi Ulwan.
  - RNB Branch Jogja: Mevia Dikta Namira dan Tsabita Anisa Erliana.
  - KMA: Ahmad Fahmil Ulumi, Arya Widi Nugroho, Yusuf Eriansyah, dan M Alfian Aris Subakti.
  - RNE: Fuad M Fahrudin.
  - TRAH: Rexy Aldinny, Arum Kusumawati, dan Dedy Setiawan.
  - Niskala: Leonie Putri Andhari.
  - TMS: Muhammad Syafiq, Syarif Hidayatullah, Rifka Febriza, dan Dimas Prayoga.
- Menambahkan sinkronisasi company, workplace, office location, employee, deployment, primary position, dan tanggal mulai kerja.
- Mempertahankan tanggal mulai kerja lama jika tersedia. Employee tanpa tanggal mulai kerja menggunakan nilai default satu bulan sebelum proses seed.
- Menonaktifkan akun legacy yang sudah tidak termasuk daftar staff terbaru beserta assignment PIC aktifnya.
- Menambahkan konfigurasi default office Yogyakarta untuk kebutuhan sinkronisasi office location dan deployment.
- Menjalankan ulang `LegacySqlUserSeeder` dan `EmployeePicAssignmentSeeder` pada database lokal.

## Company dan Branch

- Branch employee saat ini direpresentasikan melalui `employee_deployments.workplace`.
- Titik kantor employee disimpan melalui `employee_deployments.current_office_location_id`.
- Office location akan dibuat atau diperbarui berdasarkan company dan workplace yang dipetakan oleh seeder.
- Form input Branch terpisah pada halaman create/update Data Employee belum termasuk dalam perubahan ini.

## PIC Overtime Lintas Company

- Menghapus pembatasan company yang sebelumnya diterapkan saat mengambil staff di bawah PIC.
- Daftar staff PIC sekarang mengikuti assignment aktif pada `employee_pic_assignments`.
- Assignment tetap memperhatikan status aktif employee, deployment, PIC, serta periode berlakunya assignment.
- Perubahan ini memungkinkan supervisor dari satu company mengelola overtime staff dari company lain jika relasinya memang terdaftar.

## Profile Header dan Chart Mobile

- Menambahkan wrapping pada email profile agar alamat email panjang tidak keluar dari card.
- Menyesuaikan chart Attendance Overview agar tinggi container mengikuti chart dan tidak menyisakan ruang kosong berlebih.
- Menambahkan minimum height hanya ketika elemen chart masih kosong sebelum ApexCharts selesai dirender.
- Menetapkan `parentHeightOffset` menjadi `0` pada chart terkait agar kalkulasi tinggi lebih stabil.
- Menyesuaikan ukuran dan offset radial chart Project Management pada breakpoint mobile.

## Sick Leave Attachment

- Menghapus kewajiban attachment untuk pengajuan dan pembaruan Sick Leave.
- Mempertahankan validasi attachment sebagai file opsional dengan format JPG, JPEG, PNG, atau PDF dan ukuran maksimal 1 MB.
- Mengubah label form menjadi `Attachment (optional, max 1 MB)`.
- Menghapus class upload global dari komponen attachment Leave.
- Perubahan class mencegah handler global dan handler khusus halaman berjalan bersamaan, yang sebelumnya menyebabkan file picker atau preview diproses dua kali.

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/LegacyEmployeeAssignmentSeederTest.php tests/Feature/PicAttendanceOvertimeStoreTest.php`
  - Hasil: 7 test lulus dengan 114 assertion.
- `php artisan test --compact tests/Feature/LeaveRequestSickAttachmentOptionalTest.php`
  - Hasil: 2 test lulus dengan 12 assertion.
- Menambahkan atau memperbarui test untuk:
  - Pemetaan employee legacy terbaru.
  - PIC Overtime lintas company.
  - Attachment Sick Leave opsional.
  - Struktur profile header dan chart responsive.
- Menjalankan Laravel Pint untuk file PHP yang berubah.

