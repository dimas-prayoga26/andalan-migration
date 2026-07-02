# Ringkasan Perubahan Attendance, Business Trip, dan Leave

Tanggal: 2026-06-03 17:01 WIB

## Ringkasan

- Merapikan struktur modul Attendance agar halaman overview, attendance today, report, leave request, overtime, dan business trip memakai folder dan nama route/view berbahasa Inggris.
- Mengaktifkan flow awal Business Trip dari index, create, show detail statis, hingga link cash advance dan reimbursement.
- Menambahkan struktur database, model, controller, dan route pendukung untuk detail Business Trip.
- Memperbarui halaman Leave Request: summary dipisah menjadi Eligibility dan Tracker, card dibuat dinamis, responsif mobile, serta filter history dibuat aktif via AJAX.
- Menambahkan `Unpaid Leave` ke seeder `leave_types`.

## Detail Perubahan

### 1) Attendance URL dan Struktur View

- Route `/attendance` dipakai sebagai halaman overview Attendance.
- Route daily attendance dipindah ke `/attendance/today`.
- View Attendance ditempatkan berdasarkan folder fitur:
  - `resources/views/attendance/index.blade.php`
  - `resources/views/attendance/attendance/index.blade.php`
  - `resources/views/attendance/reports/index.blade.php`
  - `resources/views/attendance/leave-requests/index.blade.php`
  - `resources/views/attendance/overtimes/index.blade.php`
  - `resources/views/attendance/business-trips/index.blade.php`
- Navbar/profile tab disesuaikan agar active state tetap benar pada route baru, termasuk `/attendance/business-trips/create`.

### 2) Attendance Overview dan Responsive Card

- Chart pada halaman Attendance overview dipindahkan/diinisialisasi langsung di `attendance/index.blade.php`:
  - `pieChart`
  - `radialBar`
  - `donut`
  - `barChart_3`
  - `lineChart_3`
  - `barChart_1`
  - `lineChart_2`
- Layout chart overview dirapikan agar sejajar.
- Card Attendance Rate sampai Overtime Rate dibuat slider pada tampilan mobile.
- Penyesuaian spacing mobile pada `layouts/profile-header.blade.php` agar chart dan slide card tidak terlalu menempel.

### 3) Attendance Report Holiday

- Attendance Report tidak lagi mengambil hari libur dari API `libur.deno.dev`.
- Data holiday/cuti bersama diambil dari tabel lokal `attendances_holidays`.
- Virtual row Sabtu, Minggu, dan holiday tetap ditampilkan dari logic report yang sudah ada, bukan dari data attendance real.

### 4) Business Trip

- Menghapus tabel/list lama `Data Perjalanan Dinas` dan flow button modal lama pada halaman business trip.
- Tombol `+ Business Trip` diarahkan ke halaman create.
- Form create Business Trip diaktifkan untuk proses store data:
  - purpose,
  - date range,
  - business trip type,
  - province,
  - city/regency,
  - transportation,
  - accommodation,
  - transportation mode,
  - departure date/time,
  - check-in/check-out.
- Field `Dates` memakai range date.
- `Departure Date`, `Date Check In`, dan `Date Check Out` memakai date picker.
- Jika `Transportation = Booked by GA`, field departure date/time disembunyikan.
- Jika `Accommodation = Booked by GA`, field check-in/check-out disembunyikan.
- Province dan city/regency diambil melalui proxy controller dari `https://wilayah.id/`.
- Index Business Trip dibuat dinamis dari tabel `business_trips`.
- Card Business Trip dapat diklik dan diarahkan ke detail berdasarkan id.
- Progress card sementara dibuat dummy `0%`.
- Detail Business Trip masih statis, tetapi sudah menerima model `BusinessTrip` dan tombol Cash Advance/Reimbursement diarahkan ke route nested sesuai id trip.

### 5) Struktur Database Business Trip

- Menambahkan kolom detail ke tabel `business_trips`:
  - `request_number`
  - `purpose`
  - `trip_type`
  - `province_code`
  - `province_name`
  - `city_regency_code`
  - `city_destination`
  - `transportation_type`
  - `accommodation_type`
  - `transportation_mode`
  - `departure_date`
  - `departure_time`
  - `check_in_date`
  - `check_out_date`
- Menambahkan tabel baru:
  - `business_trip_expense_items`
  - `business_trip_cash_advances`
  - `business_trip_reimbursements`
  - `business_trip_lifecycle_logs`
- Menambahkan model:
  - `BusinessTripExpenseItem`
  - `BusinessTripCashAdvance`
  - `BusinessTripReimbursement`
  - `BusinessTripLifecycleLog`
- Menambahkan relasi di model `BusinessTrip` untuk expense items, cash advances, reimbursements, lifecycle logs, dan attendances.

### 6) Controller dan Route Business Trip

- `BusinessTripController` dibuat sebagai resource untuk:
  - `index`
  - `create`
  - `store`
  - `show`
- Menambahkan endpoint proxy wilayah:
  - `attendance.business-trips.provinces`
  - `attendance.business-trips.regencies`
- Menambahkan controller resource pendukung di root controller folder:
  - `BusinessTripExpenseItemController`
  - `BusinessTripCashAdvanceController`
  - `BusinessTripReimbursementController`
  - `BusinessTripLifecycleLogController`
- Menambahkan route nested create:
  - `/attendance/business-trips/{businessTrip}/cash-advances/create`
  - `/attendance/business-trips/{businessTrip}/reimbursements/create`

### 7) Leave Request Summary

