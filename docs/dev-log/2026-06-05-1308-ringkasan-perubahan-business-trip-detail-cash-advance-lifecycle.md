# Ringkasan Perubahan Business Trip Detail, Cash Advance, dan Lifecycle

Tanggal: 2026-06-05 13:08 WIB

## Ringkasan

- Merapikan flow Business Trip detail agar data utama tidak lagi bergantung ke dummy statis.
- Menghapus konsep `business_trip_expense_items` karena tidak lagi dipakai untuk flow saat ini.
- Mengaktifkan create/update cash advance dari form staff.
- Menghubungkan Trip Lifecycle Tracker ke tabel `business_trip_lifecycle_logs`.
- Menambahkan command dan scheduler untuk sinkronisasi otomatis status lifecycle berdasarkan tanggal dinas.
- Menyesuaikan tampilan Expense, Cash Advance, Reimbursement, financial summary, attachment modal, dan status agar mengikuti data approved.
- Menambahkan flow Trip Report dari cash advance report: semua item wajib punya `amount_realized` dan attachment sebelum step 6 complete.
- Mengaktifkan tombol Reimbursement hanya saat lifecycle step 7 `reimbursement_submitted` sudah `pending`.

## Detail Perubahan

### 1) Lifecycle Logs

- Menghapus migration tambahan `2026_06_04_044036_add_display_values_to_business_trip_lifecycle_logs_table` karena kolom display tidak dipakai.
- Menyesuaikan tabel `business_trip_lifecycle_logs` agar cukup menyimpan:
  - `phase`
  - `event_key`
  - `step_order`
  - `title`
  - `status`
  - `actor_id`
  - `happened_at`
  - `metadata`
- Business Trip create sekarang membuat initial lifecycle logs.
- Trip Lifecycle Tracker di detail mengambil data dari tabel `business_trip_lifecycle_logs`, bukan array statis di Blade.
- Format datetime lifecycle dikonversi ke timezone `Asia/Jakarta` saat tampil.

### 2) Cash Advance Store/Update

- Menambahkan route POST untuk store/update cash advance:
  - `attendance.business-trips.cash-advances.store`
- Form `create-cash-advance.blade.php` sekarang bisa create dan update data `business_trip_cash_advances`.
- Form cash advance sekarang memakai `multipart/form-data` karena staff bisa upload attachment report per item.
- Field report per item cash advance:
  - `amount_realized`
  - `attachment_path`
- Submit cash advance mengupdate lifecycle log `cash_advance_submitted`:
  - `event_key`: `cash_advance_submitted`
  - `status`: `waiting`
  - `actor_id`: user staff yang submit
  - `happened_at`: waktu submit
- Attachment cash advance disimpan di disk `public` pada folder:
  - `business-trip-cash-advance-attachments/{business_trip_id}`
- Attachment existing dipertahankan saat update jika staff tidak upload file baru.
- Attachment lama dihapus dari storage jika diganti atau row cash advance dihapus.
- Bug dropdown `Breakdown` saat klik Add diperbaiki dengan reset dan re-init select picker pada row clone.

### 2.1) Trip Report dari Cash Advance

- Step 6 `Trip Report & Task Submitted` tidak complete hanya karena masuk Phase 4.
- Step 6 berubah menjadi `complete` setelah semua row pada `business_trip_cash_advances` untuk trip tersebut memenuhi syarat:
  - `amount_realized` terisi dan lebih besar dari 0.
  - `attachment_path` terisi.
- Saat syarat lengkap, `BusinessTripCashAdvanceController` mengupdate lifecycle:
  - `trip_report`
    - `status`: `complete`
    - `actor_id`: user staff yang submit cash advance report
    - `happened_at`: waktu submit
  - `reimbursement_submitted`
    - `status`: `pending`
    - `actor_id`: `null`
    - `happened_at`: `null`
- Step 7 pending menjadi penanda bahwa tombol Reimbursement boleh aktif.

### 3) Penghapusan Expense Item

