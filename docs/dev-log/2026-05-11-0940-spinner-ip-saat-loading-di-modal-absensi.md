# 2026-05-11 09:40 - Spinner IP Saat Loading di Modal Absensi

## Perubahan
- Jika IP belum tersedia, tampilan IP di modal onsite sekarang menampilkan spinner + teks `Memuat...` (bukan `-`).
- Menambahkan state fallback `Tidak tersedia` ketika request IP gagal.
- Badge IP dibuat netral saat loading (`Memuat`) dan berubah sesuai hasil validasi (`Valid` / `Tidak Valid`).

## File Terdampak
- `resources/views/absensi/index.blade.php`
