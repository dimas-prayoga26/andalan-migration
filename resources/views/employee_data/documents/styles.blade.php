<style>
    .employee-document-card {
        border: 1px solid #e7eaf3;
        border-radius: 0.5rem;
        padding: 1rem;
        background: var(--bs-body-bg, #fff);
        display: flex;
        flex-direction: column;
    }

    .employee-document-dropzone {
        display: block;
        border: 2px dashed #cfd6e4;
        border-radius: 0.5rem;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }

    .employee-document-dropzone:hover,
    .employee-document-dropzone.is-dragover {
        border-color: var(--bs-primary);
        background-color: rgba(40, 70, 199, 0.05);
    }

    .employee-document-dropzone .fa {
        font-size: 1.5rem;
        color: var(--bs-primary);
        margin-bottom: 0.25rem;
    }
</style>
