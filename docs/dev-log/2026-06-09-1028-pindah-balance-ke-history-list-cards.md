# Pindah Balance ke History List Cards

## Ringkasan

- Memindahkan markup balance analytic dari `history-cards.blade.php` ke `history-list-cards.blade.php`.
- Mengembalikan markup Leave List agar tetap berada inline di `index.blade.php`.
- Memindahkan fragment AJAX filter Leave List ke `request-cards.blade.php` agar `history-list-cards` tidak lagi berisi card request.
- Mengarahkan controller filter cards ke partial AJAX baru.

## Verifikasi

- Test Leave Request diperbarui untuk memastikan `history-list-cards` berisi balance analytic dan Leave List tetap tersedia di index.
