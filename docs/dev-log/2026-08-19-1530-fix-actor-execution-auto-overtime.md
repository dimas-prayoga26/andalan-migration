# Fix Actor Execution Auto Overtime

Tanggal: 2026-08-19 15:30 WIB

## Ringkasan

- Memperbaiki `TwelveHourAutoOvertimeService` yang sebelumnya memakai satu actor (PIC/Supervisor) untuk seluruh lifecycle log auto overtime 12 jam, termasuk `session_started`, `task_deliverables_submitted`, dan `session_ended`.
- Dampaknya, di halaman detail overtime staff, tahap `Overtime Session Started` sampai `Overtime Session Ended` menampilkan PIC/Supervisor sebagai actor, padahal yang benar-benar clock in/out adalah staff itu sendiri.
- Menambahkan `$executionActorUser` terpisah dari `$actorUser`: `$actorUser` (supervisor) tetap dipakai untuk `assigned_by` pada `attendance_overtimes` dan actor step `assignment_submitted`, sedangkan `$executionActorUser` (user milik employee yang bersangkutan) dipakai untuk actor step `session_started`, `task_deliverables_submitted`, dan `session_ended`.
- Kolom `PIC / Supervisor` pada card overview overtime (Admin/PIC/Director) tidak berubah karena tetap membaca `attendance_overtimes.assigned_by`.

## File yang Berubah

- `app/Services/Attendance/TwelveHourAutoOvertimeService.php`

## Data Existing yang Perlu Dikoreksi

Perbaikan ini hanya berlaku untuk auto overtime baru (dibuat saat clock-out setelah kode di-deploy). Record `overtime_lifecycle_logs` yang sudah terlanjur dibuat sebelum fix ini (actor_id-nya masih PIC/Supervisor untuk step execution) perlu dikoreksi manual lewat `php artisan tinker` di server:

```
App\Models\OvertimeLifecycleLog::query()
    ->whereIn('event_key', ['session_started', 'task_deliverables_submitted', 'session_ended'])
    ->whereJsonContains('metadata->source', 'attendance_12_hour_auto_overtime')
    ->with('overtime.employee.user')
    ->get()
    ->each(function ($log) {
        $userId = $log->overtime?->employee?->user?->id;
        if ($userId && $log->actor_id !== $userId) {
            $log->update(['actor_id' => $userId]);
        }
    });
```

## Test dan Verifikasi

- `php artisan test --compact tests/Feature/TwelveHourAutoOvertimeCreationTest.php tests/Unit/TwelveHourAutoOvertimeServiceTest.php`
- `vendor/bin/pint --dirty --format agent`

Hasil terakhir: `4 passed (13 assertions)`.