- Menghapus model dan controller `BusinessTripExpenseItem`.
- Menghapus migration `create_business_trip_expense_items_table`.
- Menghapus relasi `expenseItems()` dari model `BusinessTrip`.
- Alur expense saat ini memakai cash advance approved dan reimbursement untuk summary, bukan tabel expense item terpisah.

### 4) Detail Business Trip - Permission dan Placeholder

- Nilai biaya perjalanan, cash advance, dan reimbursement tidak ditampilkan sebelum syarat terpenuhi.
- Tombol action dibuat readonly/disabled sampai Supervisor Review approved.
- Placeholder card tetap dipertahankan agar style tidak kosong:
  - Expense muncul setelah cash advance approved.
  - Cash Advance muncul setelah staff submit cash advance di Phase 2.
  - Reimbursement muncul setelah staff submit report dan attachment yang diperlukan.
- Total Expenses sampai Total Payment tidak dihapus, tetapi ditampilkan sebagai pending sampai data siap.
- Tombol action dipisah:
  - Cash Advance aktif setelah Supervisor Review approved.
  - Reimbursement aktif hanya saat lifecycle `reimbursement_submitted` berstatus `pending`.

### 5) Expense Tab

- List Expense tidak lagi memakai dummy statis.
- Item Expense aktif dari `business_trip_cash_advances` yang `status = approved`.
- Tanggal Expense memakai kolom `approved_at`.
- Nilai yang tampil memakai `amount_approved`.
- Link `Attachment` pada Expense membuka modal.
- Jika belum ada attachment, modal menampilkan pesan merah:
  - `Belum mengupload attachment.`

### 6) Breakdown Biaya di Kartu Kiri

- Bagian Transportation, Local Transportation, Accommodation, Meals & Entertainment, dan Others tidak lagi hardcoded.
- Data breakdown hanya diambil dari `business_trip_cash_advances` dengan status `approved`.
- Nominal menggunakan `amount_approved`.
- Deskripsi menggunakan `notes`.
- Kategori tanpa data approved tetap tampil `-` agar layout stabil.

### 7) Financial Summary

- Summary Expense sekarang dihitung dari data:
  - `Cash Advance`: total cash advance approved.
  - `Total Expenses`: cash advance approved + reimbursement staff.
  - `Balance Due`: selisih yang perlu dibayar ke employee.
  - `Trip Incentive`: `Rp 100.000 x total_days`.
  - `Total Payment`: `Balance Due + Trip Incentive`.
- Nominal Cash Advance diberi style:
  - `text-success` jika Total Expenses sama dengan Cash Advance.
  - `text-danger` jika Total Expenses lebih besar dari Cash Advance.
- Setelah row Cash Advance ditambahkan garis pemisah.
- Di bawah Balance Due ditampilkan:
  - `Reimbursement to Employee`
- Di bawah Total Payment ditampilkan:
  - `Company to Employee`

### 8) Requested Cash Advance, Incentive, dan Status

- Bagian Requested Cash Advance tidak lagi hardcoded.
- Nilai Requested Cash Advance mengambil total `amount_approved` dari cash advance approved.
- Deskripsi Requested Cash Advance mengambil notes dari cash advance approved.
- Business Trip Incentive tetap hardcoded rate `Rp 100.000`, tetapi dikalikan `total_days`.
- Status Cash Advance:
  - `Approved` jika semua cash advance approved.
  - selain itu `Pending`.
- Status Reimbursement:
  - `Approved` jika semua reimbursement approved.
  - selain itu `Pending`.
- Status Incentive:
  - `Paid` jika `business_trips.payment_status = paid`.
  - selain itu `Pending`.

### 9) Reimbursement Store/Update

- Menambahkan route POST untuk store/update reimbursement:
  - `attendance.business-trips.reimbursements.store`
- Form `create-reimbursement.blade.php` sekarang submit multipart ke controller.
- Staff dapat create/update/delete row reimbursement berdasarkan row yang dikirim dari form.
- Receipt reimbursement wajib diupload untuk row baru.
- Receipt existing tetap dipertahankan saat update jika staff tidak upload file baru.
- File receipt disimpan di disk `public` pada folder:
  - `business-trip-reimbursement-receipts/{business_trip_id}`
