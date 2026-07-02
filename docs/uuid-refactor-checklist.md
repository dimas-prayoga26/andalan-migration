# UUID Refactor Checklist

Dokumen ini merangkum semua area yang masih perlu dirombak agar konsisten dengan custom UUID (`ddmmyyyy + urutan 4 digit`) di seluruh domain utama.

## 1. Blocker Kritis

- [ ] `composer.lock` masih mengandung merge conflict marker (`<<<<<<<`, `=======`, `>>>>>>>`).
  - Dampak: dependency state tidak valid dan berisiko memicu error composer.
  - File: `composer.lock`

## 2. Konsistensi Primary Key Domain

- [ ] Tentukan apakah semua tabel domain ikut custom UUID, atau hanya sebagian.
  - Saat ini campuran:
    - Sudah UUID/custom: `users`, `roles`, `permissions`, `attendances`
    - Masih auto-increment: `companies`, `attendance_permissions`, `attendance_logs`, `attendances_overtime`, `leave_balances`, `user_profiles`, `user_documents`, `user_employments`, `attendance_permission_attachments`, dan metadata tables.

## 3. Mismatch Foreign Key yang Perlu Disinkronkan

- [ ] `attendance_permission_attachments.attendance_permission_id` masih `unsignedBigInteger`; pastikan cocok dengan PK `attendance_permissions`.
  - File: `database/migrations/2026_05_06_080731_create_attendance_permission_attachments_table.php`

- [ ] `rules_of_attendaces.companies_id` dan `meta_data_leave_companies.company_id` masih `foreignId`; jika `companies` nanti diubah ke UUID, dua kolom ini wajib ikut.
  - File: `database/migrations/2026_05_05_070254_create_rules_of_attendaces_table.php`
  - File: `database/migrations/2026_05_06_071038_create_meta_data_leave_companies_table.php`

- [ ] `2026_04_28_071845_add_company_id_to_users_table.php` masih `foreignId`; walau mungkin sudah tidak dipakai (karena ada migration drop), tetap perlu konsisten jika bootstrap dari nol.
  - File: `database/migrations/2026_04_28_071845_add_company_id_to_users_table.php`

## 4. Model yang Perlu Penyeragaman Key Type

- [ ] Model domain yang PK tabelnya nanti ikut UUID perlu diset:
  - `protected $keyType = 'string';`
  - `public $incrementing = false;`
  - custom generator di event `creating`.

- [ ] Kandidat model untuk diseragamkan (jika tabelnya ikut UUID):
  - `App\Models\Company`
  - `App\Models\AttendancePermission`
  - `App\Models\AttendanceLog`
  - `App\Models\AttendanceOvertime`
  - `App\Models\UserEmployment`
  - `App\Models\AttendancePermissionAttachment`

## 5. Migration Konversi Metadata Employment/Profile

- [ ] Review migration konversi metadata agar kompatibel penuh dengan state UUID final.
  - `database/migrations/2026_05_04_080751_convert_user_employment_metadata_to_foreign_keys.php`
  - `database/migrations/2026_05_04_090355_convert_user_profile_gender_and_marital_status_to_foreign_keys.php`
  - `database/migrations/2026_05_04_090707_drop_company_id_from_users_table.php`
  - Fokus: jangan ada asumsi tipe integer pada PK/FK jika tabel induk nanti berubah ke UUID.

## 6. Seeder yang Masih Asumsi Integer

- [ ] `LeaveBalanceSeeder` masih cast `user->id` ke `(int)`.
  - Dampak: UUID string bisa jadi `0`, perhitungan salah.
  - File: `database/seeders/LeaveBalanceSeeder.php`

- [ ] Audit semua seeder untuk operasi aritmatika terhadap `_id` string.
  - Target umum: hilangkan cast `(int)` untuk ID UUID.

## 7. Controller/Query Assumption Audit Lanjutan

- [ ] Lanjut audit cast `(int)` yang tersisa untuk kolom ID relasional.
  - `AttendancePermissionController`, `AttendanceOvertimeController`, `AttendanceController` sudah sebagian disesuaikan, tetapi perlu verifikasi end-to-end setelah skema final diputuskan.

## 8. Strategi Migrasi Data

- [ ] Tentukan jalur implementasi:
  - Opsi A: `migrate:fresh --seed` (untuk environment dev yang boleh reset data)
  - Opsi B: migration konversi bertahap untuk database existing (produksi/staging)

- [ ] Jika Opsi B:
  - tambah kolom UUID baru,
  - backfill data,
  - ubah FK secara bertahap,
  - swap PK lama ke PK baru,
  - update relasi aplikasi.

## 9. Verifikasi Setelah Rombak

- [ ] Jalankan:
  - `php artisan migrate:fresh --seed` (dev)
  - `php artisan test --compact`
  - uji flow: login, absen masuk/pulang, izin, lembur, assign role, permission check.

