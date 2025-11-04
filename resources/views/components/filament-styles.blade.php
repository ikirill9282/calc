<style>
    .fi-ta-table thead th,
    .fi-ta-table tbody td {
        border-right: 1px solid rgba(148, 163, 184, 0.4);
    }

    .fi-ta-table thead th:last-child,
    .fi-ta-table tbody td:last-child {
        border-right: none;
    }

    .copy-toast {
        position: fixed;
        z-index: 9999;
        right: 1.5rem;
        bottom: 1.5rem;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        background: rgba(15, 23, 42, 0.9);
        color: #f8fafc;
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.25);
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none;
        transition: opacity 150ms ease, transform 150ms ease;
    }

    .copy-toast--visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const showCopyToast = (() => {
            let timeoutId;
            let toast;

            return (text) => {
                if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'copy-toast';
                    document.body.appendChild(toast);
                }

                toast.textContent = text;
                toast.classList.add('copy-toast--visible');

                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    toast.classList.remove('copy-toast--visible');
                }, 1600);
            };
        })();

        document.addEventListener('click', async (event) => {
            const cell = event.target.closest('.fi-ta-table tbody td');

            if (!cell) {
                return;
            }

            if (event.target.closest('button, a, input, textarea, label, [data-no-copy]')) {
                return;
            }

            const text = cell.innerText.replace(/\s+/g, ' ').trim();

            if (!text.length) {
                return;
            }

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'absolute';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                }
            } catch (error) {
                console.error('Clipboard copy failed', error);
                return;
            }

            const preview = text.length > 60 ? `${text.slice(0, 57)}…` : text;
            showCopyToast(`Скопировано: ${preview}`);
        });
    });
</script>
