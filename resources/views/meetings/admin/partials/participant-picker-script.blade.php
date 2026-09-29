<script>
    $(function () {
        $('[data-hr-participant-picker]').each(function () {
            var picker = $(this);
            var groupInput = picker.find('.hr-participant-group-input');
            var toggle = picker.find('.hr-participant-toggle');
            var summary = picker.find('.hr-participant-summary');
            var search = picker.find('.hr-participant-search');
            var groupButtons = picker.find('[data-group-option]');
            var employeeOptions = picker.find('[data-employee-option]');
            var employeeChecks = picker.find('.hr-participant-employee-checkbox');

            function checkedEmployees() {
                return employeeChecks.filter(':checked');
            }

            function updateSummary() {
                var group = groupInput.val();
                var checked = checkedEmployees();

                if (group === 'all_staff') {
                    summary.text('All Staff');
                    return;
                }

                if (group === 'bod') {
                    summary.text('BOD (' + checked.length + ' Staff)');
                    return;
                }

                if (! checked.length) {
                    summary.text('Nothing selected');
                    return;
                }

                var names = checked.map(function () {
                    return $(this).closest('[data-employee-option]').find('span').text().trim();
                }).get();

                summary.text(names.length > 2 ? names.slice(0, 2).join(', ') + ' +' + (names.length - 2) : names.join(', '));
            }

            function setGroup(group, applySelection) {
                groupInput.val(group);
                groupButtons.removeClass('is-active');
                groupButtons.filter('[data-group-option="' + group + '"]').addClass('is-active');

                if (applySelection && group === 'all_staff') {
                    employeeChecks.prop('checked', true);
                }

                if (applySelection && group === 'bod') {
                    employeeChecks.each(function () {
                        var checkbox = $(this);
                        checkbox.prop('checked', checkbox.data('supervisor') === true || checkbox.attr('data-supervisor') === 'true');
                    });
                }

                updateSummary();
            }

            groupButtons.on('click', function () {
                var group = $(this).data('group-option');
                setGroup(group, group !== 'custom');
            });

            employeeChecks.on('change', function () {
                setGroup('custom', false);
            });

            toggle.on('click', function () {
                var isOpen = picker.toggleClass('is-open').hasClass('is-open');
                toggle.attr('aria-expanded', isOpen ? 'true' : 'false');

                if (isOpen) {
                    search.trigger('focus');
                }
            });

            search.on('input', function () {
                var keyword = $(this).val().trim().toLowerCase();

                employeeOptions.each(function () {
                    var option = $(this);
                    option.toggle(option.data('employee-name').indexOf(keyword) !== -1);
                });
            });

            $(document).on('click', function (event) {
                if ($(event.target).closest(picker).length) {
                    return;
                }

                picker.removeClass('is-open');
                toggle.attr('aria-expanded', 'false');
            });

            setGroup(groupInput.val() || 'all_staff', groupInput.val() !== 'custom');
        });
    });
</script>
