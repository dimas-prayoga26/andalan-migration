# 2026-05-11 08:23 - Hapus Menu Business Trip dan Tutup Modal Setelah Masuk

## Perubahan
- Menghapus tab `Absen Business Trip` pada modal aksi absensi di halaman index.
- Menghapus konten pane `Business Trip` yang terkait tab tersebut.
- Menambahkan alur setelah submit `Masuk` berhasil:
  - modal absensi ditutup otomatis
  - DataTable absensi direfresh otomatis (`ajax.reload(null, false)`).

## File Terdampak
- `resources/views/absensi/index.blade.php`
