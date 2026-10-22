<script src="{{ asset('assets/vendor/jquery-smartwizard/dist/js/jquery.smartWizard.min.js') }}"></script>
<script>
    (function () {
        function onReady(callback) {
            if (window.jQuery) {
                jQuery(callback);

                return;
            }

            document.addEventListener('DOMContentLoaded', callback);
        }

        onReady(function () {
            var wizardElement = document.getElementById('employeeDataWizard');

            if (!wizardElement) {
                return;
            }

            var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
            var errorElement = wizardElement.querySelector('.js-employee-data-error');
            var statusElement = wizardElement.querySelector('.js-employee-data-save-status');
            var stepPanes = Array.prototype.slice.call(wizardElement.querySelectorAll('.tab-content > .tab-pane'));
            var stepForms = Array.prototype.slice.call(wizardElement.querySelectorAll('form.js-employee-data-step-form'));
            var savedSnapshots = new Map();
            var regionLevels = ['province', 'regency', 'subdistrict', 'village'];
            var regionRequests = {};
            var regionUrls = {};
            var currentStepIndex = 0;

            try {
                regionUrls = JSON.parse(wizardElement.dataset.regionUrls || '{}');
            } catch (error) {
                regionUrls = {};
            }

            function hasSelectpicker(select) {
                return !!(window.jQuery && jQuery.fn.selectpicker && jQuery(select).data('selectpicker'));
            }

            function refreshSelect(select) {
                if (hasSelectpicker(select)) {
                    jQuery(select).selectpicker('refresh');
                }
            }

            function initSelects(container) {
                if (!window.jQuery || !jQuery.fn.selectpicker) {
                    return;
                }

                jQuery(container).find('select').each(function () {
                    if (!jQuery(this).data('selectpicker')) {
                        jQuery(this).selectpicker();
                    }
                });
            }

            function onFieldChange(field, handler) {
                if (window.jQuery) {
                    jQuery(field).on('change', handler);

                    return;
                }

                field.addEventListener('change', handler);
            }

            function stepFormAt(stepIndex) {
                var stepPane = stepPanes[stepIndex];

                return stepPane ? stepPane.querySelector('form.js-employee-data-step-form') : null;
            }

            function isEditable(form) {
                var fieldset = form ? form.querySelector('fieldset') : null;

                return !!(form && form.dataset.saveUrl && (!fieldset || !fieldset.disabled));
            }

            function serialize(form) {
                var pairs = [];

                new FormData(form).forEach(function (value, key) {
                    pairs.push(key + '=' + value);
                });

                return pairs.join('&');
            }

            function snapshot(form) {
                savedSnapshots.set(form, serialize(form));
            }

            function isDirty(form) {
                return savedSnapshots.get(form) !== serialize(form);
            }

            function setStatus(message, className) {
                if (!statusElement) {
                    return;
                }

                statusElement.className = 'small mb-0 mt-2 js-employee-data-save-status ' + className;
                statusElement.textContent = message;
            }

            function hideWizardError() {
                errorElement.classList.add('d-none');
                errorElement.replaceChildren();
            }

            function showWizardError(messages) {
                var title = document.createElement('strong');
                var list = document.createElement('ul');

                title.textContent = 'Data belum tersimpan. Periksa kembali isian berikut:';
                list.className = 'mb-0 mt-1';

                messages.filter(function (message, index) {
                    return messages.indexOf(message) === index;
                }).forEach(function (message) {
                    var item = document.createElement('li');

                    item.textContent = message;
                    list.appendChild(item);
                });

                errorElement.replaceChildren(title, list);
                errorElement.classList.remove('d-none');
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function clearFieldErrors(container) {
                container.querySelectorAll('.is-invalid').forEach(function (field) {
                    field.classList.remove('is-invalid');
                });
                container.querySelectorAll('.js-employee-data-field-error').forEach(function (feedback) {
                    feedback.remove();
                });
            }

            function fieldForErrorKey(form, errorKey) {
                var keyParts = errorKey.split('.');
                var fieldName = keyParts[0] + keyParts.slice(1).map(function (keyPart) {
                    return '[' + keyPart + ']';
                }).join('');

                return form.querySelector('[name="' + fieldName + '"]')
                    || form.querySelector('[name="' + keyParts[0] + '[]"]');
            }

            function showFieldErrors(form, errors) {
                var messages = [];

                Object.keys(errors).forEach(function (errorKey) {
                    var message = errors[errorKey][0];
                    var field = fieldForErrorKey(form, errorKey);

                    messages.push(message);

                    if (!field) {
                        return;
                    }

                    var feedbackAnchor = field.closest('.bootstrap-select') || field.closest('.input-group') || field;
                    var feedback = document.createElement('div');

                    field.classList.add('is-invalid');
                    feedbackAnchor.classList.add('is-invalid');
                    feedback.className = 'invalid-feedback d-block js-employee-data-field-error';
                    feedback.textContent = message;
                    feedbackAnchor.insertAdjacentElement('afterend', feedback);
                });

                showWizardError(messages);
            }

            function isFilledRow(row) {
                return Array.prototype.some.call(row.querySelectorAll('[data-row-field]'), function (field) {
                    return field.dataset.rowField !== 'id' && !field.disabled && String(field.value || '').trim() !== '';
                });
            }

            function rowsOf(repeatable) {
                return Array.prototype.slice.call(repeatable.querySelectorAll('.js-repeatable-rows > .js-repeatable-row'));
            }

            function reindexRows(repeatable) {
                var rowGroup = repeatable.dataset.repeatableGroup;

                rowsOf(repeatable).forEach(function (row, rowIndex) {
                    row.querySelectorAll('[data-row-field]').forEach(function (field) {
                        field.name = rowGroup + '[' + rowIndex + '][' + field.dataset.rowField + ']';
                    });
                });
            }

            function addRowAfter(repeatable, referenceRow) {
                var template = repeatable.querySelector('template.js-repeatable-template');
                var newRow = template.content.cloneNode(true).querySelector('.js-repeatable-row');
                var rowsContainer = repeatable.querySelector('.js-repeatable-rows');

                if (referenceRow && referenceRow.parentNode === rowsContainer) {
                    rowsContainer.insertBefore(newRow, referenceRow.nextSibling);
                } else {
                    rowsContainer.appendChild(newRow);
                }

                reindexRows(repeatable);
                initSelects(newRow);

                var firstField = newRow.querySelector('input:not([type="hidden"]), select');

                if (firstField) {
                    firstField.focus();
                }
            }

            function removeRow(repeatable, row) {
                if (rowsOf(repeatable).length > 1) {
                    row.remove();
                } else {
                    row.querySelectorAll('input, select, textarea').forEach(function (field) {
                        field.value = '';

                        if (field.tagName === 'SELECT') {
                            refreshSelect(field);
                        }
                    });
                    clearFieldErrors(row);
                }

                reindexRows(repeatable);
            }

            function applyRowIds(form, rowIds) {
                Object.keys(rowIds).forEach(function (rowGroup) {
                    var repeatable = form.querySelector('.js-repeatable[data-repeatable-group="' + rowGroup + '"]');
                    var savedIds = rowIds[rowGroup] || [];
                    var filledRowIndex = 0;

                    if (!repeatable) {
                        return;
                    }

                    rowsOf(repeatable).forEach(function (row) {
                        var idInput = row.querySelector('[data-row-field="id"]');

                        if (!idInput) {
                            return;
                        }

                        idInput.value = isFilledRow(row) ? (savedIds[filledRowIndex++] || '') : '';
                    });
                });
            }

            function errorMessageFor(status, payload) {
                if (status === 419) {
                    return 'Sesi Anda telah berakhir. Muat ulang halaman lalu coba lagi.';
                }

                if (status === 403 || status === 404) {
                    return 'Anda tidak memiliki akses untuk menyimpan data ini.';
                }

                return payload && payload.message ? payload.message : 'Gagal menyimpan data. Silakan coba lagi.';
            }

            function saveStep(form, isForced) {
                if (!isEditable(form) || (!isForced && !isDirty(form))) {
                    return Promise.resolve(true);
                }

                var formData = new FormData(form);

                formData.append('_method', 'PUT');
                clearFieldErrors(form);
                hideWizardError();
                setStatus('Menyimpan...', 'text-muted');

                return fetch(form.dataset.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData
                })
                    .then(function (response) {
                        return response.json()
                            .catch(function () {
                                return {};
                            })
                            .then(function (payload) {
                                return { ok: response.ok, status: response.status, payload: payload };
                            });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            if (result.status === 422 && result.payload.errors) {
                                showFieldErrors(form, result.payload.errors);
                            } else {
                                showWizardError([errorMessageFor(result.status, result.payload)]);
                            }

                            setStatus('Data belum tersimpan.', 'employee-data-save-error');

                            return false;
                        }

                        applyRowIds(form, result.payload.row_ids || {});
                        snapshot(form);
                        setStatus('Tersimpan pukul ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + '.', 'text-success');
                        wizardElement.dispatchEvent(new CustomEvent('employee-data:saved', { bubbles: true, detail: result.payload }));

                        return true;
                    })
                    .catch(function () {
                        showWizardError(['Koneksi terputus. Data belum tersimpan, silakan coba lagi.']);
                        setStatus('Data belum tersimpan.', 'employee-data-save-error');

                        return false;
                    });
            }

            function fetchRegions(regionLevel, parentCode) {
                var regionUrl = regionLevel === 'province'
                    ? regionUrls.province
                    : regionUrls[regionLevel] + '/' + encodeURIComponent(parentCode);

                if (!regionUrl) {
                    return Promise.resolve(null);
                }

                if (!regionRequests[regionUrl]) {
                    regionRequests[regionUrl] = fetch(regionUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Region request failed');
                            }

                            return response.json();
                        })
                        .then(function (payload) {
                            return Array.isArray(payload.data) ? payload.data : [];
                        })
                        .catch(function () {
                            delete regionRequests[regionUrl];

                            return null;
                        });
                }

                return regionRequests[regionUrl];
            }

            function resetRegionSelect(select) {
                var placeholderText = select.options[0] ? select.options[0].text : '';

                select.replaceChildren(new Option(placeholderText, ''));
                refreshSelect(select);
            }

            function fillRegionSelect(select, regions, selectedName) {
                var placeholderText = select.options[0] ? select.options[0].text : '';
                var normalizedSelectedName = String(selectedName || '').trim().toLowerCase();
                var selectedCode = null;

                select.replaceChildren(new Option(placeholderText, ''));

                regions.forEach(function (region) {
                    var option = new Option(region.name, region.name);

                    option.dataset.regionCode = region.code;

                    if (normalizedSelectedName !== '' && String(region.name).trim().toLowerCase() === normalizedSelectedName) {
                        option.selected = true;
                        selectedCode = region.code;
                    }

                    select.appendChild(option);
                });

                if (normalizedSelectedName !== '' && selectedCode === null) {
                    select.appendChild(new Option(selectedName, selectedName, true, true));
                }

                refreshSelect(select);

                return selectedCode;
            }

            function initRegionAddress(addressContainer) {
                var regionSelects = {};

                regionLevels.forEach(function (regionLevel) {
                    regionSelects[regionLevel] = addressContainer.querySelector('.js-region-select[data-region-level="' + regionLevel + '"]');
                });

                function loadRegionLevel(levelIndex, parentCode, selectedName) {
                    var regionLevel = regionLevels[levelIndex];
                    var select = regionSelects[regionLevel];

                    if (!select || (regionLevel !== 'province' && !parentCode)) {
                        return Promise.resolve();
                    }

                    return fetchRegions(regionLevel, parentCode).then(function (regions) {
                        if (regions === null) {
                            return;
                        }

                        var selectedCode = fillRegionSelect(select, regions, selectedName);
                        var nextRegionLevel = regionLevels[levelIndex + 1];

                        if (nextRegionLevel && selectedCode && regionSelects[nextRegionLevel]) {
                            return loadRegionLevel(levelIndex + 1, selectedCode, regionSelects[nextRegionLevel].value);
                        }
                    });
                }

                regionLevels.forEach(function (regionLevel, levelIndex) {
                    var select = regionSelects[regionLevel];

                    if (!select) {
                        return;
                    }

                    onFieldChange(select, function () {
                        var selectedOption = select.options[select.selectedIndex];
                        var selectedCode = selectedOption && selectedOption.dataset.regionCode ? selectedOption.dataset.regionCode : null;

                        regionLevels.slice(levelIndex + 1).forEach(function (lowerRegionLevel) {
                            if (regionSelects[lowerRegionLevel]) {
                                resetRegionSelect(regionSelects[lowerRegionLevel]);
                            }
                        });

                        if (selectedCode) {
                            loadRegionLevel(levelIndex + 1, selectedCode, '');
                        }
                    });
                });

                loadRegionLevel(0, null, regionSelects.province ? regionSelects.province.value : '');
            }

            function syncDomicileAddress() {
                var sameAsKtpCheckbox = wizardElement.querySelector('.js-domicile-same-as-ktp');
                var domicileAddress = wizardElement.querySelector('.js-domicile-address');

                if (!sameAsKtpCheckbox || !domicileAddress) {
                    return;
                }

                domicileAddress.classList.toggle('d-none', sameAsKtpCheckbox.checked);
                domicileAddress.querySelectorAll('input, select').forEach(function (field) {
                    field.disabled = sameAsKtpCheckbox.checked;

                    if (field.tagName === 'SELECT') {
                        refreshSelect(field);
                    }
                });
            }

            function syncEmploymentEndDate() {
                var employmentStatusSelect = wizardElement.querySelector('[data-employment-status-select]');
                var endDateInput = wizardElement.querySelector('[data-employment-end-date]');

                if (!employmentStatusSelect || !endDateInput) {
                    return;
                }

                var hasEndDate = employmentStatusSelect.value === 'PKWT' || employmentStatusSelect.value === 'Probation';

                endDateInput.disabled = !hasEndDate;

                if (!hasEndDate) {
                    endDateInput.value = '';
                }
            }

            function formatCurrencyInput(input) {
                var digits = String(input.value || '').replace(/\D+/g, '');

                input.value = digits !== '' ? Number(digits).toLocaleString('id-ID') : '';
            }

            wizardElement.addEventListener('click', function (event) {
                var actionButton = event.target.closest('.js-repeatable-add, .js-repeatable-remove');

                if (!actionButton || actionButton.disabled || actionButton.closest('fieldset[disabled]')) {
                    return;
                }

                var repeatable = actionButton.closest('.js-repeatable');
                var row = actionButton.closest('.js-repeatable-row');

                if (actionButton.classList.contains('js-repeatable-add')) {
                    addRowAfter(repeatable, row);
                } else {
                    removeRow(repeatable, row);
                }
            });

            wizardElement.querySelectorAll('.js-region-address').forEach(initRegionAddress);
            wizardElement.querySelectorAll('[data-currency-input]').forEach(function (input) {
                input.addEventListener('input', function () {
                    formatCurrencyInput(input);
                });
            });

            var sameAsKtpCheckbox = wizardElement.querySelector('.js-domicile-same-as-ktp');
            var employmentStatusSelect = wizardElement.querySelector('[data-employment-status-select]');

            if (sameAsKtpCheckbox) {
                sameAsKtpCheckbox.addEventListener('change', syncDomicileAddress);
            }

            if (employmentStatusSelect) {
                onFieldChange(employmentStatusSelect, syncEmploymentEndDate);
            }

            syncDomicileAddress();
            syncEmploymentEndDate();

            stepForms.forEach(function (form) {
                snapshot(form);

                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    var submitButton = form.querySelector('.js-employee-data-step-submit');

                    if (submitButton) {
                        submitButton.disabled = true;
                    }

                    saveStep(form, true).finally(function () {
                        if (submitButton) {
                            submitButton.disabled = false;
                        }
                    });
                });
            });

            window.addEventListener('beforeunload', function (event) {
                var hasUnsavedStep = stepForms.some(function (form) {
                    return isEditable(form) && isDirty(form);
                });

                if (hasUnsavedStep) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });

            var hostTabPane = wizardElement.closest('.tab-pane');
            var hostTabTrigger = hostTabPane && hostTabPane.id
                ? document.querySelector('[data-bs-target="#' + hostTabPane.id + '"]')
                : null;

            if (hostTabTrigger) {
                hostTabTrigger.addEventListener('hide.bs.tab', function () {
                    var currentForm = stepFormAt(currentStepIndex);

                    if (currentForm) {
                        saveStep(currentForm, false);
                    }
                });
            }

            if (!window.jQuery || !jQuery.fn.smartWizard) {
                return;
            }

            var $wizard = jQuery(wizardElement);
            var isNavigatingAfterSave = false;

            $wizard.smartWizard({
                autoAdjustHeight: false,
                enableURLhash: false,
                keyboardSettings: { keyNavigation: false },
                transition: { animation: 'none' },
                anchorSettings: { enableAllAnchors: true },
                lang: { next: 'Selanjutnya', previous: 'Sebelumnya' }
            });

            $wizard.on('showStep', function (event, anchorObject, stepIndex) {
                currentStepIndex = Number(stepIndex) || 0;
            });

            $wizard.on('leaveStep', function (event, anchorObject, leavingStepIndex, nextStepIndex) {
                if (isNavigatingAfterSave) {
                    isNavigatingAfterSave = false;

                    return true;
                }

                var leavingForm = stepFormAt(leavingStepIndex);

                if (!isEditable(leavingForm) || !isDirty(leavingForm)) {
                    return true;
                }

                saveStep(leavingForm, false).then(function (isSaved) {
                    if (!isSaved) {
                        return;
                    }

                    isNavigatingAfterSave = true;
                    $wizard.smartWizard('goToStep', nextStepIndex);
                    isNavigatingAfterSave = false;
                });

                return false;
            });
        });
    })();
</script>
