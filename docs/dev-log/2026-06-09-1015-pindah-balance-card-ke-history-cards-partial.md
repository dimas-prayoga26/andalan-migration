# Pindah Balance Card ke History Cards Partial

## Ringkasan

- Memindahkan isi balance card analytic ke `partials/history-cards.blade.php`.
- Menghapus partial `partials/balance-cards.blade.php`.
- Menjaga markup awal Leave List tetap inline di `index.blade.php`.
- Memindahkan fragment AJAX Leave List ke `partials/history-list-cards.blade.php` agar filter AJAX tetap mengembalikan card history request.
- Mengarahkan controller `cards()` ke partial AJAX baru.

## Verifikasi

- Test halaman Leave Request diperbarui untuk memastikan `history-cards` berisi balance analytic dan `history-list-cards` dipakai hanya untuk fragment AJAX Leave List.
