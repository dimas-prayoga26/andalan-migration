# Catatan Dev Log

Aturan log:
- Setiap perubahan dibuatkan file baru di `docs/dev-log/`.
- Nama file: `YYYY-MM-DD-jam-singkat-topik.md`.
- `docs/catatan-dev-log.md` dipakai sebagai ringkasan harian + index file detail.

## Ringkasan Harian

### 2026-06-09 (Selasa)
- Mengubah modal detail Leave List agar non-sick memakai format Out of Office dan Sick Leave memakai format Attendance Sick dengan preview Medical Notes.
- Menghapus partial `history-cards` dan `request-cards`; filter AJAX Leave List sekarang mengirim data `cards` dan dirender langsung dari `index.blade.php`.
- Memindahkan balance analytic ke partial `history-list-cards` dan memindahkan fragment AJAX Leave List ke partial `request-cards`.
- Memindahkan isi balance card analytic ke partial `history-cards` dan memisahkan fragment AJAX Leave List ke partial khusus.
- Memindahkan partial Leave Request agar row analytic `leave-balance-mobile-slider` yang terpisah, sementara render awal Leave List kembali inline di index.

### 2026-06-05 (Jumat)
- Memindahkan modal detail Leave Request dari card analytic ke card Leave List/history pengajuan time off.
- Memperluas trigger modal detail Annual Leave dan Sick Leave agar seluruh area card bisa diklik.
- Menambahkan modal detail Annual Leave dan Sick Leave yang terbuka dari kartu summary leave.
- Mengubah hitungan kartu summary Business Trip agar berbasis lifecycle, tanggal trip, cash advance, dan reimbursement.
- Mengubah nominal `Trip Incentive` dan `Total Payment` pada Business Trip menjadi hijau.
- Mengubah attachment Business Trip agar preview tampil di modal, bukan membuka tab baru.
- Mengubah lifecycle seed Business Trip `staff32` agar step 3 `Cash Advance Submitted` berstatus pending.
- Mengubah lifecycle seed Business Trip `staff31` agar berhenti di step 2 `Supervisor Review` dengan status pending.
- Membatasi list dan detail Business Trip untuk role staff agar hanya menampilkan trip milik staff login.
- Menambahkan seeder Business Trip untuk 4 staff RNB dengan variasi lifecycle sampai step 1, step 3, step 6, dan step 7.
- Menambahkan 4 akun staff aktif untuk company RNB melalui `UserSeeder`.
- Merapikan detail Business Trip agar lifecycle tracker, cash advance, expense, summary pembayaran, dan status memakai data database.
- Mengaktifkan create/update cash advance staff, termasuk date range, amount realized, attachment report per item, dan update lifecycle saat submit.
- Menghapus konsep `business_trip_expense_items` karena tidak dipakai pada flow saat ini.
- Menambahkan command + scheduler cron untuk sinkronisasi otomatis lifecycle trip execution/trip report berdasarkan tanggal dinas, termasuk actor step 5 dari user staff.
- Menambahkan modal attachment pada Expense dan kalkulasi financial summary dari cash advance approved, reimbursement, serta incentive.
- Mengaktifkan form reimbursement staff untuk create/update data dan upload receipt, dengan tombol Reimbursement aktif setelah step 7 pending.

### 2026-06-03 (Rabu)
- Merapikan struktur route/view Attendance ke nama Inggris dan memindahkan daily attendance ke `/attendance/today`.
- Mengaktifkan flow awal Business Trip: index dinamis, create/store, detail by id, link cash advance/reimbursement, serta proxy province/city dari `wilayah.id`.
- Menambahkan struktur tabel/model/controller pendukung Business Trip untuk expense items, cash advances, reimbursements, dan lifecycle logs.
- Memperbarui Leave Request: summary dipisah menjadi Eligibility dan Tracker, card mobile slider, `Unpaid Leave`, filter Leave List via AJAX, serta refresh card otomatis setelah `Request Time Off`.

### 2026-06-02 (Selasa)
- Mengubah sumber data hari libur pada Attendance Report dari API eksternal `libur.deno.dev` menjadi tabel lokal `attendances_holidays`.
- Menambahkan test terfokus untuk memastikan pemetaan libur nasional dan cuti bersama berasal dari database tanpa HTTP request eksternal.

### 2026-05-29 (Jumat)
- Penyelesaian modul `Leaves & Sick`: halaman izin memakai data database, form pengajuan aktif, validasi cuti khusus dan sakit ditambahkan, serta riwayat pengajuan dapat difilter berdasarkan tahun.
- Penyederhanaan konfigurasi cuti: hapus `meta_data_leave_companies` dan flag lama `is_monthly_limit_used`, tambah role `admin`, otorisasi hapus pengajuan, serta seeder histori cuti.
- Perbaikan horizontal scroll pada tabel report absensi.
- Penambahan kalender hari libur berbasis tabel `attendances_holidays`, termasuk seeder hari libur 2026 dan warna khusus untuk attendance exception.
- Statistik staff pada profile header dibuat dinamis dari database: `Late In`, `Leaves & Sick`, persentase kehadiran mingguan, `On-Time`, serta grafik progress bulanan.

