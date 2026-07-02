# Update Leave Approval Actor Columns

## Ringkasan
- Saat status izin diubah oleh role **Board of Directors** atau **Superuser**, sistem sekarang ikut mengisi:
  - `approved_by`
  - `approved_at`

## Perubahan Teknis
- File: `app/Http/Controllers/LeaveRequestController.php`
  - Method `updateStatus()`:
    - Menyusun payload update status.
    - Jika updater adalah Board of Directors / Superuser dan status menjadi final (`approved` atau `refused`):
      - `approved_by` diisi `id` user yang melakukan approval.
      - `approved_at` diisi waktu saat update.
  - Tambah helper `isSuperuser()` untuk pengecekan role khusus superuser.

## Dampak
- Riwayat approval lebih lengkap karena aktor dan timestamp approval tercatat otomatis pada tabel `leave_requests`.
