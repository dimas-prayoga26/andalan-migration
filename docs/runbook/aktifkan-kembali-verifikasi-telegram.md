# Runbook: Aktifkan Kembali Verifikasi Telegram

Dokumen ini dipakai saat fitur verifikasi Telegram ingin dihidupkan lagi pada alur absensi.

## Lokasi Kode
- `app/Http/Controllers/AttendanceController.php`
- Method: `verifyTelegramUsername(Request $request): JsonResponse`

## Kondisi Saat Ini (Sementara Dinonaktifkan)
- Endpoint verifikasi Telegram mengembalikan sukses langsung:
  - `success: true`
  - pesan: `Verifikasi Telegram sementara dinonaktifkan. Lanjut verifikasi geofencing.`
- Blok verifikasi Telegram asli berada di bawah komentar block `/* ... */`.

## Langkah Mengaktifkan Kembali
1. Buka `app/Http/Controllers/AttendanceController.php`.
2. Cari blok berikut di method `verifyTelegramUsername`:
   - komentar:
     - `Temporary disabled:`
     - `Skip Telegram API verification + persistence flow for now.`
   - lalu `return response()->json([...]);`
3. Hapus `return` sementara tersebut.
4. Hapus komentar block `/* ... */` yang membungkus logika verifikasi Telegram.
5. (Opsional, jika ingin strict binding) aktifkan kembali validasi username:
   - blok `$applicationUsername = ...`
   - blok perbandingan:
     - `if (mb_strtolower($telegramUsername) !== mb_strtolower($applicationUsername)) { continue; }`

## Setelah Diaktifkan
Alur verifikasi kembali menjadi:
1. Cek user login dan employee tersedia.
2. Cek `TELEGRAM_BOT_TOKEN`.
3. Call Telegram API `getUpdates`.
4. Cocokkan data Telegram user.
5. `updateOrCreate` ke tabel `telegram_users`.
6. Set `users.is_telegram_verified = true`.

## Verifikasi Manual
1. Buka halaman absensi.
2. Klik `Mulai Verifikasi` pada modal `Clock In` / `Clock Out`.
3. Pastikan:
   - tidak muncul pesan "Verifikasi Telegram sementara dinonaktifkan"
   - verifikasi berhasil saat data Telegram cocok
   - gagal dengan pesan yang sesuai saat data tidak cocok.

## Catatan
- Jika fitur masih ingin nonaktif, biarkan return sementara tetap ada.
- Geofencing tetap bisa jalan walau verifikasi Telegram dimatikan.