- Detail tab Reimbursement sekarang mengambil data dari tabel `business_trip_reimbursements`, bukan dummy statis.
- Total Reimbursement di detail dihitung dari data reimbursement.
- Submit reimbursement mengupdate lifecycle:
  - `reimbursement_submitted` menjadi `complete`
  - `actor_id` dan `happened_at` diisi dari user staff dan waktu submit.
- Submit reimbursement tidak lagi mengubah `trip_report`; step 6 sudah diselesaikan oleh cash advance report.

### 10) Cash Advance Date Range

- Request Cash Advance Date diubah dari single date menjadi date range.
- Format input:
  - `DD/MM/YYYY - DD/MM/YYYY`
- Menambahkan kolom `date_needed_until` pada tabel `business_trip_cash_advances`.
- `date_needed` menyimpan tanggal awal range.
- `date_needed_until` menyimpan tanggal akhir range.
- Detail Cash Advance menampilkan range jika tanggal awal dan akhir berbeda.

### 11) Cron Job Lifecycle

- Menambahkan command:
  - `business-trips:lifecycle:sync`
- Menambahkan scheduler harian di `routes/console.php`:
  - jam `00:05`
  - timezone `Asia/Jakarta`
  - `withoutOverlapping()`
- Menambahkan runner script:
  - `cronJob/Schedule/run-business-trip-lifecycle-sync.bat`
  - `cronJob/Schedule/run-business-trip-lifecycle-sync.sh`
- Logic command:
  - hanya memproses trip yang sudah supervisor approved.
  - saat tanggal dinas mulai sampai end date, `trip_execution` menjadi `pending`.
  - setelah end date lewat, `trip_execution` menjadi `complete` dan `trip_report` menjadi `pending`.
  - status rejected/cancelled/failed tidak ditimpa otomatis.
- Actor cron:
  - `business_trips.employee_id` dipakai cron untuk mengambil `employees.user_id`.
  - Nilai tersebut hanya dipakai mengisi `actor_id` pada lifecycle `trip_execution` step 5.
  - Tampilan tracker tidak memakai fallback actor dari `business_trips.employee_id`; actor di UI hanya tampil jika `business_trip_lifecycle_logs.actor_id` terisi.
- Date/time lifecycle Phase 3:
  - Saat `trip_execution` pending, marker kiri tampil `Now`.
  - Saat `trip_execution` complete, marker kiri memakai `end_date`, contoh `15 Jun`.
  - Field `Datetime` Phase 3 tetap memakai range `business_trips.start_date - business_trips.end_date`.
- Phase 4 dari cron:
  - Setelah lewat `end_date`, `trip_report` dapat menjadi `pending`.
  - `happened_at` memakai tanggal selesai dinas (`end_date` end of day).
  - `actor_id` tidak diisi dari `business_trips.employee_id` untuk step 6.
  - Step 6 baru complete setelah staff mengisi cash advance report lengkap.

### 12) Test dan Verifikasi

- Menambahkan dan memperbarui test untuk:
  - cleanup controller/model/migration expense item.
  - cash advance store/update.
  - detail page wiring dan data dinamis.
  - lifecycle scheduler.
  - kalkulasi financial summary.
  - breakdown cash advance approved.
  - attachment modal.
  - date range cash advance.
  - store/update reimbursement.
  - aktivasi tombol Reimbursement berdasarkan status pending step 7.
  - completion step 6 dari seluruh `amount_realized` + attachment cash advance.
  - actor lifecycle hanya dari `actor_id` log, bukan fallback business trip employee.
