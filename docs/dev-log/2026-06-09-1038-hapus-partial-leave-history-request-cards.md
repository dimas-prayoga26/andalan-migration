# Hapus Partial Leave History dan Request Cards

## Ringkasan
- Menghapus `history-cards.blade.php` karena sudah tidak dipakai setelah balance analytic dipindahkan ke `history-list-cards.blade.php`.
- Menghapus `request-cards.blade.php` dan mengganti response filter AJAX Leave List menjadi data `cards`.
- Menambahkan render Leave List di JavaScript `index.blade.php` agar filter tetap bisa memperbarui card tanpa partial terpisah.

## File Terkait
- `app/Http/Controllers/LeaveRequestController.php`
- `resources/views/attendance/leave-requests/index.blade.php`
- `resources/views/attendance/leave-requests/partials/history-cards.blade.php`
- `resources/views/attendance/leave-requests/partials/request-cards.blade.php`
- `tests/Feature/LeaveHistoryYearFilterTest.php`
