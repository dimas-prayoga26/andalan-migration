# Ringkasan Perubahan Harian 2 Juli 2026

Tanggal: 2026-07-02 WIB

## Ringkasan

- Menyempurnakan Data Employee dan Authorization untuk pencarian, pagination, cakupan akses, serta assignment permission berbasis position.
- Menambahkan menu Task pada modul PIC agar PIC dapat melihat task staff yang berada di bawah pengawasannya.
- Merapikan data employee legacy, multiple position, assignment PIC, profil, foto profil, dan akun tambahan RNB.
- Menyesuaikan geofencing agar mengikuti office location employee dan mendukung kantor RNB Yogyakarta serta Jakarta.
- Menambahkan branding berdasarkan host/domain untuk logo aplikasi dan halaman login.
- Merapikan Dashboard, Attendance, Overtime, dan Project Management, khususnya tampilan mobile dan data summary.
- Memusatkan perhitungan saldo cuti tahunan ke service dan menjalankan sinkronisasi accrual setiap awal bulan.

## Data Employee dan Authorization

- Menambahkan pencarian employee berdasarkan nama, username, email, employee code, NIK, company, position, status, dan PIC.
- Mengubah list employee menjadi pagination server-side dengan 10 data per halaman.
- Menambahkan footer informasi jumlah data serta navigasi previous, nomor halaman, dan next.
- Administrator dan superuser dapat melihat employee lintas company sesuai kewenangan pengelolaan authorization.
- Chief Operating Officer dapat membuka pengaturan permission dengan cakupan employee pada company-nya.
- Tab Assign Permission hanya ditampilkan kepada user yang memang dapat mengelola permission position.
- Mempertahankan pemeriksaan permission pada route, bukan hanya menyembunyikan menu dari tampilan.

## Employee, Position, PIC, dan Seeder Legacy

- Menyesuaikan dukungan multiple position pada employee dan pemetaan permission berbasis seluruh position aktif.
- Menetapkan Mevia Dikta Namira sebagai Administrator dan Accounting and Taxation.
- Menetapkan Leonie Putri Andhari dan Muhammad Syafiq sebagai Supervisor tanpa menambahkan position Administrator.
- Menyesuaikan mapping Director dan Supervisor untuk user legacy lain sesuai struktur organisasi.
- Menambahkan akun RNB eksplisit untuk Rully Priyatno dan Hilmi Ulwan.
- Mengecualikan akun legacy Adik Wiriyanto serta placeholder administrator dari hasil import.
- Memperbarui assignment PIC agar relasi supervisor dan staff mengikuti mapping data terbaru.
- Menghapus assignment PIC self-reference Leonie yang sebelumnya tidak sesuai.
- Menyesuaikan scope Project Management dan Business Trip agar membaca seluruh position aktif employee.

## Profil dan Header

- Memusatkan data foto profil ke `employee_profiles.profile_picture_path`.
- Menghapus model dan migration lama `user_profiles` yang sudah tidak dipakai sebagai sumber profil utama.
- Menambahkan `HeaderProfileComposer` untuk nama, position, dan avatar user pada header aplikasi.
- Menambahkan gambar avatar default ketika employee belum memiliki foto atau file foto tidak ditemukan.
- Menyesuaikan header Attendance dan Project Management agar menggunakan sumber profil employee yang sama.

## PIC Attendance

- Menambahkan menu navbar **Task** pada modul PIC Attendance.
- Menambahkan controller dan halaman Task khusus PIC.
- Menampilkan task staff berdasarkan relasi aktif `employee_pic_assignments`.
- Menambahkan filter staff dan endpoint DataTable task.
- Menampilkan nama staff, task, project/daily task, pemberi tugas, rentang tanggal, dan status task.
- Memastikan PIC tidak dapat melihat task staff yang tidak berada di bawah pengawasannya.
- Merapikan tampilan Attendance PIC dan menambahkan test struktur modul PIC.

## Attendance dan Geofencing

- Menghapus koordinat kantor dari tabel `companies` agar titik geofencing tidak lagi melekat langsung pada company.
- Menggunakan `office_locations` sebagai sumber address, latitude, dan longitude kantor.
- Menghubungkan `employee_deployments.current_office_location_id` dan `rules_of_attendaces.office_location_id` ke titik kantor terkait.
- Menambahkan rules attendance untuk setiap office location aktif.
- Menambahkan office location RNB Jakarta di Jl. Bhineka Blok Bhineka No.26 dengan koordinat `-6.3636699, 106.8016359`.
- Mengarahkan employee RNB Jakarta ke office location dan rules attendance Jakarta, sementara office utama tetap tersedia sebagai fallback.
- Menyesuaikan Attendance Context dan Attendance Mutation agar validasi geofencing memakai office location employee.

