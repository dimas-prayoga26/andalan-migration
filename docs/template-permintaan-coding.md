# Template Permintaan Coding (Standar Project)

Dokumen ini merangkum preferensi implementasi yang diminta.

## 1) JavaScript Style
- Hindari pola IIFE: `(function () { ... })();`
- Gunakan function biasa agar mudah dibaca:

```js
function initSomething() {
  // ...
}

initSomething();
```

## 2) Submit Form Wajib AJAX (jQuery)
Gunakan pola berikut:

```js
$("#simpanData").on("click", function (e) {
    e.preventDefault();

    let formData = new FormData($("#tambahData")[0]);

    $.ajax({
        url: "<route>",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        beforeSend: function () {
            $("#simpanData").prop("disabled", true).html("Menyimpan...");
        },
        success: function (response) {
            if (response.success === true || response.status === true) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                // reset form / close modal / reload table
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal',
                    text: response.message
                });
            }
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'Gagal memproses permintaan.';

            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: message
            });
        },
        complete: function () {
            $("#simpanData").prop("disabled", false).html("Simpan");
        }
    });
});
```

## 3) SweetAlert Message Sumbernya Dari Controller
- `Swal.fire(... text: response.message ...)` wajib ambil dari backend.
- Error validasi juga dari backend (`xhr.responseJSON.message` / `xhr.responseJSON.errors`).
- Hindari hardcoded message frontend untuk validasi utama.

## 4) Response Backend Minimalis
Response JSON untuk create/store cukup:
- `success`
- `message`

Tidak perlu kirim field tambahan seperti `attendance_permission_id` jika tidak dipakai UI.

## 5) Route Convention
- Controller gunakan bahasa Inggris.
- Tambahkan komentar singkat di `routes/web.php` untuk menjelaskan fungsi route.
- Gunakan template route seperti ini untuk modul resource + endpoint pendukung:

```php
// About
Route::get('/about/datatable', [AboutController::class, 'datatable'])->name('about.datatable');
Route::post('/about/upload-image', [AboutController::class, 'uploadImage'])->name('about.upload-image');
Route::post('/about/delete-uploaded-image', [AboutController::class, 'deleteUploadedImage'])->name('about.delete-uploaded-image');
Route::get('/about/list-superiority-options', [AboutController::class, 'getListSuperiorityOptions'])->name('about.list-superiority-options');
Route::resource('about', AboutController::class);
```

## 6) Lampiran File
- Gunakan FilePond untuk tampilan upload lampiran jika perlu UI drag/drop.
- File tetap dikirim melalui AJAX submit form utama, bukan upload endpoint terpisah.
- Field lampiran bersifat opsional, label: `Lampiran (Opsional)`.
- Nama field untuk multiple upload: `attachment_files[]`.

## 7) Catatan Implementasi Menu Izin
- Data utama: `attendance_permissions`.
- Data lampiran: `attendance_permission_attachments`.
- Insert lampiran hanya jika file ada.

## 8) Standar Pembuatan DataTable
Referensi implementasi aktif:
- Backend: `app/Http/Controllers/AttendanceController.php` (method `datatable`)
- Frontend: `resources/views/absensi/index.blade.php` (inisialisasi `$('#myTable').DataTable({...})`)

### Backend (Controller)
Pattern endpoint DataTable:
1. Ambil data user + relasi yang dibutuhkan (`userEmployee.company`, `attendances` hari ini).
2. Terapkan filter role/permission di query (superuser / board of directors).
3. Ambil `attendance_logs` berdasarkan `attendance_id`.
4. Mapping response JSON dengan key yang dipakai frontend.
5. Return format:

```php
return response()->json([
    'data' => $tableRows,
]);
```

Contoh field yang dikirim:
- `staff_name`
- `company_name`
- `check_in`
- `check_out`
- `status`

### Frontend (Blade + DataTables)
Pattern inisialisasi:

```js
var attendanceTable = $('#myTable').DataTable({
    ajax: {
        url: attendanceDatatableUrl,
        data: function (requestData) {
            requestData.company_id = attendanceCompanyFilter ? attendanceCompanyFilter.value : 0;
        },
        dataSrc: 'data'
    },
    autoWidth: false,
    scrollX: true,
    scrollCollapse: true,
    columns: [
        { data: null, defaultContent: '' },
        { data: 'staff_name' },
        { data: 'check_in' },
        { data: 'check_out' },
        { data: 'company_name' }
    ],
    columnDefs: [
        { targets: 0, searchable: false, orderable: false },
        // render kolom sesuai kebutuhan
    ],
    initComplete: function () {
        var tableApi = this.api();
        var tableContainer = $(tableApi.table().container());
        var scrollBody = tableContainer.find('.dt-scroll-body');

        scrollBody.css({
            overflowX: 'auto',
            overflowY: 'hidden',
            WebkitOverflowScrolling: 'touch'
        });

        scrollBody.scrollLeft(0);
        tableApi.columns.adjust();
    }
});
```

### Nomor Urut (No) Tetap 1++

```js
attendanceTable.on('order.dt search.dt draw.dt', function () {
    var pageInfo = attendanceTable.page.info();
    attendanceTable.column(0, { page: 'current' }).nodes().each(function (cell, index) {
        cell.innerHTML = pageInfo.start + index + 1;
    });
});
```

### Checklist DataTable Baru
- [ ] Route data JSON tersedia.
- [ ] Controller return `['data' => ...]`.
- [ ] Key JSON sinkron dengan `columns.data`.
- [ ] `scrollX` aktif jika kolom banyak.
- [ ] Nomor urut pakai event `order/search/draw`.
- [ ] Filter frontend tersambung ke backend.
- [ ] Role/permission diproteksi di backend.
- [ ] Render kolom status/jam sesuai kebutuhan bisnis.

---
Dokumen ini jadi acuan implementasi berikutnya agar konsisten dengan preferensi coding yang diminta.
