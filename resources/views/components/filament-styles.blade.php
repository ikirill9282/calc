<style>
    .fi-ta-table thead th,
    .fi-ta-table tbody td {
        border-right: 1px solid rgba(148, 163, 184, 0.4);
        padding: 0.35rem 0.5rem;
    }

    .fi-ta-table table {
        table-layout: auto !important;
    }

    .fi-ta-table thead tr {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    }

    .fi-ta-table thead th {
        color: #f8fafc;
        font-weight: 600;
        border-right-color: rgba(248, 250, 252, 0.25);
        white-space: nowrap;
    }

    .fi-ta-table thead th > div,
    .fi-ta-table thead th .fi-ta-header-cell-label-wrapper {
        white-space: nowrap !important;
        display: flex !important;
        align-items: center;
        flex-wrap: nowrap !important;
    }

    .fi-ta-table thead th .fi-ta-header-cell-label {
        color: inherit;
        white-space: nowrap !important;
        word-break: normal !important;
        overflow: hidden;
        text-overflow: ellipsis;
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
        white-space: nowrap !important;
        word-break: normal !important;
    }

    .fi-ta-table thead th .fi-icon {
        color: rgba(248, 250, 252, 0.8);
    }

    .fi-ta-table tbody td {
        white-space: nowrap;
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

    .fi-inline-editable-cell {
        cursor: pointer;
    }

    .fi-order-heading {
        margin: 0.75rem 0 0.25rem;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #475569;
    }

    .fi-ta-summary {
        display: none !important;
    }

    .fi-resource-orders {
        --fi-orders-topbar-offset: 4rem;
    }

    .fi-resource-orders .fi-ta-table thead th {
        position: sticky;
        top: var(--fi-orders-topbar-offset);
        z-index: 20;
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    }

    .fi-resource-orders .fi-ta-selection-cell {
        padding: 0 !important;
    }

    .fi-resource-orders .fi-ta-selection-cell > div {
        padding: 0 !important;
        height: 100%;
        min-height: 2.5rem;
        display: flex;
        align-items: stretch;
    }

    .fi-resource-orders .fi-ta-selection-cell label {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.65rem 0.75rem;
        cursor: pointer;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const showToast = (() => {
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

        const updateTopbarOffset = () => {
            const root = document.querySelector('.fi-resource-orders');
            if (!root) {
                return;
            }

            const topbar = document.querySelector('.fi-topbar');
            const topbarHeight = Math.ceil(topbar?.getBoundingClientRect()?.height ?? 64);
            root.style.setProperty('--fi-orders-topbar-offset', `${topbarHeight}px`);
        };

        const openInlineEditPrompt = (cell) => {
            const recordId = cell.getAttribute('data-inline-record');
            const field = cell.getAttribute('data-inline-field');

            if (!recordId || !field || !window.Livewire?.dispatch) {
                return;
            }

            const label = cell.getAttribute('data-inline-label') || 'значение';
            const currentValue = cell.getAttribute('data-inline-value') ?? cell.innerText.trim();

            let promptText = `Изменить «${label}»`;
            if (field === 'distribution_edit') {
                promptText += '\n\nФормат: РЦ|Адрес\nПример: Wildberries - Москва|ул. Примерная, д. 1';
            }

            const nextValue = window.prompt(promptText, currentValue);

            if (nextValue === null || nextValue === currentValue) {
                return;
            }

            cell.setAttribute('data-inline-value', nextValue);

            window.Livewire.dispatch('inlineEditCell', {
                recordId,
                field,
                value: nextValue,
            });
        };

        document.addEventListener('click', async (event) => {
            if (event.shiftKey) {
                return;
            }

            const selectionCell = event.target.closest('.fi-resource-orders .fi-ta-selection-cell');
            if (selectionCell && !event.target.closest('input, label, button, a, textarea, select')) {
                const checkbox = selectionCell.querySelector('input[type="checkbox"]');
                if (checkbox && !checkbox.disabled) {
                    checkbox.click();
                }
                return;
            }

            const cell = event.target.closest('.fi-ta-table tbody td');

            if (!cell) {
                return;
            }

            if (event.target.closest('button, a, input, textarea, label, [data-no-copy]')) {
                return;
            }

            if (cell.matches('[data-inline-editable="1"]')) {
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
            showToast(`Скопировано: ${preview}`);
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

        const handleInlineDoubleClick = (event) => {
            if (event.shiftKey || event.button !== 0) {
                return;
            }

            if (event.target.closest('button, a, input, textarea, label, select, [data-no-inline-edit]')) {
                return;
            }

            const cell = event.target.closest('[data-inline-editable="1"]');

            if (!cell) {
                return;
            }

            event.preventDefault();
            openInlineEditPrompt(cell);
        };

        document.addEventListener('dblclick', handleInlineDoubleClick);
        window.addEventListener('resize', updateTopbarOffset);

        window.addEventListener('inline-edit-cell-saved', () => {
            showToast('Значение обновлено');
        });

        window.addEventListener('inline-edit-cell-error', (event) => {
            const message = event.detail?.message ?? 'Не удалось обновить';
            showToast(message);
        });

        setupSidebarToggle();
        updateTopbarOffset();

        document.addEventListener('livewire:navigated', () => {
            setupSidebarToggle();
            updateTopbarOffset();
        });
    });
</script>