## Admin dan PIC Attendance Recap

- Menyesuaikan denominator Working Days agar menggunakan total hari kerja penuh pada bulan terpilih.
- Perhitungan hari kerja tetap mengecualikan weekend dan holiday sesuai rules recap.
- Menyamakan perhitungan Working Days pada Admin Attendance dan PIC Attendance.
- Merapikan tampilan detail recap attendance dan menambahkan test untuk memastikan denominator tidak bergantung pada tanggal join employee.

## Branding dan Login

- Menambahkan `HostBrandingResolver` dan konfigurasi `config/branding.php`.
- Menentukan logo berdasarkan host/domain untuk development, RNB, TRAH, KMA, RNE, Niskala, dan TMS.
- Mengganti logo carousel login menjadi satu logo yang mengikuti host aktif.
- Menyesuaikan logo desktop/mobile pada layout utama dan header aplikasi.
- Menambahkan asset logo development `public/images/images.png`.

## Dashboard

- Menyembunyikan carousel menu dashboard tanpa menghapus markup lama.
- Menampilkan shortcut yang sudah aktif: Attendance, Calendar, Project, Task List, dan Data Employee sesuai permission.
- Menyembunyikan shortcut fitur yang belum aktif agar tidak mengarahkan user ke halaman yang belum berjalan.
- Menghapus informasi Distance dari card attendance dashboard.
- Mengganti informasi card menjadi Date & Time dengan format `dd Mon yyyy | HH:mm:ss`.
- Menyesuaikan warna waktu clock-in berdasarkan batas keterlambatan dan clock-out berdasarkan rentang jam kerja.
- Merapikan tampilan card dan modal clock-in/clock-out.

## Staff Attendance dan Overtime

- Merapikan chart Attendance Overview agar responsif pada mobile.
- Menyesuaikan ukuran legend, label axis, wrapper chart, radial chart, dan tinggi chart berdasarkan viewport.
- Merapikan layout card summary Attendance Overview pada layar kecil.
- Mengubah card summary Overtime menjadi horizontal mobile slider dengan satu card per slide.
- Mempertahankan filter overtime dan route detail yang sudah berjalan.

## Project Management

- Merapikan halaman Project Management Overview untuk tampilan mobile.
- Menyesuaikan summary card, task overview, chart, dan layout profile project agar tidak terpotong pada layar kecil.
- Menambahkan penyesuaian struktur dan test layout Project Management.

## Leave Balance dan Cuti Tahunan

- Menambahkan `AnnualLeaveBalanceService` sebagai pusat perhitungan saldo cuti tahunan.
- Menetapkan total hak cuti tahunan sebanyak 12 hari.
- Mengurangi kuota pribadi dengan jumlah cuti bersama dari `attendance_holidays` bertipe 2.
- Menetapkan syarat pengajuan cuti tahunan setelah masa kerja minimal 12 bulan.
- Menghitung accrual berdasarkan bulan kerja yang telah selesai dengan batas maksimal kuota pribadi.
- Menghitung `used_quota` dari leave request Annual Leave berstatus approved.
- Menolak pengajuan jika masa kerja belum memenuhi syarat, saldo habis, atau durasi melebihi saldo tersedia.
- Mengubah `LeaveBalanceSeeder` agar memakai service yang sama dengan aplikasi.
- Menjalankan command `leave-balances:sync` setiap tanggal 1 pukul 00:10 zona waktu Asia/Jakarta.
- Mendaftarkan `LeaveBalanceSeeder` setelah `AttendanceHolidaySeeder` pada fresh seed.

## Dokumentasi dan Test

- Mengganti README bawaan Laravel dengan dokumentasi project Andalan Migration, requirement, dan langkah setup project.
- Menambahkan atau memperbarui test Authorization, Data Employee, multiple position, profile picture, header profile, Dashboard, PIC Attendance, geofencing, Project Management, Attendance Overview, dan Leave Balance.

## Commit Terkait

- `5be7787` - Fixing position.
- `e2701b6` - Fixing position dan geofencing.
- `5938432` - Menambahkan asset logo development.
- `f385366` - Branding host, dashboard, profile scope, dan office assignment RNB Jakarta.
- `d7aaf3e` - Memperbaiki total Working Days bulanan.
- `7bd02e1` - Memperbaiki format Date & Time dashboard.
- `019bd8c` - README dan responsive view Project Management, Attendance Overview, serta Overtime.
- `c7e2e6c` - Service dan sinkronisasi saldo cuti tahunan.

