<!-- Start - Script -->
@php
    $assetVersion = static function (string $path): int {
        $assetPath = public_path('assets/' . ltrim($path, '/'));

        return file_exists($assetPath) ? filemtime($assetPath) : time();
    };
@endphp
<script src="vendor/jquery/dist/jquery.min.js?v={{ $assetVersion('vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="vendor/bootstrap/dist/js/bootstrap.bundle.min.js?v={{ $assetVersion('vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script>
    /**
     * Three-dot action menu for JS-rendered table rows; markup mirrors the x-action-menu Blade component.
     * Each item is a ready-made '<li>…</li>' string.
     */
    window.actionMenu = function (items) {
        return '<div class="dropdown action-menu">'
            + '<button type="button" class="btn btn-light action-menu-toggle" data-bs-toggle="dropdown" data-bs-popper-config=\'{"strategy":"fixed"}\' aria-expanded="false" aria-label="Action" title="Action">'
            + '<i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>'
            + '</button>'
            + '<ul class="dropdown-menu dropdown-menu-end">' + items.join('') + '</ul>'
            + '</div>';
    };
</script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js?v={{ $assetVersion('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="vendor/metismenu/dist/metisMenu.min.js?v={{ $assetVersion('vendor/metismenu/dist/metisMenu.min.js') }}"></script>

<!-- Script For Daterangepicker -->
<script src="vendor/daterangepicker/moment.min.js?v={{ $assetVersion('vendor/daterangepicker/moment.min.js') }}"></script>
<script>
    if (typeof moment !== 'undefined') {
        moment.updateLocale('en', {
            week: { dow: 1 },
        });
    }
</script>
<script src="vendor/daterangepicker/daterangepicker.js?v={{ $assetVersion('vendor/daterangepicker/daterangepicker.js') }}"></script>
<script src="vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v={{ $assetVersion('vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>

<!-- Script for Chart piety -->
<script src="vendor/peity/jquery.peity.min.js?v={{ $assetVersion('vendor/peity/jquery.peity.min.js') }}"></script>

<!-- Script For Multiple Languages -->
<script src="vendor/i18n/i18n.js?v={{ $assetVersion('vendor/i18n/i18n.js') }}"></script>
<script src="js/translator.js?v={{ $assetVersion('js/translator.js') }}"></script>

<!-- Script For Custom JS -->
<script src="js/deznav-init.js?v={{ $assetVersion('js/deznav-init.js') }}"></script>
<script src="js/custom.js?v={{ $assetVersion('js/custom.js') }}"></script>