- Test yang sudah dijalankan selama perubahan:
  - `php artisan test --compact tests/Feature/BusinessTripPageCleanupTest.php`
  - `php artisan test --compact tests/Feature/BusinessTripCashAdvanceStoreTest.php`
  - `php artisan test --compact tests/Feature/BusinessTripReimbursementStoreTest.php`
  - `php artisan test --compact tests/Feature/BusinessTripLifecycleScheduleTest.php`
  - `php artisan test --compact tests/Feature/BusinessTripDetailControllersTest.php tests/Feature/BusinessTripDetailModelsTest.php tests/Feature/BusinessTripDetailTablesMigrationTest.php tests/Feature/BusinessTripPageCleanupTest.php`
  - `php artisan test --compact tests/Feature/BusinessTripPageCleanupTest.php tests/Feature/BusinessTripCashAdvanceStoreTest.php tests/Feature/BusinessTripReimbursementStoreTest.php`
- Catatan test lokal:
  - Sebagian test database behavior bisa `skipped` jika SQLite PDO tidak tersedia di environment local.
- Formatter dijalankan:
  - `vendor/bin/pint --dirty --format agent`

## File Utama yang Terdampak

- `app/Http/Controllers/BusinessTripController.php`
- `app/Http/Controllers/BusinessTripCashAdvanceController.php`
- `app/Console/Commands/SyncBusinessTripLifecycleStatus.php`
- `app/Models/BusinessTrip.php`
- `routes/web.php`
- `routes/console.php`
- `resources/views/attendance/business-trips/detail.blade.php`
- `resources/views/attendance/business-trips/create-cash-advance.blade.php`
- `resources/views/attendance/business-trips/create-reimbursement.blade.php`
- `database/migrations/2026_06_05_062930_add_date_needed_until_to_business_trip_cash_advances_table.php`
- `database/migrations/2026_06_03_080205_create_business_trip_lifecycle_logs_table.php`
- `cronJob/Schedule/run-business-trip-lifecycle-sync.bat`
- `cronJob/Schedule/run-business-trip-lifecycle-sync.sh`
- `tests/Feature/BusinessTripPageCleanupTest.php`
- `tests/Feature/BusinessTripCashAdvanceStoreTest.php`
- `tests/Feature/BusinessTripReimbursementStoreTest.php`
- `tests/Feature/BusinessTripLifecycleScheduleTest.php`
- `tests/Feature/BusinessTripDetailControllersTest.php`
- `tests/Feature/BusinessTripDetailModelsTest.php`
- `tests/Feature/BusinessTripDetailTablesMigrationTest.php`

## Panduan Lanjutan untuk AI/Developer

Bagian ini dibuat agar penerus project bisa langsung melanjutkan tanpa membaca seluruh history percakapan.

### Source of Truth

- Trip Lifecycle Tracker harus mengambil data dari `business_trip_lifecycle_logs`.
- Nilai expense yang tampil di tab Expense dan breakdown kiri berasal dari `business_trip_cash_advances` yang `status = approved`.
- Nilai yang dipakai untuk expense approved adalah `amount_approved`, bukan `amount_requested` atau `amount_realized`.
- Tanggal expense approved memakai `approved_at`.
- Reimbursement staff disimpan di `business_trip_reimbursements`.
- Tabel `business_trip_expense_items` sudah tidak dipakai dan sudah dihapus dari flow.

### Lifecycle Flow Saat Ini

- Step 1 `submitted`: dibuat saat staff create Business Trip.
- Step 2 `supervisor_review`: menunggu/menyimpan approval supervisor.
- Step 3 `cash_advance_submitted`: diupdate saat staff submit Cash Advance.
- Step 4 `finance_disbursement`: untuk approval/disbursement finance cash advance.
- Step 5 `trip_execution`: diupdate otomatis oleh cron `business-trips:lifecycle:sync`.
- Step 6 `trip_report`: menjadi `pending` setelah tanggal selesai dinas lewat, lalu menjadi `complete` saat semua cash advance report lengkap.
- Step 7 `reimbursement_submitted`: menjadi `pending` setelah step 6 complete, lalu menjadi `complete` saat staff submit reimbursement.
- Step 8 dan 9 masih disiapkan untuk final finance verification dan payment distribution.

### Cron Job Rules

