# Modal Detail Leave History Sick dan Non-Sick

## Ringkasan
- Mengubah modal detail Leave List menjadi format ringkas berisi intro, Leave Type, Reason, Leave Duration, dan Status.
- Menambahkan variasi modal Sick Leave dengan judul `Attendance Sick`, copy kesehatan, dan preview `Medical Notes`.
- Menambahkan data detail dari controller untuk modal: `modal_title`, `detail_leave_type`, `is_sick_leave`, `status_date_label`, dan `attachment_url`.

## File Terkait
- `app/Http/Controllers/LeaveRequestController.php`
- `resources/views/attendance/leave-requests/index.blade.php`
- `tests/Feature/LeaveHistoryYearFilterTest.php`
