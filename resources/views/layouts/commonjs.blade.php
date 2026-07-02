<!-- Start - Script -->
@php
    $assetVersion = static function (string $path): int {
        $assetPath = public_path('assets/' . ltrim($path, '/'));

        return file_exists($assetPath) ? filemtime($assetPath) : time();
    };
@endphp
<script src="vendor/jquery/dist/jquery.min.js?v={{ $assetVersion('vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="vendor/bootstrap/dist/js/bootstrap.bundle.min.js?v={{ $assetVersion('vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js?v={{ $assetVersion('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="vendor/metismenu/dist/metisMenu.min.js?v={{ $assetVersion('vendor/metismenu/dist/metisMenu.min.js') }}"></script>

<!-- Script for Owl Carousel -->
<script src="vendor/owl-carousel/owl.carousel.js?v={{ $assetVersion('vendor/owl-carousel/owl.carousel.js') }}"></script>

<!-- Script For Daterangepicker -->
<script src="vendor/daterangepicker/moment.min.js?v={{ $assetVersion('vendor/daterangepicker/moment.min.js') }}"></script>
<script src="vendor/daterangepicker/daterangepicker.js?v={{ $assetVersion('vendor/daterangepicker/daterangepicker.js') }}"></script>
<script src="js/plugins-init/daterangepicker-init.js?v={{ $assetVersion('js/plugins-init/daterangepicker-init.js') }}"></script>
<script src="vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?v={{ $assetVersion('vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>

<!-- Script for Chart piety -->
<script src="vendor/peity/jquery.peity.min.js?v={{ $assetVersion('vendor/peity/jquery.peity.min.js') }}"></script>

<!-- Script For Multiple Languages -->
<script src="vendor/i18n/i18n.js?v={{ $assetVersion('vendor/i18n/i18n.js') }}"></script>
<script src="js/translator.js?v={{ $assetVersion('js/translator.js') }}"></script>

<!-- Script For Custom JS -->
<script src="js/deznav-init.js?v={{ $assetVersion('js/deznav-init.js') }}"></script>
<script src="js/custom.js?v={{ $assetVersion('js/custom.js') }}"></script>
