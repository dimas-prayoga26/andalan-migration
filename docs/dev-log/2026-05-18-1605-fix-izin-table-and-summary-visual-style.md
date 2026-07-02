# Fix Visual Style Izin/Cuti (Summary Value & Table Columns)

## Ringkasan
- Merapikan style nilai summary card dan kolom tabel pada halaman `absensi/izin`.
- Mengubah tampilan kolom `Status` agar tidak terlihat seperti dropdown disabled untuk user non-approver.

## Perubahan
- File: `resources/views/absensi/izin.blade.php`
  - Update style summary:
    - `izin-summary-value` diperbesar dan dipertegas agar proporsional.
  - Rapikan style tabel:
    - Header kolom: font, warna, border, padding.
    - Value/body kolom: font-size, warna, vertical align, spacing.
    - Alignment kolom tertentu (`No`, `Durasi`, `Action`) jadi center.
  - Tambah style status:
    - `.izin-status-badge.approved` untuk status approved.
    - `.permission-status-select` untuk ukuran select yang konsisten.
  - Logic render DataTable kolom `Status`:
    - Jika user tidak punya hak update status -> tampil badge.
    - Jika status sudah final (`approved/refused`) -> tampil badge.
    - Dropdown hanya muncul untuk status `pending` ketika user memang punya hak update.

## Dampak
- Tampilan value summary tidak “berat sebelah”.
- Kolom tabel lebih rapi dan konsisten.
- Status lebih mudah dibaca, khususnya untuk staff (badge, bukan select disable).
