# Ringkasan Perubahan Attendance, Seeder, dan Settings

Tanggal: 2026-08-06 14:38 WIB

## Ringkasan

- Menambahkan kolom `office_reset_time` pada tabel `rules_of_attendaces`.
- Mengatur nilai awal `office_reset_time`: rules dengan `office_start_time = 09:00:00` memakai `04:00:00`, sedangkan rules lain memakai `00:00:00`.
- Mengubah logika tanggal attendance agar reset harian dibaca dari alur `current_office_location_id` employee, lalu `office_locations`, lalu `rules_of_attendaces.office_reset_time`.
- Mengubah validasi clock-out agar mengikuti `office_end_time`; staff tidak bisa clock-out sebelum jam selesai kantor pada attendance rule lokasi aktifnya.
- Menyamakan logika reset attendance dan validasi clock-out pada menu Dashboard serta menu Attendance.
- Menambahkan notifikasi `Swal.fire` saat tombol Clock Out diklik sebelum waktunya, dengan fallback browser alert jika SweetAlert2 belum tersedia.
- Menambahkan `attendance_type` pada `rules_of_attendaces` dengan tipe `fixed` dan `flexible`.
- Menambahkan pivot `attendance_rule_positions` agar satu rule attendance bisa dipakai banyak position, dan satu office seperti Jakarta bisa punya rule berbeda untuk staff biasa dan Driver.
- Menambahkan position `Driver`, permission staff dasar untuk Driver, serta rule flexible Jakarta yang otomatis dipasang ke Driver.
- Mengubah flexible attendance menjadi open-session based: sistem mencari attendance terakhir yang `clock_in` sudah terisi dan `clock_out` masih kosong.
- Flexible attendance tidak memakai late/on-time dan tidak dibatasi `office_end_time`, sehingga Driver bisa clock-out setelah jam 12 malam.
- Clock In flexible akan tetap terkunci selama masih ada sesi open yang belum clock-out.
- Memperbaiki urutan dependency seeder agar company dan position tersedia sebelum `LegacySqlUserSeeder` berjalan.
- Memperbaiki parser `LegacySqlUserSeeder` agar bisa membaca SQL dump `INSERT` yang tidak mencantumkan daftar kolom secara eksplisit.
- Mengganti reusable delete confirmation settings dari Bootstrap modal partial menjadi `Swal.fire`.

## Attendance

- `AttendanceContextService` sekarang mengambil context rules attendance berdasarkan lokasi kantor aktif employee:
  - `employee_deployments.current_office_location_id`
  - `office_locations`
  - `rules_of_attendaces`
- Context attendance menyertakan `office_reset_time` dan `office_end_time`.
- Tanggal attendance dihitung dari `office_reset_time`, sehingga shift tertentu bisa reset setelah jam khusus, misalnya reset jam `04:00:00` untuk rules yang mulai jam `09:00:00`.
- `AttendanceTodayStateService` memakai tanggal attendance dari context rules, bukan hanya tanggal kalender biasa.
- `AttendanceMutationService` memakai tanggal attendance yang sama saat clock-in dan clock-out.
- Clock-out ditolak sebelum `office_end_time` pada rules lokasi aktif. Contoh: jika `office_end_time = 17:00:00`, maka sebelum pukul 17:00 staff belum bisa clock-out.
- Pesan validasi clock-out disiapkan dari service agar UI dan backend tetap konsisten.
- Untuk rules `attendance_type = flexible`, validasi late dan batas jam clock-out dilewati. Status attendance disimpan sebagai `Flexible` dengan `late_minutes = 0`.
- Untuk flexible, state hari ini mengambil sesi open terakhir lintas tanggal, bukan hanya attendance tanggal kalender berjalan.

## Dashboard dan Menu Attendance

- `AttendanceCardsViewDataService` mengirim status tambahan untuk tampilan:
  - `canClockOutNow`
  - `clockOutAvailableAt`
  - `clockOutUnavailableMessage`
- `resources/views/dashboard.blade.php` men-disable tombol Clock Out sebelum `office_end_time`.
- `resources/views/staff_attendance/components/attendance-cards.blade.php` memakai kondisi yang sama, sehingga menu Dashboard dan menu Attendance tidak berbeda perilaku.
- Tombol Clock Out yang belum waktunya tetap dapat diklik untuk menampilkan peringatan `Clock Out Belum Tersedia`, tanpa membuka modal submit clock-out.

## Database dan Seeder

