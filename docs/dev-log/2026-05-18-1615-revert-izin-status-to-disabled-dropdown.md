# Revert Status Column UI to Disabled Dropdown

## Ringkasan
- UI kolom `Status` pada halaman `absensi/izin` dikembalikan ke bentuk dropdown seperti sebelumnya.

## Perubahan
- File: `resources/views/absensi/izin.blade.php`
  - Render kolom `Status` di DataTable:
    - kembali selalu memakai `<select class="permission-status-select">`
    - kondisi disable: `!canUpdatePermissionStatus || isFinalStatus`
  - Hapus logic render badge untuk status.

## Dampak
- Tampilan status kembali konsisten dengan model lama (dropdown disabled).
