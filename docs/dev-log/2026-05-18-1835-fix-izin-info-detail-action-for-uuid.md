# Fix Info Detail Action on Izin Table

## Ringkasan
- Memperbaiki tombol `info detail` pada tabel izin agar berfungsi untuk ID UUID.
- Menyesuaikan akses endpoint detail supaya bisa dipakai juga oleh Director/Superuser sesuai otorisasi perusahaan.

## Perubahan
- File: `resources/views/absensi/izin.blade.php`
  - Action button tidak lagi memakai inline `onclick` dengan ID mentah.
  - Diganti ke `data-id` + delegated event:
    - `.js-izin-info` -> `infoData(permissionId)`
    - `.js-izin-delete` -> `deleteData(permissionId)`
  - Ini mencegah gagal parse JS saat `id` berbentuk UUID (`xxxx-xxxx-...`).

- File: `app/Http/Controllers/LeaveRequestController.php`
  - Method `show()` sekarang mengizinkan akses jika:
    - owner request (staff pemilik), atau
    - user punya hak manajemen status (`canManagePermissionStatus`) seperti Director/Superuser/Admin.

## Dampak
- Tombol info detail kembali bisa dibuka normal.
- Director/Superuser bisa melihat detail izin staff yang memang ada dalam scope otorisasinya.
