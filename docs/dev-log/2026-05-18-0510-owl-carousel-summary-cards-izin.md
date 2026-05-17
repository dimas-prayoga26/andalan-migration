# 2026-05-18 05:10 - Owl Carousel untuk Summary Card Izin

## Ringkasan
- Mengubah daftar summary card pada halaman `absensi/izin` menjadi `Owl Carousel`.
- Fokus utama: di mobile tampil 1 card dominan dan bisa swipe/scroll horizontal.
- Desktop tetap menampilkan banyak card per baris melalui pengaturan `responsive` Owl.

## File yang Diubah
- `resources/views/absensi/izin.blade.php`

## Detail Perubahan
- Mengganti wrapper summary dari struktur `row` biasa ke:
  - `.izin-summary-carousel.js-izin-summary-carousel.owl-carousel`
- Mengubah item card summary agar kompatibel dengan struktur item Owl.
- Menambahkan inisialisasi JS `initIzinSummaryCarousel()` pada `document.ready`.
- Menambahkan style penyesuaian agar tinggi item rapi di dalam `owl-stage`.

## Konfigurasi Carousel
- `loop: false`
- `margin: 10`
- `nav: false`
- `dots: false`
- `responsive`:
  - `<576`: `1.05` item
  - `>=576`: `2` item
  - `>=992`: `4` item
