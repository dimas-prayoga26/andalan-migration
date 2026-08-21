# Staff Add Task Division Event

Tanggal: 2026-08-21 10:58 WIB

## Ringkasan

- Memperbaiki tombol `+ Add Task` pada detail Project Management agar muncul untuk staff biasa yang sudah menjadi member aktif project dan sudah memiliki `current_event_division_id`.
- Sebelumnya tombol hanya muncul untuk event project admin karena `can_create_task` bergantung pada akses Google Drive/event admin.
- Event project admin tetap bisa menambahkan task untuk staff mana pun dalam division terkait.
- Staff biasa hanya mendapat assignee option untuk dirinya sendiri, dan backend hanya mengizinkan pembuatan task jika staff tersebut member aktif project pada event division yang dipilih.
- Endpoint store task project detail sekarang mengizinkan staff member project, bukan hanya event project admin.

## File yang Berubah

- `app/Http/Controllers/ProjectManagement/ProjectController.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`

## Test dan Verifikasi

- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=project`

Hasil terakhir: `2 passed, 6 skipped (687 assertions)`.

Catatan: test behavior database untuk skenario staff membuat task sendiri ditambahkan, tetapi skip di environment ini karena SQLite PDO tidak tersedia.