### 2026-05-21 (Kamis)
- Penambahan halaman `Report` pada modul absensi beserta route dan controller terpisah.
- Revamp modal absensi: state `Clock In`/`Clock Out` dipisah, alur verifikasi onsite diperjelas, dan status tombol submit lebih ketat.
- Perubahan skema attendance: `distance` dipecah jadi `distance_in`/`distance_out` dan `late_grace_minutes` dihapus dari rules.
- Perbaikan logic backend: hitung `late_minutes`, hitung `work_hours` lebih akurat, serta update `location_out`/`distance_out` saat clock out.
- Perbaikan seeder agar kompatibel dengan skema baru.

### 2026-05-13 (Rabu)
- Perbaikan seeder pasca penghapusan tabel `meta_data_divisions` dan `meta_data_positions`.
- `DatabaseSeeder` dan `UserSeeder` disesuaikan ke `departments` + `positions` (UUID).
- Sinkronisasi `UserSeeder` dengan struktur `users` terbaru yang tidak lagi memakai kolom `name`.
- Tambah proteksi `try-catch` untuk proses controller (middleware global) dan seluruh `run()` seeder.

### 2026-05-12 (Selasa)
- Refactor UUID lanjutan: generator custom diubah ke format UUID style (`8-4-4-4-12`) dengan obfuscation.
- Tabel `companies` dipindah ke UUID dan seluruh foreign key terkait disesuaikan.
- Struktur `users` diselaraskan agar relasi perusahaan pakai `company_id` (`foreignUuid`) dan tanpa kolom `uid`.
- Tabel `rules_of_attendaces` dipindah ke UUID.
- Tabel `overtimes` disesuaikan agar `assigned_by` relasi ke `users.id` dan sinkron dengan model/controller.

### 2026-05-11 (Senin)
- Rapihin dan ubah flow modal absensi: hapus menu/tab yang tidak dipakai, title dinamis tanggal hari ini, spinner untuk loading IP, dan tombol detail pakai `onclick`.
- Ubah alur setelah absen sukses: redirect ke halaman project management dan perbaiki route error yang sempat terjadi.
- Mulai refactor UUID custom (`ddmmyyyy + urutan`) untuk model utama: `Role`, `Permission`, `User`, `Attendance`.
- Sinkronkan sebagian migration/FK dan seeder agar kompatibel dengan UUID custom.
- Buat checklist lanjutan area yang masih perlu dirombak.

### 2026-05-08 (Jumat)
- Update besar fitur absensi: detail absensi, geocoding lokasi, dan tampilan peta pada modal detail.
- Revamp aturan absensi (status, jam kerja, grace period) termasuk migration pendukung.
- Perbaikan modul lembur (controller + view).
- Tambah otomasi sinkronisasi saldo cuti bulanan via command + scheduler.

## File Detail Entry

### 2026-06-09
- `2026-06-09-1055-modal-detail-leave-history-sick-non-sick.md`
- `2026-06-09-1038-hapus-partial-leave-history-request-cards.md`
- `2026-06-09-1028-pindah-balance-ke-history-list-cards.md`
- `2026-06-09-1015-pindah-balance-card-ke-history-cards-partial.md`
- `2026-06-09-0945-pindah-partial-leave-balance-dan-inline-leave-list.md`

### 2026-06-05
- `2026-06-05-1642-pindah-modal-detail-leave-ke-card-history.md`
- `2026-06-05-1631-trigger-modal-leave-summary-di-seluruh-card.md`
- `2026-06-05-1624-modal-detail-annual-sick-leave-dari-card.md`
- `2026-06-05-1604-hitungan-summary-business-trip-berbasis-lifecycle.md`
- `2026-06-05-1550-trip-incentive-total-payment-hijau.md`
- `2026-06-05-1542-preview-attachment-business-trip-di-modal.md`
- `2026-06-05-1532-staff32-cash-advance-submitted-pending.md`
- `2026-06-05-1526-staff31-business-trip-supervisor-review-pending.md`
- `2026-06-05-1510-scope-business-trip-list-untuk-staff-login.md`
- `2026-06-05-1445-seeder-business-trip-rnb-staff-lifecycle.md`
- `2026-06-05-1415-tambah-empat-staff-rnb-di-user-seeder.md`
- `2026-06-05-1308-ringkasan-perubahan-business-trip-detail-cash-advance-lifecycle.md`

### 2026-06-03
- `2026-06-03-1701-ringkasan-perubahan-attendance-business-trip-leave.md`

