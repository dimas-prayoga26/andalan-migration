# Ganti Upload Lampiran Izin ke Dropzone (imperative)

- `resources/views/absensi/izin.blade.php`:
  - Tambah library Dropzone CSS/JS dari CDN.
  - Ubah UI lampiran dari input file biasa menjadi `<div id="izinAttachmentDropzone"></div>`.
  - Inisialisasi Dropzone dengan metode imperative jQuery:
    - `$('div#izinAttachmentDropzone').dropzone({...})`
  - Set upload single file (`maxFiles: 1`), tipe file jpg/jpeg/png/pdf, max 5MB.
  - Sinkronkan submit AJAX: file diambil dari `dropzone.getAcceptedFiles()[0]` lalu dikirim sebagai `attachment_file`.
  - Reset file Dropzone saat submit sukses dan saat modal ditutup.
