<script>
    document.addEventListener('DOMContentLoaded', function () {
        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        function formatFileSize(bytes) {
            return bytes >= 1024 * 1024
                ? (bytes / (1024 * 1024)).toFixed(1) + ' MB'
                : Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function renderMessage(card, message, className) {
            var messageElement = card.querySelector('.js-document-message');

            if (!messageElement) {
                return;
            }

            messageElement.className = 'small js-document-message' + (message ? ' mb-1 ' + className : '');
            messageElement.textContent = message || '';
        }

        function iconElement(className) {
            var icon = document.createElement('i');

            icon.className = className;

            return icon;
        }

        function renderFiles(card, files) {
            var statusElement = card.querySelector('.js-document-status');
            var rows = files.map(function (file) {
                var row = document.createElement('div');
                var link = document.createElement('a');
                var size = document.createElement('span');

                row.className = 'd-flex align-items-center gap-2 employee-document-file';
                link.href = file.url || '#';
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'text-truncate';
                link.append(iconElement('fa fa-file me-1'), document.createTextNode(file.name));
                size.className = 'text-muted text-nowrap';
                size.textContent = file.size || '';
                row.append(link, size);

                if (file.download_url) {
                    var downloadLink = document.createElement('a');

                    downloadLink.href = file.download_url;
                    downloadLink.className = 'ms-auto text-muted';
                    downloadLink.title = 'Download';
                    downloadLink.setAttribute('aria-label', 'Download');
                    downloadLink.append(iconElement('fa fa-download'));
                    row.append(downloadLink);
                }

                if (file.delete_url) {
                    var deleteButton = document.createElement('button');

                    deleteButton.type = 'button';
                    deleteButton.className = 'btn btn-link btn-sm p-0 employee-document-error js-document-delete';
                    deleteButton.dataset.deleteUrl = file.delete_url;
                    deleteButton.dataset.fileName = file.name;
                    deleteButton.title = 'Hapus';
                    deleteButton.setAttribute('aria-label', 'Hapus');
                    deleteButton.append(iconElement('fa fa-trash'));
                    row.append(deleteButton);
                }

                return row;
            });

            if (rows.length === 0) {
                var emptyState = document.createElement('span');

                emptyState.className = 'text-muted';
                emptyState.append(
                    iconElement('fa fa-circle me-1 ' + (card.dataset.isRequired === '1' ? 'employee-document-required-dot' : 'employee-document-optional-dot')),
                    document.createTextNode('Belum diunggah')
                );
                rows.push(emptyState);
            }

            statusElement.replaceChildren.apply(statusElement, rows);
        }

        function updateRequiredCounter(vault, payload) {
            var counter = vault.querySelector('.js-document-required-counter');

            if (!counter || typeof payload.required_uploaded_count === 'undefined') {
                return;
            }

            counter.textContent = payload.required_uploaded_count + '/' + payload.required_total + ' Dokumen Wajib Terunggah';
            counter.classList.toggle('badge-success', !payload.has_missing_required_documents);
            counter.classList.toggle('badge-warning', !!payload.has_missing_required_documents);
        }

        function sendVaultRequest(url, method, body) {
            return fetch(url, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: body
            }).then(function (response) {
                return response.json()
                    .catch(function () {
                        return {};
                    })
                    .then(function (payload) {
                        if (!response.ok) {
                            var errorMessage = payload.errors
                                ? payload.errors[Object.keys(payload.errors)[0]][0]
                                : (response.status === 419 ? 'Sesi Anda telah berakhir. Muat ulang halaman lalu coba lagi.' : (payload.message || 'Permintaan gagal. Silakan coba lagi.'));

                            throw new Error(errorMessage);
                        }

                        return payload;
                    });
            });
        }

        function applyVaultPayload(vault, card, payload, message) {
            renderFiles(card, payload.files || []);
            renderMessage(card, message, 'text-success');
            updateRequiredCounter(vault, payload);
            vault.dispatchEvent(new CustomEvent('employee-documents:changed', { bubbles: true, detail: payload }));
        }

        document.querySelectorAll('.js-employee-document-vault').forEach(function (vault) {
            var uploadUrl = vault.dataset.uploadUrl || '';

            vault.addEventListener('click', function (event) {
                var deleteButton = event.target.closest('.js-document-delete');

                if (!deleteButton || deleteButton.disabled) {
                    return;
                }

                var card = deleteButton.closest('.js-document-card');

                if (!window.confirm('Hapus dokumen "' + (deleteButton.dataset.fileName || '') + '"?')) {
                    return;
                }

                var formData = new FormData();

                formData.append('_method', 'DELETE');
                deleteButton.disabled = true;
                renderMessage(card, 'Menghapus...', 'text-muted');

                sendVaultRequest(deleteButton.dataset.deleteUrl, 'POST', formData)
                    .then(function (payload) {
                        applyVaultPayload(vault, card, payload, payload.message || 'Dokumen dihapus.');
                    })
                    .catch(function (error) {
                        deleteButton.disabled = false;
                        renderMessage(card, error.message, 'employee-document-error');
                    });
            });

            vault.querySelectorAll('.js-document-dropzone').forEach(function (dropzone) {
                var fileInput = dropzone.querySelector('input[type="file"]');
                var card = dropzone.closest('.js-document-card');
                var maxSizeMb = Number(dropzone.dataset.maxSizeMb || 2);
                var acceptedExtensions = (dropzone.dataset.acceptedExtensions || '').split(',');

                if (!fileInput || !card) {
                    return;
                }

                function uploadFile(file) {
                    var extension = (file.name.split('.').pop() || '').toLowerCase();

                    fileInput.value = '';

                    if (acceptedExtensions.indexOf(extension) === -1) {
                        renderMessage(card, 'Format tidak didukung. Gunakan ' + dropzone.dataset.formatLabel + '.', 'employee-document-error');

                        return;
                    }

                    if (file.size > maxSizeMb * 1024 * 1024) {
                        renderMessage(card, 'File terlalu besar. Maksimal ' + maxSizeMb + ' MB.', 'employee-document-error');

                        return;
                    }

                    if (uploadUrl === '') {
                        renderMessage(card, file.name + ' (' + formatFileSize(file.size) + ') · belum disimpan', 'text-warning');

                        return;
                    }

                    var formData = new FormData();

                    formData.append('document_type', card.dataset.documentKey);
                    formData.append('file', file);
                    dropzone.classList.add('is-uploading');
                    renderMessage(card, 'Mengunggah ' + file.name + '...', 'text-muted');

                    sendVaultRequest(uploadUrl, 'POST', formData)
                        .then(function (payload) {
                            applyVaultPayload(vault, card, payload, payload.message || 'Dokumen diunggah.');
                        })
                        .catch(function (error) {
                            renderMessage(card, error.message, 'employee-document-error');
                        })
                        .finally(function () {
                            dropzone.classList.remove('is-uploading');
                        });
                }

                fileInput.addEventListener('change', function () {
                    if (fileInput.files && fileInput.files[0]) {
                        uploadFile(fileInput.files[0]);
                    }
                });

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.add('is-dragover');
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.remove('is-dragover');
                    });
                });

                dropzone.addEventListener('drop', function (event) {
                    var droppedFile = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null;

                    if (droppedFile) {
                        uploadFile(droppedFile);
                    }
                });
            });
        });
    });
</script>