### 2026-06-02
- `2026-06-02-1005-report-holidays-gunakan-table-lokal.md`

### 2026-05-29
- `2026-05-29-1704-ringkasan-perubahan-leaves-holidays-profile-stats.md`

### 2026-05-21
- `2026-05-21-1015-revamp-absensi-report-modal-dan-skema-attendance.md`

### 2026-05-13
- `2026-05-13-0110-fix-seeder-metadata-ke-departments-positions.md`
- `2026-05-13-0235-try-catch-controller-seeder.md`

### 2026-05-12
- `2026-05-12-0617-refactor-uuid-company-users-overtimes.md`
- `2026-05-12-1321-fix-pagination-style-tablelogs.md`
- `2026-05-12-1329-hapus-section-table-di-index-absensi.md`
- `2026-05-12-1333-nonaktifkan-js-datatable-index-absensi.md`
- `2026-05-12-1345-pindah-init-tablelogs-dari-profile-ke-index-absensi.md`
- `2026-05-12-1348-login-sessions-ganti-export-ke-dropdown-viewall.md`
- `2026-05-12-1350-hapus-table-logs-dan-login-sessions-col12.md`
- `2026-05-12-1354-rename-login-sessions-jadi-logs-dropdown-2.md`
- `2026-05-12-1356-tambah-1-opsi-dropdown-logs.md`
- `2026-05-12-1358-ubah-view-all-jadi-dropdown-kedua.md`
- `2026-05-12-1400-kembalikan-button-setelah-dua-dropdown-logs.md`
- `2026-05-12-1406-ubah-kolom-table-logs-jadi-no-namastaff-masuk-pulang-namapt-action.md`
- `2026-05-12-1416-aktifkan-kembali-table-logs-dinamis-database.md`
- `2026-05-12-1420-rename-button-presensi-clock-in-ke-check-in.md`
- `2026-05-12-1423-aktifkan-button-presensi-check-in-buka-modal-absen.md`
- `2026-05-12-1427-pindah-button-masuk-keluar-ke-footer-modal.md`
- `2026-05-12-1430-button-masuk-keluar-dibikin-lebar.md`
- `2026-05-12-1442-aktifkan-navigasi-profile-absensi-dengan-route-laravel.md`
- `2026-05-12-1450-sesuaikan-menu-presensi-izin-cuti-lembur.md`
- `2026-05-12-1452-overview-dan-list-tetap-ditampilkan-normal.md`
- `2026-05-12-1520-avatar-index-dinamis-dari-user-profiles-fallback-default.md`
- `2026-05-12-1553-sesuaikan-attendance-logs-uuid-dan-order-controller.md`
- `2026-05-12-1608-create-migration-departments-positions-name-status.md`
- `2026-05-12-1625-fix-fk-employee-deployments-departments-positions.md`
- `2026-05-12-1636-positions-departments-pakai-uuid.md`
- `2026-05-12-1641-seed-default-departments-name-di-migration.md`
- `2026-05-12-1648-create-position-seeder-untuk-table-positions.md`
- `2026-05-12-1655-create-migration-employee-profiles.md`
- `2026-05-12-1659-create-migration-employee-identities.md`
- `2026-05-12-1704-fix-blueprint-unsigneddecimal-employee-profiles.md`

### 2026-05-11
- `2026-05-11-1244-ringkasan-harian-catatan-dev-log.md`
- `2026-05-11-1243-uuid-refactor-checklist-doc.md`
- `2026-05-11-1231-attendance-custom-uuid.md`
- `2026-05-11-1223-user-model-custom-uuid-generator.md`
- `2026-05-11-1156-custom-uuid-spatie-role-permission.md`
- `2026-05-11-1145-fix-seeder-roles-uuid-dan-user-seeder-uuid.md`
- `2026-05-11-1051-setup-user-uuid-dan-relasi-user-id.md`
- `2026-05-11-1008-detail-button-onclick-style.md`
- `2026-05-11-0944-hapus-badge-memuat-ip-modal-absensi.md`
- `2026-05-11-0940-spinner-ip-saat-loading-di-modal-absensi.md`
- `2026-05-11-0934-cleanup-script-redundan-index-absensi.md`
- `2026-05-11-0922-title-modal-absen-dinamis-tanggal-hari-ini.md`
- `2026-05-11-0919-hapus-menu-absen-onsite-dan-ubah-title-modal.md`
- `2026-05-11-0844-fix-route-redirect-project-management.md`
- `2026-05-11-0830-redirect-absen-ke-project-management.md`
- `2026-05-11-0823-remove-business-trip-modal-auto-refresh.md`
- `2026-05-11-1530-format-log-per-file.md`

### 2026-05-08
- `2026-05-08-1645-attendance-leave-geocoding.md`
