# Dev Log - Pindah Init `tableLogs` dari `profile.js` ke `index.blade.php`

Tanggal: 2026-05-12  
File terkait:
- `public/assets/js/dashboard/profile.js`
- `resources/views/absensi/index.blade.php`

## Ringkasan
- Hapus inisialisasi DataTable `#tableLogs` dari `profile.js`.
- Hapus pemanggilan `tableLogs()` dari `dzProfile.load()` agar tidak double-init.
- Pastikan inisialisasi `tableLogs()` dijalankan dari script lokal di `index.blade.php`.

## Tujuan
- Menghindari conflict DataTable karena elemen `#tableLogs` diinisialisasi dari dua tempat berbeda.
