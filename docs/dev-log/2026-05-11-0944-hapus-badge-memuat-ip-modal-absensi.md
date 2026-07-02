# 2026-05-11 09:44 - Hapus Badge Memuat IP di Modal Absensi

## Perubahan
- Saat IP masih loading, badge `Memuat` dihapus sehingga yang tampil hanya spinner + teks `Memuat...`.
- Badge tetap muncul untuk status hasil:
  - `Valid` / `Tidak Valid` saat IP berhasil didapat.
  - `Tidak tersedia` saat request IP gagal.

## File Terdampak
- `resources/views/absensi/index.blade.php`
