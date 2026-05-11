# Catatan Dev Log

Aturan log:
- Setiap perubahan dibuatkan file baru di `docs/dev-log/`.
- Nama file: `YYYY-MM-DD-jam-singkat-topik.md`.
- `docs/catatan-dev-log.md` dipakai sebagai ringkasan harian + index file detail.

## Ringkasan Harian

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

### 2026-05-12
- `2026-05-12-0617-refactor-uuid-company-users-overtimes.md`

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
