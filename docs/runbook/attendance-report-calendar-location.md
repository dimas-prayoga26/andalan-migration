# Runbook: Attendance Report, Calendar Modal, dan Lokasi Absensi

Dokumen ini merangkum perubahan akhir pada Attendance Report, calendar attendance, dan penyimpanan lokasi absensi.

## Lokasi Kode
- `app/Http/Controllers/ReportController.php`
- `app/Http/Controllers/AttendanceController.php`
- `app/Services/Attendance/AttendanceMutationService.php`
- `resources/views/attendance/reports/index.blade.php`
- `resources/views/attendance/attendance/index.blade.php`
- `tests/Feature/AttendanceCalendarModalRoutingTest.php`
- `tests/Feature/AttendanceLogLocationByCoordinatesTest.php`
- `tests/Feature/AttendanceReportExcelExportTest.php`

## Attendance Report
- Kolom `Variance` diganti menjadi `Note`.
- Kolom `Notes` lama dihapus.
- Kolom `Attachment` digunakan untuk link attachment leave/sick jika tersedia.
- Nilai `Note` mengikuti status attendance:
  - `On Time`
  - `Late {n} Minutes`
  - `Izin Masuk Terlambat {durasi}`
  - `Izin Pulang Lebih Awal {durasi}`
  - label leave/holiday sesuai data terkait
- Export report memakai format `.xlsx`.
- Export PDF lama dihapus.
- Title export dinamis memakai format:
  - `{company} - {staff}`
  - fallback tetap tersedia jika company/staff tidak lengkap.

## Calendar Attendance
- Event attendance pada calendar membuka modal sesuai kondisi:
  - `onTime` untuk staff on time.
  - `late` untuk staff terlambat.
  - `deviation` untuk attendance exception `late_arrival` atau `early_departure`.
- Attendance exception tidak menunggu clock out untuk masuk modal `deviation`.
- Modal tidak lagi memakai data statis.
- Data modal diisi dari `extendedProps` event calendar:
  - `modalTitle`
  - `attendanceStatusLabel`
  - `locationName`
  - `locationAddress`
  - `clockInSchedule`
  - `clockOutSchedule`
  - `requestTypeLabel`
  - `reason`
  - `timeVarianceLabel`
  - `exceptionStatusLabel`
  - `exceptionStatusDateLabel`
- Modal `deviation` hanya menampilkan:
  - `Request Type`
  - `Reason`
  - `Time Variance`
  - `Status`
- Header dan copy pembuka modal `deviation` dikirim dari `extendedProps` event calendar, bukan ditulis statis di Blade.
- Label title dan `Request Type` modal `deviation` mengikuti `attendance_exceptions.type`:
  - `late_arrival` menjadi `Permitted Late Arrival`
  - `early_departure` menjadi `Early Departure`

## Penyimpanan Lokasi Absensi
Alur lokasi pada submit clock in / clock out:

1. Browser mengambil koordinat dari Geolocation API.
2. Frontend mengirim `latitude` dan `longitude` ke backend.
3. `AttendanceMutationService::buildTrackingContext()` memakai koordinat request jika valid.
4. Jika koordinat request tidak tersedia, service masih memiliki fallback IP geolocation.
5. Service menghitung jarak ke koordinat kantor dari `companies.latitude` dan `companies.longitude`.
6. Service memanggil `reverseGeocodeCoordinates()`.
7. Google Geocoding API mengembalikan `plus_code.compound_code`.
8. `attendance_logs.location` menyimpan plus code tersebut, contoh:
   - `7CC3+4QW Minomartani, Sleman Regency, Special Region of Yogyakarta`
9. Jika plus code tidak tersedia, `location` fallback ke URL Google Maps dari lat/long:
   - `https://www.google.com/maps?q=-7.7296271,110.4044443`

## Pecah Address Component
`plus_code.compound_code` dipecah untuk mengisi kolom:
- `address_village`
- `address_district`
- `address_regency`
- `address_city`
- `address_province`
- `address_postal_code`

Jika ada bagian yang tidak tersedia dari plus code, sistem memakai fallback dari `address_components` Google.

## Tampilan Lokasi di Calendar Modal
- Modal calendar mengambil data log clock in dari `attendance_logs`.
- Jika `location` berisi plus code, teks lokasi menampilkan plus code tersebut.
- Jika log lama berisi URL Google Maps, modal fallback menampilkan koordinat.
- Modal tidak menampilkan button/link maps terpisah.

## Label Event Calendar
- Label/card event attendance memakai format jam:
  - `In : 08:10 - Out : 17:00`
- Status event tetap disimpan di `extendedProps` untuk menentukan modal yang dibuka dan isi detail modal.
- Calendar juga menampilkan label dari data staff:
  - `Special Leave` dengan warna `#d63384`
  - `Sick Leave` dengan warna `#0d6efd`
  - `Unpaid Leave` dengan warna `#6c757d`
  - `Annual Leave` dengan warna `#6f42c1`
  - `Business Trip` dengan warna `#0dcaf0`
- Leave label berasal dari `leave_requests` status `pending` dan `approved`.
- Business Trip label hanya berasal dari `business_trips` dengan `approval_status` `approved`.
- Klik label Leave/Business Trip membuka modal sesuai tipe:
  - `Annual Leave` ke `annualLeave`
  - `Special Leave` ke `specialLeave`
  - `Unpaid Leave` ke `unpaidLeave`
  - `Sick Leave` ke `sick`
  - `Business Trip` ke `trip`
- Isi modal diambil dari `extendedProps` event calendar, termasuk reason/purpose, duration, destination, status, dan attachment medical notes bila ada.

## Catatan Teknis
- `formatted_address` Google tidak lagi menjadi sumber utama `attendance_logs.location` karena bisa menunjuk label alamat yang meleset dari titik koordinat.
- Lat/long tetap menjadi sumber kebenaran titik absensi.
- `address_components` Google tetap dipakai sebagai fallback untuk melengkapi kolom address.

## Verifikasi Manual
1. Login sebagai staff.
2. Buka halaman Attendance.
3. Clock in dari perangkat yang mengirim GPS.
4. Cek record terbaru di `attendance_logs`:
   - `latitude` dan `longitude` terisi.
   - `location` berisi plus code jika Google mengembalikannya.
   - address columns terisi dari plus code atau fallback `address_components`.
5. Buka calendar attendance.
6. Klik event on-time/late/deviation.
7. Pastikan modal menampilkan data sesuai event tanpa button/link maps terpisah.

## Verifikasi Test
Jalankan test terkait:

```bash
php artisan test --compact tests/Feature/AttendanceLogLocationByCoordinatesTest.php tests/Feature/AttendanceCalendarModalRoutingTest.php tests/Feature/AttendanceReportExcelExportTest.php
```
