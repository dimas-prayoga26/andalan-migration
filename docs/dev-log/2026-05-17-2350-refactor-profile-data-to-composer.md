# Refactor Profile Data ke AbsensiProfileComposer

## Waktu
2026-05-17

## Perubahan
- Menghapus seluruh pengisian data profile header (`profile*`, `management*`) dari:
  - `AttendanceController@index`
  - `LeaveRequestController@index`
- Menghapus pengiriman variable profile dari return `view(...)` di kedua controller di atas.
- `AbsensiProfileComposer` tetap menjadi single source untuk data `layouts_absensi/profileHeader` dengan pendekatan relasi Eloquent (`loadMissing`), tanpa `Schema::hasTable` / `Schema::hasColumn`.
- Menghapus helper yang sudah tidak dipakai setelah refactor di controller:
  - `AttendanceController::calculateWorkingDaysInMonth`
  - `AttendanceController::fetchIndonesiaHolidayDates`
  - `LeaveRequestController::calculateWorkingDaysInMonth`
- Menyederhanakan query `leave_types` di `LeaveRequestController@index` (langsung query aktif, tanpa guard `Schema::hasTable`).

## Dampak
- Header profile di semua halaman absensi yang memanggil partial `absensi.layouts_absensi.profileHeader` sekarang konsisten, walau controller halaman berbeda.
- Duplikasi logic profile antar controller hilang.
