# Preview Attachment Business Trip di Modal

Tanggal: 2026-06-05 15:42 WIB

## Ringkasan

- Mengubah link attachment expense dan receipt reimbursement agar tidak membuka tab baru.
- Menambahkan modal preview tunggal `businessTripAttachmentPreviewModal`.
- Attachment/receipt ditampilkan langsung di modal melalui iframe.
- Iframe dibersihkan saat modal ditutup agar file tidak tetap aktif di background.

## File Perubahan

- `resources/views/attendance/business-trips/detail.blade.php`
- `tests/Feature/BusinessTripAttachmentModalPreviewTest.php`

## Verifikasi

- Formatter:
  - `vendor/bin/pint --dirty --format agent`
- Test terfokus:
  - `php artisan test --compact tests/Feature/BusinessTripAttachmentModalPreviewTest.php`