- Leave Summary dipisah menjadi dua tab:
  - `Eligibility`
  - `Tracker`
- Data Eligibility:
  - Full Name
  - Supervisor
  - Join Date
  - Current Tenure
  - Available Balance
  - Next Accrual
  - Joint Holiday
- Data Tracker:
  - Annual Leave Taken
  - Annual Leave Taken This Month
  - Sick Leave Taken
  - Sick Leave Taken This Month
  - Special Leave Taken
  - Special Leave Taken This Month
  - Unpaid Leave Taken
  - Unpaid Leave Taken This Month
  - Active/Pending Requests
  - Total Approved Leaves
  - Total Rejected Leaves
- Joint Holiday memakai format sisa total yang belum lewat / total joint holiday.
- Daftar joint holiday ditampilkan kebawah agar lebih mudah dibaca.

### 8) Leave Request Mobile Layout

- Card Leave Balance sampai Unpaid Leave dibuat slider mobile, satu card tampil penuh per slide.
- Card Attendance Rate sampai Overtime Rate di halaman Attendance juga dibuat slider mobile.
- Header Leave Summary disesuaikan pada mobile agar tab Eligibility/Tracker berada pada posisi yang diinginkan.
- Layout `leave_type` dibuat kondisional:
  - selain Special Leave memakai class full width,
  - Special Leave membagi kolom dengan Special Leave Type.

### 9) Leave Request Filter Realtime

- Filter Leave List sekarang aktif via AJAX:
  - status,
  - leave type,
  - timeframe.
- Menambahkan route:
  - `GET /attendance/leave-requests/cards`
  - name: `attendance.leave-requests.cards`
- Menambahkan method `LeaveRequestController::cards()`.
- Query history card difilter dari database berdasarkan status, leave type, dan timeframe.
- Setelah tombol `Request Time Off` berhasil submit, card Leave List langsung refresh otomatis tanpa reload halaman manual.
- Markup card history dipindahkan ke partial:
  - `resources/views/attendance/leave-requests/partials/history-cards.blade.php`

### 10) Leave Type

- Menambahkan value `Unpaid Leave` pada `LeaveTypeSeeder`:
  - `code = UNPAID`
  - `name = Unpaid Leave`
  - `accrual_method = none`
  - `monthly_accrual_rate = 0`
  - `is_encashable = false`
  - `is_active = true`

## File Penting

- `routes/web.php`
- `app/Http/Controllers/BusinessTripController.php`
- `app/Http/Controllers/BusinessTripCashAdvanceController.php`
- `app/Http/Controllers/BusinessTripReimbursementController.php`
- `app/Http/Controllers/LeaveRequestController.php`
- `app/Models/BusinessTrip.php`
- `app/Models/BusinessTripExpenseItem.php`
- `app/Models/BusinessTripCashAdvance.php`
- `app/Models/BusinessTripReimbursement.php`
- `app/Models/BusinessTripLifecycleLog.php`
- `database/migrations/2026_06_03_080202_add_business_trip_details_to_business_trips_table.php`
- `database/migrations/2026_06_03_080203_create_business_trip_expense_items_table.php`
- `database/migrations/2026_06_03_080204_create_business_trip_cash_advances_table.php`
- `database/migrations/2026_06_03_080204_create_business_trip_reimbursements_table.php`
- `database/migrations/2026_06_03_080205_create_business_trip_lifecycle_logs_table.php`
- `database/seeders/LeaveTypeSeeder.php`
- `resources/views/attendance/index.blade.php`
- `resources/views/attendance/attendance/index.blade.php`
- `resources/views/attendance/leave-requests/index.blade.php`
- `resources/views/attendance/leave-requests/partials/history-cards.blade.php`
- `resources/views/attendance/business-trips/index.blade.php`
- `resources/views/attendance/business-trips/create.blade.php`
- `resources/views/attendance/business-trips/detail.blade.php`
- `resources/views/attendance/business-trips/create-cash-advance.blade.php`
- `resources/views/attendance/business-trips/create-reimbursement.blade.php`

## Verifikasi

- Menjalankan formatter:
  - `vendor\bin\pint --dirty --format agent`
- Menjalankan test terfokus:
  - `php artisan test --compact tests\Feature\AttendanceNamingConventionTest.php`
  - `php artisan test --compact tests\Feature\BusinessTripPageCleanupTest.php`
  - `php artisan test --compact tests\Feature\BusinessTripStoreTest.php`
  - `php artisan test --compact tests\Feature\BusinessTripDetailTablesMigrationTest.php`
  - `php artisan test --compact tests\Feature\BusinessTripDetailModelsTest.php`
  - `php artisan test --compact tests\Feature\BusinessTripDetailControllersTest.php`
  - `php artisan test --compact tests\Feature\LeaveHistoryYearFilterTest.php`
- Menjalankan validasi tambahan:
  - `php -l app\Http\Controllers\LeaveRequestController.php`
  - `php artisan route:list --name=attendance.leave-requests --except-vendor`
  - `php artisan view:cache --no-interaction`
  - `php artisan view:clear --no-interaction`
  - `git diff --check`

## Catatan

- Detail Business Trip masih memakai tampilan statis untuk beberapa area, tetapi route dan model sudah disiapkan untuk data dinamis berikutnya.
- Progress percentage Business Trip sementara masih dummy `0%`; rencana berikutnya bisa dihitung dari akumulasi `business_trip_lifecycle_logs`.
- Warning `Module "mysqli" is already loaded` muncul dari konfigurasi PHP lokal dan bukan dari perubahan kode.
