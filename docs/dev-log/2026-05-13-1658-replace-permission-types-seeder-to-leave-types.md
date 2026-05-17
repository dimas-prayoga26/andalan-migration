# Update Seeder leave_types dan hapus permission_types

- Ganti sumber data tipe izin dari `meta_data_permission_types` ke `leave_types`.
- Tambah seeder baru `LeaveTypeSeeder` untuk isi data:
  - Sakit
  - Cuti Khusus
  - Cuti Tahunan
  - Izin Dinas Dalam Kota
  - Izin Dinas Luar Kota
- Hapus file seeder lama `MetaDataPermissionTypeSeeder.php`.
- Update `DatabaseSeeder` agar memanggil `LeaveTypeSeeder`.
- Update `LeaveBalanceSeeder` agar tidak lagi baca kolom `permission_types`, sekarang join ke `leave_types.name`.
- Update `LeaveRequestController` untuk ambil opsi tipe izin dari tabel `leave_types` aktif.
- Fix `LeaveTypeSeeder` agar mengisi kolom `id` (UUID) saat insert data baru di tabel `leave_types`.
