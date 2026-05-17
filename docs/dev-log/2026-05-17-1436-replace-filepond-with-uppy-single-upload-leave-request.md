# Ganti Lampiran Izin dari FilePond ke Uppy (single upload)

- `resources/views/absensi/izin.blade.php`:
  - Hapus input multiple `attachment_files[]` berbasis FilePond.
  - Ganti ke Uppy Dashboard inline (`max 1 file`, JPG/JPEG/PNG/PDF, maks 5MB).
  - Submit AJAX sekarang kirim field tunggal `attachment_file`.
  - Reset uploader saat modal ditutup/sukses submit.
  - Tampilkan nama file lampiran di modal detail.
- `app/Http/Controllers/LeaveRequestController.php`:
  - Validasi upload ganti ke `attachment_file` tunggal.
  - Simpan file ke storage public dan path-nya ke kolom `leave_requests.attachment_path`.
  - Hapus proses create ke tabel `leave_request_attachments`.
  - Endpoint `show` sekarang membentuk data `attachments` dari `attachment_path`.
  - Proses `destroy` hapus file berdasarkan `attachment_path`.
- `routes/web.php`:
  - Hapus route `absensi.izin.attachment` karena tidak dipakai lagi.
