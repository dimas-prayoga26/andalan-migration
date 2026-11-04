<script src="{{ asset('assets/vendor/select2/js/select2.full.min.js') }}"></script>
<script>
    (function () {
        function initializeMailAccountPicker() {
            if (!window.jQuery || !jQuery.fn.select2) {
                return;
            }

            $('[data-mail-account-picker]').each(function () {
                var picker = $(this);

                if (picker.data('mailAccountPickerReady')) {
                    return;
                }

                picker.data('mailAccountPickerReady', true);

                var trigger = picker.find('[data-mail-account-trigger]');
                var menu = picker.find('[data-mail-account-menu]');
                var selectElement = picker.find('.mail-account-select2');

                if ($.fn.selectpicker && selectElement.data('selectpicker')) {
                    selectElement.selectpicker('destroy');
                }

                selectElement.siblings('.bootstrap-select').remove();

                selectElement.select2({
                    allowClear: true,
                    dropdownCssClass: 'mail-account-select2-dropdown',
                    dropdownParent: menu,
                    minimumResultsForSearch: 0,
                    placeholder: selectElement.data('placeholder'),
                    width: '100%'
                });

                trigger.on('click.mailAccountPicker', function () {
                    menu.toggleClass('d-none');
                    picker.toggleClass('is-open', !menu.hasClass('d-none'));

                    if (!menu.hasClass('d-none')) {
                        window.setTimeout(function () {
                            selectElement.select2('open');
                        }, 0);
                    }
                });

                selectElement.on('change.mailAccountPicker', function () {
                    if (this.value) {
                        this.form.submit();
                    }
                });
            });
        }

        function copyText(value) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(value);
            }

            return new Promise(function (resolve, reject) {
                var temporaryInput = document.createElement('textarea');

                temporaryInput.value = value;
                temporaryInput.setAttribute('readonly', '');
                temporaryInput.style.position = 'fixed';
                temporaryInput.style.opacity = '0';
                document.body.appendChild(temporaryInput);
                temporaryInput.select();

                var isCopied = document.execCommand('copy');

                temporaryInput.remove();
                isCopied ? resolve() : reject(new Error('Copy command failed'));
            });
        }

        function showCopyResult(copyButton, isCopied) {
            var icon = copyButton.querySelector('i');

            window.clearTimeout(Number(copyButton.dataset.resetTimer));
            copyButton.classList.toggle('is-copied', isCopied);
            copyButton.classList.toggle('is-failed', !isCopied);
            copyButton.title = isCopied ? 'Email tersalin' : 'Gagal menyalin email';
            icon.className = isCopied ? 'fa-solid fa-check' : 'fa-solid fa-xmark';

            copyButton.dataset.resetTimer = window.setTimeout(function () {
                copyButton.classList.remove('is-copied', 'is-failed');
                copyButton.title = 'Salin email';
                icon.className = 'fa-regular fa-copy';
            }, 1500);
        }

        document.addEventListener('click', function (event) {
            var copyButton = event.target.closest('[data-mail-account-copy]');

            if (!copyButton || !copyButton.dataset.copyValue) {
                return;
            }

            copyText(copyButton.dataset.copyValue)
                .then(function () {
                    showCopyResult(copyButton, true);
                })
                .catch(function () {
                    showCopyResult(copyButton, false);
                });
        });

        $(initializeMailAccountPicker);
        $(window).on('load.mailAccountPicker', initializeMailAccountPicker);

        document.addEventListener('click', function (event) {
            document.querySelectorAll('[data-mail-account-picker]').forEach(function (picker) {
                if (!picker.contains(event.target)) {
                    picker.querySelector('[data-mail-account-menu]')?.classList.add('d-none');
                    picker.classList.remove('is-open');
                }
            });
        });
    })();
</script>
