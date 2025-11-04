<style>
    .fi-ta-table thead th,
    .fi-ta-table tbody td {
        border-right: 1px solid rgba(148, 163, 184, 0.4);
    }

    .fi-ta-table thead th:last-child,
    .fi-ta-table tbody td:last-child {
        border-right: none;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
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

                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        status: 'success',
                        message: 'Скопировано',
                    },
                }));
            } catch (error) {
                console.error('Clipboard copy failed', error);
            }
        });
    });
</script>
