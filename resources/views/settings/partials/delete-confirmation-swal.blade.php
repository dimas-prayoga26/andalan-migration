@once
    @push('scripts')
        <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
        <script>
            (function () {
                document.addEventListener('submit', function (event) {
                    var form = event.target;

                    if (!form.matches('[data-delete-confirmation-form], [data-settings-delete-form]')) {
                        return;
                    }

                    if (form.dataset.deleteConfirmed === 'true') {
                        return;
                    }

                    event.preventDefault();

                    var title = form.dataset.deleteTitle || 'Delete Data';
                    var message = form.dataset.deleteMessage || 'This data will be permanently deleted.';
                    var confirmButtonText = form.dataset.deleteConfirmButton || 'Delete';
                    var cancelButtonText = form.dataset.deleteCancelButton || 'Cancel';

                    if (typeof Swal === 'undefined' || !Swal || typeof Swal.fire !== 'function') {
                        if (window.confirm(message)) {
                            form.dataset.deleteConfirmed = 'true';
                            form.submit();
                        }

                        return;
                    }

                    Swal.fire({
                        title: title,
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: confirmButtonText,
                        cancelButtonText: cancelButtonText,
                        confirmButtonColor: '#dc3545',
                        reverseButtons: true,
                        focusCancel: true
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        form.dataset.deleteConfirmed = 'true';
                        form.submit();
                    });
                });
            })();
        </script>
    @endpush
@endonce
