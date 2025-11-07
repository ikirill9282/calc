<style>
    .fi-ta-table thead th,
    .fi-ta-table tbody td {
        border-right: 1px solid rgba(148, 163, 184, 0.4);
        padding: 0.35rem 0.5rem;
    }

    .fi-ta-table thead th:last-child,
    .fi-ta-table tbody td:last-child {
        border-right: none;
    }
		.fi-ta-table .py-4{
			padding-top: 0!important;
			padding-bottom: 0!important;
		}
		.fi-ta-table .px-3 {
			padding-left: .2rem !important;
			padding-right: .2rem!important;
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

    .fi-ta-header-cell-label {
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .fi-sidebar-toggle {
        display: none;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(148, 163, 184, 0.5);
        background: rgba(15, 23, 42, 0.05);
        color: #0f172a;
        transition: background 150ms ease, border-color 150ms ease, color 150ms ease;
    }

    .fi-sidebar-toggle:hover {
        background: rgba(15, 23, 42, 0.15);
        border-color: rgba(15, 23, 42, 0.6);
    }

    .fi-sidebar-toggle svg {
        width: 1.1rem;
        height: 1.1rem;
    }

    .dark .fi-sidebar-toggle {
        background: rgba(248, 250, 252, 0.05);
        color: #e2e8f0;
        border-color: rgba(248, 250, 252, 0.3);
    }

    .dark .fi-sidebar-toggle:hover {
        background: rgba(248, 250, 252, 0.15);
        border-color: rgba(248, 250, 252, 0.5);
    }

    @media (min-width: 1024px) {
        .fi-sidebar-toggle {
            display: inline-flex;
        }
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

    .fi-modal-content {
        padding: 1rem !important;
    }

    .fi-modal-content .fi-in-grid {
        gap: 0.5rem !important;
    }

    .fi-modal-content .fi-in-entry {
        padding: 0.15rem 0 !important;
    }

    .fi-order-heading {
        margin: 0.75rem 0 0.25rem;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #475569;
    }

    .fi-dropdown-panel {
        width: 500px !important;
        max-width: calc(100vw - 2rem)!important;
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
            const nav = document.querySelector('.fi-topbar nav');

            if (!nav || nav.querySelector('.fi-sidebar-toggle')) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'fi-sidebar-toggle';
            button.setAttribute('aria-label', 'Переключить меню');
            button.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="7" x2="20" y2="7" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                    <line x1="4" y1="17" x2="20" y2="17" />
                </svg>
            `;

            const applyState = (collapsed) => {
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                button.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            };

            let collapsed = localStorage.getItem(STORAGE_KEY) === '1';
            applyState(collapsed);

            button.addEventListener('click', () => {
                collapsed = !collapsed;
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
                applyState(collapsed);
            });

            nav.insertBefore(button, nav.firstChild);
        };

        setupSidebarToggle();
        document.addEventListener('livewire:navigated', setupSidebarToggle);
    });
</script>
