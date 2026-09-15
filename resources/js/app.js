const fileInput = document.querySelector('#file');
const selectedFile = document.querySelector('#selected-file');
const selectedFileName = document.querySelector('#selected-file-name');
const selectedFileSize = document.querySelector('#selected-file-size');
const removeFileButton = document.querySelector('#remove-file');

if (fileInput && selectedFile && selectedFileName && selectedFileSize && removeFileButton) {
    const formatFileSize = (sizeInBytes) => {
        if (sizeInBytes < 1_000) {
            return `${sizeInBytes} bytes`;
        }

        if (sizeInBytes < 1_000_000) {
            return `${(sizeInBytes / 1_000).toLocaleString('pt-BR', { maximumFractionDigits: 1 })} KB`;
        }

        return `${(sizeInBytes / 1_000_000).toLocaleString('pt-BR', { maximumFractionDigits: 2 })} MB`;
    };

    const clearSelectedFile = () => {
        fileInput.value = '';
        selectedFile.classList.add('hidden');
        selectedFile.classList.remove('flex');
        selectedFileName.textContent = '';
        selectedFileSize.textContent = '';
    };

    fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0];

        if (!file) {
            clearSelectedFile();

            return;
        }

        selectedFileName.textContent = file.name;
        selectedFileSize.textContent = formatFileSize(file.size);
        selectedFile.classList.remove('hidden');
        selectedFile.classList.add('flex');
    });

    removeFileButton.addEventListener('click', () => {
        clearSelectedFile();
        fileInput.focus();
    });
}