- Migration baru menambahkan kolom `office_reset_time` bertipe `time` dengan default `00:00:00` pada `rules_of_attendaces`.
- Model `RulesOfAttendace` diberi default attribute `office_reset_time = 00:00:00`.
- `DatabaseSeeder` menempatkan `CompanySeeder` sebelum seeder yang membutuhkan data company/position.
- `LegacySqlUserSeeder` bisa memetakan kolom dari `CREATE TABLE` ketika `INSERT INTO users VALUES (...)` tidak membawa daftar kolom.
- Perbaikan ini menutup error seeder terkait data RNB, position, dan employee assignment yang sebelumnya tidak terbaca lengkap dari dump legacy.

## Settings Delete Confirmation

- Partial lama `resources/views/settings/partials/delete-confirmation-modal.blade.php` dihapus.
- Partial baru `resources/views/settings/partials/delete-confirmation-swal.blade.php` dibuat untuk menangani konfirmasi delete settings dengan `Swal.fire`.
- Halaman settings berikut diarahkan ke partial Swal:
  - `resources/views/settings/index.blade.php`
  - `resources/views/settings/office-locations/index.blade.php`
  - `resources/views/settings/attendance-rules/index.blade.php`
- Handler Swal tetap melakukan intercept form `data-settings-delete-form`, menampilkan konfirmasi, lalu submit form hanya setelah user menekan tombol Delete.
- Jika SweetAlert2 tidak tersedia, handler menyediakan fallback `window.confirm`.

## File Utama

- `app/Http/Controllers/Settings/AttendanceRuleController.php`
- `app/Models/RulesOfAttendace.php`
- `app/Services/Attendance/AttendanceCardsViewDataService.php`
- `app/Services/Attendance/AttendanceContextService.php`
- `app/Services/Attendance/AttendanceMutationService.php`
- `app/Services/Attendance/AttendanceTodayStateService.php`
- `database/migrations/2026_08_06_135554_add_office_reset_time_to_rules_of_attendaces_table.php`
- `database/migrations/2026_08_06_154257_add_attendance_type_and_positions_to_rules_of_attendaces_table.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/LegacySqlUserSeeder.php`
- `database/seeders/PositionPermissionSeeder.php`
- `database/seeders/PositionSeeder.php`
- `database/seeders/RulesOfAttendacesSeeder.php`
- `resources/views/dashboard.blade.php`
- `resources/views/settings/attendance-rules/index.blade.php`
- `resources/views/settings/index.blade.php`
- `resources/views/settings/office-locations/index.blade.php`
- `resources/views/settings/partials/delete-confirmation-swal.blade.php`
- `resources/views/staff_attendance/components/attendance-cards.blade.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `tests/Feature/DatabaseSeederDependencyOrderTest.php`
- `tests/Feature/LegacyEmployeeAssignmentSeederTest.php`
- `tests/Feature/OfficeLocationGeofencingTest.php`
- `tests/Feature/SettingsManagementTest.php`
- `tests/Unit/AttendanceRuleResetTimeTest.php`

## Test dan Verifikasi

- `vendor\bin\pint --dirty --format agent`
- `php artisan test --compact tests/Unit/AttendanceRuleResetTimeTest.php`
- `php artisan test --compact tests/Feature/DatabaseSeederDependencyOrderTest.php`
- `php artisan test --compact tests/Feature/LegacyEmployeeAssignmentSeederTest.php`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php tests/Feature/OfficeLocationGeofencingTest.php tests/Feature/DashboardStructureTest.php tests/Unit/AttendanceRuleResetTimeTest.php`
- `php artisan test --compact tests/Feature/SettingsManagementTest.php`
- `php artisan test --compact tests/Feature/DashboardStructureTest.php tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `php artisan migrate --no-interaction`
- `php artisan db:seed --class=PositionSeeder --no-interaction`
- `php artisan db:seed --class=PositionPermissionSeeder --no-interaction`
- `php artisan db:seed --class=RulesOfAttendacesSeeder --no-interaction`
- `php artisan test --compact tests/Feature/AttendanceCalendarModalRoutingTest.php tests/Feature/OfficeLocationGeofencingTest.php tests/Feature/SettingsManagementTest.php tests/Feature/DashboardStructureTest.php tests/Unit/AttendanceRuleResetTimeTest.php`

## Catatan Lanjutan

- Pastikan data production/dev sudah menjalankan migration `office_reset_time`.
- Jika ada rules dengan jam kerja khusus selain mulai pukul `09:00:00`, nilai `office_reset_time` perlu disesuaikan manual sesuai kebijakan kantor.
- Validasi clock-out sudah ada di backend, sehingga UI yang disabled bukan satu-satunya pengaman.
