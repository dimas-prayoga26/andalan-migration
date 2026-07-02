# Restrict Leave Request Delete by Role

## Ringkasan
- Tombol hapus pengajuan izin sekarang disembunyikan untuk role Staff.
- Hapus data pengajuan hanya diizinkan untuk role Director dan Superuser.

## Perubahan
- File: `app/Http/Controllers/LeaveRequestController.php`
  - `index()` menambahkan flag view `canDeletePermission`.
  - `destroy()` tidak lagi mengizinkan owner/staff menghapus datanya sendiri.
  - `destroy()` sekarang memakai helper baru `canDeletePermissionRequest()`.
  - Helper `canDeletePermissionRequest()`:
    - `Superuser` => boleh hapus semua.
    - `Board/Director` => boleh hapus dalam scope company yang sama.
    - Role lain => ditolak.

- File: `resources/views/absensi/izin.blade.php`
  - Tambah JS variable `canDeletePermission` dari backend.
  - Tombol delete di kolom action hanya dirender jika `canDeletePermission = true`.
  - `deleteData()` guard tambahan agar tidak jalan kalau role tidak diizinkan.

## Dampak
- Staff tidak bisa menghapus list pengajuan.
- Director/Superuser bisa menghapus pengajuan sesuai hak akses yang diminta.
