<script>
    document.addEventListener('DOMContentLoaded', function () {
        function formatFileSize(bytes) {
            return bytes >= 1024 * 1024
                ? (bytes / (1024 * 1024)).toFixed(1) + ' MB'
                : Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function renderDocumentStatus(statusElement, message, className) {
            var statusText = document.createElement('span');

            statusText.className = className;
            statusText.textContent = message;
            statusElement.replaceChildren(statusText);
        }

        document.querySelectorAll('.js-document-dropzone').forEach(function (dropzone) {
            var fileInput = dropzone.querySelector('input[type="file"]');
            var statusElement = dropzone.closest('.employee-document-card').querySelector('.js-document-status');
            var maxSizeMb = Number(dropzone.dataset.maxSizeMb || 2);
            var acceptedExtensions = (dropzone.dataset.acceptedExtensions || '').split(',');

            if (!fileInput || !statusElement) {
                return;
            }

            function showSelectedFile(file) {
                var extension = (file.name.split('.').pop() || '').toLowerCase();

                if (acceptedExtensions.indexOf(extension) === -1) {
                    fileInput.value = '';
                    renderDocumentStatus(statusElement, 'Format tidak didukung. Gunakan ' + dropzone.dataset.formatLabel + '.', 'text-danger');

                    return;
                }

                if (file.size > maxSizeMb * 1024 * 1024) {
                    fileInput.value = '';
                    renderDocumentStatus(statusElement, 'File terlalu besar. Maksimal ' + maxSizeMb + ' MB.', 'text-danger');

                    return;
                }

                renderDocumentStatus(statusElement, file.name + ' (' + formatFileSize(file.size) + ') · belum disimpan', 'text-warning');
            }

            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    showSelectedFile(fileInput.files[0]);
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

                if (!droppedFile) {
                    return;
                }

                if (window.DataTransfer) {
                    var dataTransfer = new DataTransfer();

                    dataTransfer.items.add(droppedFile);
                    fileInput.files = dataTransfer.files;
                }

                showSelectedFile(droppedFile);
            });
        });
    });
</script>
