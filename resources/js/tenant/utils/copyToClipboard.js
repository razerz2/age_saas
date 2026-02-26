export function copyToClipboard(text) {
    const value = String(text || '');

    if (value === '') {
        return Promise.reject(new Error('empty_text'));
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(value);
    }

    return new Promise((resolve, reject) => {
        const textarea = document.createElement('textarea');
        textarea.value = value;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.pointerEvents = 'none';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            const copied = document.execCommand('copy');
            document.body.removeChild(textarea);

            if (copied) {
                resolve();
                return;
            }

            reject(new Error('copy_failed'));
        } catch (error) {
            document.body.removeChild(textarea);
            reject(error);
        }
    });
}
