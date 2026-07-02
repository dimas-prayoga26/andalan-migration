# Rangkuman Final Leave History Card, Modal, dan Dropdown

## Ringkasan
- Menyesuaikan ikon pada card Leave List agar memakai asset SVG sesuai tipe leave:
  - `annual_leave.svg`
  - `sick_leave.svg`
  - `special_leave.svg`
  - `unpaid_leave.svg`
- Menambahkan `icon_file` dari `LeaveRequestController` berdasarkan `leave_types.code` dan `leave_types.name`, sehingga render awal dan render hasil filter AJAX memakai ikon yang sama.
- Mengubah modal detail Leave List menjadi modal ringkas:
  - Non-sick leave memakai judul dan copy `Out of Office mode: ON`.
  - Sick Leave memakai judul `Attendance Sick`, copy kesehatan, dan bagian `Medical Notes`.
- Menampilkan gambar `Medical Notes` dari `attachment_path` pada tabel `leave_requests`.
- Jika `attachment_path` kosong, `Medical Notes` memakai fallback `public/assets/not_available_images.png`.
- Preview `Medical Notes` dapat diklik dan membuka gambar di tab baru.
- Mengubah format period untuk cuti lebih dari 1 hari menjadi `20 May - 21 May 2026 (2 days)`.
- Menjaga format cuti 1 hari tetap `20 May 2026 (1 day)`.
- Mengubah card Leave List agar modal detail dibuka manual lewat JavaScript, bukan langsung dari atribut Bootstrap pada card.
- Menambahkan area aksi `.leave-history-card-actions` supaya klik dropdown tidak ikut membuka modal detail card.
- Mengubah tombol dropdown `View` agar membuka modal detail yang sama dengan klik card.
- Menyamakan render awal dan render AJAX setelah filter agar sama-sama memiliki dropdown `View`, `Update`, dan `Delete`.

## File Terkait
- `app/Http/Controllers/LeaveRequestController.php`
- `resources/views/attendance/leave-requests/index.blade.php`
- `tests/Feature/LeaveHistoryYearFilterTest.php`

## Verifikasi
- `vendor\bin\pint --dirty --format agent`
- `php -l app\Http\Controllers\LeaveRequestController.php`
- `php -l tests\Feature\LeaveHistoryYearFilterTest.php`
- `php artisan test --compact tests\Feature\LeaveHistoryYearFilterTest.php`

## Catatan
- PHP lokal masih menampilkan warning `Module "mysqli" is already loaded`, tetapi formatter, lint, dan test tetap berhasil.
