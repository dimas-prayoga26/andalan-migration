# PWA Temporarily Disabled

Folder ini berisi file dan aset PWA yang sengaja dipindahkan dari aplikasi aktif.
Tujuannya supaya fitur PWA, install prompt, service worker, dan web push tidak jalan dulu, tapi bisa dipasang ulang nanti tanpa tebak-tebakan.

## Status Saat Ini

- Install prompt PWA sudah dilepas dari halaman login.
- Manifest PWA sudah dilepas dari login dan main layout.
- Route manifest, service worker debug, dan device subscription sudah dilepas.
- Web push meeting dinonaktifkan dari `HrMeetingNotificationService`.
- App notification database masih tetap dibuat seperti biasa.
- Icon khusus PWA sudah dipindahkan ke folder ini.

## File Yang Dipindahkan

| File asal | Lokasi arsip |
| --- | --- |
| `app/Console/Commands/TestWebPushNotification.php` | `disabled_pwa/app/Console/Commands/TestWebPushNotification.php` |
| `app/Http/Controllers/DeviceSubscriptionController.php` | `disabled_pwa/app/Http/Controllers/DeviceSubscriptionController.php` |
| `app/Http/Controllers/PwaManifestController.php` | `disabled_pwa/app/Http/Controllers/PwaManifestController.php` |
| `app/Models/DeviceSubscription.php` | `disabled_pwa/app/Models/DeviceSubscription.php` |
| `app/Services/WebPushNotificationService.php` | `disabled_pwa/app/Services/WebPushNotificationService.php` |
| `database/migrations/2026_09_28_152200_create_device_subscriptions_table.php` | `disabled_pwa/database/migrations/2026_09_28_152200_create_device_subscriptions_table.php` |
| `public/sw.js` | `disabled_pwa/public/sw.js` |
| `public/assets/js/install-app-prompt.js` | `disabled_pwa/public/assets/js/install-app-prompt.js` |
| `public/images/pwa/*` | `disabled_pwa/public/images/pwa/*` |
| `resources/views/layouts/install-app-prompt.blade.php` | `disabled_pwa/resources/views/layouts/install-app-prompt.blade.php` |

## Cara Mengaktifkan Lagi

1. Pindahkan semua file dari folder ini ke lokasi asalnya sesuai tabel di atas.

2. Tambahkan lagi route PWA di `routes/web.php`.

```php
use App\Http\Controllers\DeviceSubscriptionController;
use App\Http\Controllers\PwaManifestController;

Route::get('/manifest.webmanifest', PwaManifestController::class)->name('manifest');
Route::post('/device-subscriptions', [DeviceSubscriptionController::class, 'store'])->name('device-subscriptions.store');
Route::delete('/device-subscriptions', [DeviceSubscriptionController::class, 'destroy'])->name('device-subscriptions.destroy');
```

Route debug ini opsional, aktifkan hanya kalau perlu test manual.

```php
Route::get('/sw-push-debug', function () {
    return view('sw-push-debug');
});
```

3. Tambahkan lagi manifest dan meta PWA di `resources/views/auth/login.blade.php` dan `resources/views/layouts/mainhead.blade.php`.

```blade
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
```

4. Tampilkan lagi install prompt di halaman login saja.

```blade
@include('layouts.install-app-prompt')
```

Letakkan sebelum `</body>` di `resources/views/auth/login.blade.php`.

5. Tambahkan lagi config web push di `config/services.php`.

```php
'webpush' => [
    'vapid_public_key' => env('VAPID_PUBLIC_KEY'),
    'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
],
```

6. Pastikan `.env` punya key berikut.

```env
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```

7. Kalau table belum ada, jalankan migration.

```bash
php artisan migrate
```

8. Kalau ingin icon PWA per brand, tambahkan lagi key berikut di `config/branding.php`.

```php
'pwa_icon_192' => 'images/pwa/tms-192.png',
'pwa_icon_512' => 'images/pwa/tms-512.png',
```

Lalu kembalikan key `pwa_icon_192_url` dan `pwa_icon_512_url` di `app/Support/Branding/HostBrandingResolver.php`.

9. Aktifkan lagi pengiriman web push di `app/Services/HrMeetingNotificationService.php`.

Minimal yang perlu dikembalikan:

- Inject `App\Services\WebPushNotificationService`.
- Query subscription dari `App\Models\DeviceSubscription` berdasarkan user peserta meeting.
- Panggil service web push setelah `AppNotification` dibuat.

10. Bersihkan cache Laravel.

```bash
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

11. Di device user, reset permission/site data Chrome untuk domain aplikasi lalu login ulang supaya service worker dan subscription dibuat ulang.

## Dependency

Fitur ini memakai package Composer:

```bash
composer require minishlink/web-push:^11.0
```

Kalau suatu saat package ini dihapus dari `composer.json`, install lagi sebelum mengaktifkan PWA.
