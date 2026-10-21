<script src="{{ asset('assets/vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jQuery || !jQuery.fn.smartWizard) {
            return;
        }

        jQuery('#employeeDataWizard').smartWizard({
            autoAdjustHeight: false,
            enableURLhash: false,
            keyboardSettings: { keyNavigation: false },
            transition: { animation: 'none' }
        });
    });
</script>
