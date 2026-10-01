@php
    $alertType = strtolower(trim((string) ($type ?? 'info')));
    $alertTitle = trim((string) ($title ?? match ($alertType) {
        'success' => 'Berhasil',
        'error', 'danger' => 'Gagal',
        'warning' => 'Perhatian',
        default => 'Informasi',
    }));
    $alertMessage = trim((string) ($message ?? ''));
    $alertIcon = match ($alertType) {
        'danger' => 'error',
        'success', 'error', 'warning', 'info', 'question' => $alertType,
        default => 'info',
    };
@endphp

@if ($alertMessage !== '')
    <script>
        window.__swalFlashAlerts = window.__swalFlashAlerts || [];
        window.__swalFlashAlerts.push(@json([
            'icon' => $alertIcon,
            'title' => $alertTitle,
            'text' => $alertMessage
        ]));
    </script>

    @once
        <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
        <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var alerts = window.__swalFlashAlerts || [];

                function showAlert(index) {
                    if (index >= alerts.length) {
                        return;
                    }

                    var alert = alerts[index] || {};

                    if (typeof Swal === 'undefined' || !Swal || typeof Swal.fire !== 'function') {
                        window.alert((alert.title ? alert.title + '\n' : '') + (alert.text || ''));
                        showAlert(index + 1);
                        return;
                    }

                    Swal.fire({
                        icon: alert.icon || 'info',
                        title: alert.title || 'Informasi',
                        text: alert.text || '',
                        confirmButtonColor: '#2846c7'
                    }).then(function () {
                        showAlert(index + 1);
                    });
                }

                showAlert(0);
            });
        </script>
    @endonce
@endif
