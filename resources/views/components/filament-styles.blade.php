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

    .fi-sidebar-toggle {
        position: fixed;
        top: 5.5rem;
        left: 15.25rem;
        z-index: 9998;
        display: none;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 0.9rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        background: rgba(15, 23, 42, 0.85);
        color: #f8fafc;
        font-size: 0.85rem;
        font-weight: 500;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.25);
        transition: background 150ms ease, transform 150ms ease;
    }

    .fi-sidebar-toggle:hover {
        background: rgba(15, 23, 42, 0.95);
    }

    .fi-sidebar-toggle svg {
        width: 1rem;
        height: 1rem;
    }

    @media (min-width: 1024px) {
        .fi-sidebar-toggle {
            display: inline-flex;
        }
    }

    body.sidebar-collapsed .fi-sidebar-toggle {
        left: 1rem;
    }

    body.sidebar-collapsed .fi-sidebar {
        width: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0;
        opacity: 0;
        pointer-events: none;
    }

    body.sidebar-collapsed .fi-layout {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    body.sidebar-collapsed .fi-main {
        max-width: 100% !important;
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

        const setupSidebarToggle = () => {
            const STORAGE_KEY = 'filament.sidebarCollapsed';
            const existingButton = document.querySelector('.fi-sidebar-toggle');

            if (existingButton) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'fi-sidebar-toggle';
            button.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                    <line x1="4" y1="18" x2="14" y2="18" />
                </svg>
                <span class="fi-sidebar-toggle__label">Скрыть меню</span>
            `;

            const label = button.querySelector('.fi-sidebar-toggle__label');
            const applyState = (collapsed) => {
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                button.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                label.textContent = collapsed ? 'Показать меню' : 'Скрыть меню';
            };

            let collapsed = localStorage.getItem(STORAGE_KEY) === '1';
            applyState(collapsed);

            button.addEventListener('click', () => {
                collapsed = !collapsed;
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
                applyState(collapsed);
            });

            document.body.appendChild(button);
        };

        setupSidebarToggle();
    });
</script>
