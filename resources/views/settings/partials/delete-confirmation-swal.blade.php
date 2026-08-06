@once
    @push('scripts')
        <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
        <script>
            (function () {
                document.querySelectorAll('[data-settings-delete-form]').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.dataset.deleteConfirmed === 'true') {
                            return;
                        }

                        event.preventDefault();

                        var title = form.dataset.deleteTitle || 'Delete Data';
                        var message = form.dataset.deleteMessage || 'This data will be permanently deleted.';

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
                            confirmButtonText: 'Delete',
                            cancelButtonText: 'Cancel',
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
                });
            })();
        </script>
    @endpush
@endonce
