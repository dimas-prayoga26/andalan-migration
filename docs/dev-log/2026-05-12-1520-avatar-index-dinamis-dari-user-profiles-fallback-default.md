# Dev Log - Avatar Index Dinamis dari user_profiles + Fallback Default

Tanggal: 2026-05-12  
File:
- `app/Http/Controllers/AttendanceController.php`
- `resources/views/absensi/index.blade.php`

## Ringkasan
- Menambahkan pengambilan nilai `profile_picture` dari tabel `user_profiles` berdasarkan `user_id` login.
- Nilai path avatar dikirim ke view sebagai `profilePicturePath`.
- Pada Blade, avatar sekarang dinamis:
  - Jika `profile_picture` ada -> dipakai sebagai sumber gambar.
  - Jika kosong/tidak ada -> fallback ke default `assets/images/avatar/large/avatar5.webp`.
  - Mendukung URL penuh (`http/https`) maupun path relatif.
