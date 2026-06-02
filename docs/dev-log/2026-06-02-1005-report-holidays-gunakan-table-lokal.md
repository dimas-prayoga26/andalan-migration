# Report Holidays Gunakan Table Lokal

## Ringkasan

- Mengubah sumber data hari libur pada Attendance Report dari API eksternal menjadi tabel lokal `attendances_holidays`.
- Menghapus pemanggilan `https://libur.deno.dev/api` dari `ReportController`.
- Menghapus cache API hari libur yang tidak lagi diperlukan.

## File Perubahan

- `app/Http/Controllers/ReportController.php`
- `tests/Feature/ReportHolidayDatabaseTest.php`

## Detail Implementasi

- Method `buildHolidayMapByMonth()` sekarang mengambil data melalui model `AttendanceHoliday`.
- Query dibatasi berdasarkan tahun dan bulan terpilih.
- Nilai `type = 1` dipetakan sebagai libur nasional.
- Nilai selain `type = 1` dipetakan sebagai cuti bersama.

## Verifikasi

- Menambahkan test untuk memastikan:
  - data libur nasional dan cuti bersama berasal dari tabel `attendances_holidays`,
  - data di luar bulan terpilih tidak ikut dipakai,
  - tidak ada HTTP request eksternal yang dikirim.
- Menjalankan:
  - `vendor/bin/pint --dirty --format agent`
  - `php -d extension=php_pdo_sqlite.dll -d extension=php_sqlite3.dll vendor/bin/phpunit --colors=never tests/Feature/ReportHolidayDatabaseTest.php`
