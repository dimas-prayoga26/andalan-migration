# Fix leave_requests pakai leave_type_id (hapus permission_types)

- Perbaiki `LeaveRequestController` agar semua query dan payload tidak lagi pakai kolom `permission_types`.
- Datatable sekarang select `leave_type_id` dan ambil nama tipe dari relasi `leaveType`.
- Proses `store` sekarang hanya simpan `leave_type_id` (tanpa `permission_types`).
- Perhitungan summary cuti sekarang join ke tabel `leave_types` untuk filter `cuti tahunan` dan `cuti khusus`.
- Perbaiki normalisasi list tipe izin karena `id` sekarang UUID string, bukan numeric.
- Tambah model `LeaveType` + relasi `LeaveRequest::leaveType()`.