- Command: `php artisan business-trips:lifecycle:sync`.
- Scheduler: `routes/console.php`, jalan setiap hari `00:05` timezone `Asia/Jakarta`.
- Runner manual:
  - Windows: `cronJob/Schedule/run-business-trip-lifecycle-sync.bat`
  - Linux: `cronJob/Schedule/run-business-trip-lifecycle-sync.sh`
- Cron hanya memakai `business_trips.employee_id -> employees.user_id` untuk mengisi `actor_id` pada `trip_execution` step 5.
- Cron tidak boleh memakai `business_trips.employee_id` sebagai actor UI fallback.
- Jika `actor_id` di lifecycle log kosong, UI harus tampil `Actor : -`.
- Setelah lewat `end_date`, cron boleh membuat `trip_report` menjadi `pending` dan mengisi `happened_at` dari `end_date`, tetapi `actor_id` step 6 tidak diisi dari employee.

### Phase 3 Display Rules

- Jika `trip_execution` pending:
  - marker kiri: `Now`
  - datetime: range `start_date - end_date`
- Jika `trip_execution` complete:
  - marker kiri: `end_date`, contoh `15 Jun`
  - datetime: tetap range `start_date - end_date`
- Actor Phase 3 tampil dari `business_trip_lifecycle_logs.actor_id`.

### Cash Advance Report Rules

- Form Cash Advance menyimpan:
  - `date_needed`
  - `date_needed_until`
  - `amount_requested`
  - `amount_realized`
  - `attachment_path`
  - `category`
  - `notes`
- Attachment cash advance disimpan di disk `public` folder `business-trip-cash-advance-attachments/{business_trip_id}`.
- Step 6 `trip_report` hanya menjadi `complete` kalau semua row `business_trip_cash_advances` pada trip tersebut punya:
  - `amount_realized > 0`
  - `attachment_path` terisi
- Setelah step 6 complete, sistem mengubah step 7 `reimbursement_submitted` menjadi `pending`.

### Button Permission Rules

- Tombol Cash Advance aktif setelah Supervisor Review approved.
- Tombol Reimbursement tidak mengikuti Supervisor Review langsung.
- Tombol Reimbursement hanya aktif jika lifecycle `reimbursement_submitted` statusnya `pending`.
- Setelah staff submit reimbursement, `reimbursement_submitted` berubah menjadi `complete`.

### File yang Biasanya Perlu Dicek Saat Lanjut

- Detail view dan permission:
  - `app/Http/Controllers/BusinessTripController.php`
  - `resources/views/attendance/business-trips/detail.blade.php`
- Cash advance create/update/report:
  - `app/Http/Controllers/BusinessTripCashAdvanceController.php`
  - `resources/views/attendance/business-trips/create-cash-advance.blade.php`
- Reimbursement create/update:
  - `app/Http/Controllers/BusinessTripReimbursementController.php`
  - `resources/views/attendance/business-trips/create-reimbursement.blade.php`
- Lifecycle cron:
  - `app/Console/Commands/SyncBusinessTripLifecycleStatus.php`
  - `routes/console.php`

### Test Rujukan

- `tests/Feature/BusinessTripPageCleanupTest.php`
- `tests/Feature/BusinessTripCashAdvanceStoreTest.php`
- `tests/Feature/BusinessTripReimbursementStoreTest.php`
- `tests/Feature/BusinessTripLifecycleScheduleTest.php`

Gunakan command terfokus sebelum finalisasi:

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact tests/Feature/BusinessTripPageCleanupTest.php tests/Feature/BusinessTripCashAdvanceStoreTest.php tests/Feature/BusinessTripReimbursementStoreTest.php tests/Feature/BusinessTripLifecycleScheduleTest.php
```

## Catatan Lanjutan

- Approval finance cash advance perlu action admin/finance terpisah untuk mengisi `amount_approved`, `approved_at`, dan status approved.
- Reimbursement dan final payment dapat dibuat lebih lengkap setelah report phase dan finance verification selesai.
