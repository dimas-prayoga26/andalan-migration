# Google Drive OAuth Token Folder ID

Tanggal: 2026-08-24 14:25 WIB

## Ringkasan

- Menambahkan kolom `folder_id` pada tabel `project_division_event` untuk menyimpan ID folder Google Drive final.
- Menambahkan tabel `google_oauth_tokens` yang berelasi ke `users` melalui `user_id`.
- Token OAuth Google disimpan terenkripsi melalui cast model `encrypted`.
- Menambahkan endpoint server-side untuk exchange authorization code Google dan mengambil/refresh access token dari database.
- Frontend Drive memakai Google OAuth code flow dan meminta token sementara dari server saat butuh Picker/Drive API.

## File yang Berubah

- `database/migrations/2026_08_24_142536_add_folder_id_to_project_division_event_table.php`
- `database/migrations/2026_08_24_142537_create_google_oauth_tokens_table.php`
- `app/Models/GoogleOauthToken.php`
- `app/Models/User.php`
- `app/Http/Controllers/GoogleDriveOAuthController.php`
- `app/Http/Controllers/ProjectManagement/ProjectController.php`
- `routes/web.php`
- `resources/views/project_management/projects/detail.blade.php`
- `tests/Feature/ProjectManagementOverviewLayoutTest.php`

## Test dan Verifikasi

- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=monthly_overview_cards_use_equal_height_layout`
- `php artisan test --compact tests/Feature/ProjectManagementOverviewLayoutTest.php --filter=test_google_drive_oauth_code_exchange_stores_token_for_authenticated_user`
- `php artisan migrate --pretend`
- `php artisan migrate --force`
- `php artisan tinker --execute "dump(Schema::hasColumn('project_division_event', 'folder_id')); dump(Schema::hasTable('google_oauth_tokens')); dump(Schema::getColumnListing('google_oauth_tokens'));"`

Hasil terakhir:
- Layout/detail project: `1 passed (734 assertions)`.
- Test OAuth database: `1 skipped` karena driver SQLite test tidak tersedia di environment ini.
- Migration berhasil membuat `project_division_event.folder_id` dan tabel `google_oauth_tokens`.
