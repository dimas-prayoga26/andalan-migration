# Fix Attachment Preview in Izin Detail

## Ringkasan
- Memperbaiki URL lampiran pada modal info detail izin agar file bisa dibuka meski environment tidak serve langsung dari `public`/`storage` URL.

## Perubahan
- File: `routes/web.php`
  - Tambah route baru:
    - `GET /absensi/izin/{leaveRequest}/attachment`
    - Name: `absensi.izin.attachment`

- File: `app/Http/Controllers/LeaveRequestController.php`
  - `show()`:
    - `attachments.file_url` sekarang pakai route `absensi.izin.attachment` (bukan `Storage::url('/storage/...')`).
  - Tambah method `showAttachment(LeaveRequest $leaveRequest)`:
    - Validasi akses (owner atau role manajemen).
    - Validasi path lampiran dan file existence.
    - Return `response()->file(...)` dengan `Content-Disposition: inline` supaya bisa langsung dilihat di browser.

## Dampak
- Klik lampiran di modal info detail tidak lagi gagal 404 karena mismatch webroot/symlink.
- Lampiran tetap aman karena akses dicek via authorization controller.
