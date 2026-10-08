(function () {
    var personalType = 'personal';

    function toggleMailAccountForm(form) {
        var typeSelect = form.querySelector('[data-mail-account-type]');
        var companyField = form.querySelector('[data-mail-account-company-field]');
        var ownerField = form.querySelector('[data-mail-account-owner-field]');

        if (! typeSelect || ! companyField || ! ownerField) {
            return;
        }

        var companySelect = companyField.querySelector('select');
        var ownerSelect = ownerField.querySelector('select');
        var isPersonal = typeSelect.value === personalType;

        companyField.classList.toggle('d-none', isPersonal);
        ownerField.classList.toggle('d-none', ! isPersonal);

        if (companySelect) {
            companySelect.disabled = isPersonal;
            companySelect.required = ! isPersonal;

            if (isPersonal) {
                companySelect.value = '';
            }
        }

        if (ownerSelect) {
            ownerSelect.disabled = ! isPersonal;
            ownerSelect.required = isPersonal;

            if (! isPersonal) {
                ownerSelect.value = '';
            }
        }
    }

    function bootMailAccountForms() {
        document.querySelectorAll('[data-mail-account-form]').forEach(function (form) {
            toggleMailAccountForm(form);

            var typeSelect = form.querySelector('[data-mail-account-type]');

            if (typeSelect) {
                typeSelect.addEventListener('change', function () {
                    toggleMailAccountForm(form);
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', bootMailAccountForms);
    document.addEventListener('shown.bs.modal', function (event) {
        event.target.querySelectorAll('[data-mail-account-form]').forEach(toggleMailAccountForm);
    });
})();
