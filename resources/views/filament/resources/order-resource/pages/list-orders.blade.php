<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{-- Сводка по выбранным заявкам --}}
        <div 
            id="selected-orders-summary-container" 
            class="mt-6"
            style="min-height: 50px; position: relative; z-index: 10;"
        >
            {{-- Контент будет обновляться через JavaScript --}}
        </div>

        <script>
            (function() {
                let updateTimeout;
                
                function getContainer() {
                    return document.getElementById('selected-orders-summary-container');
                }
                
                function formatNumber(num, decimals = 0) {
                    return Number(num || 0).toLocaleString('ru-RU', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                }
                
                function updateSummary() {
                    clearTimeout(updateTimeout);
                    updateTimeout = setTimeout(function() {
                        const selectedIds = [];
                        
                        // Способ 1: Ищем все отмеченные чекбоксы и извлекаем ID из строки таблицы
                        const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
                        checkedBoxes.forEach(function(checkbox) {
                            // Ищем строку таблицы, содержащую этот чекбокс
                            const row = checkbox.closest('tr');
                            if (row) {
                                // Ищем все ячейки в строке
                                const cells = row.querySelectorAll('td, th');
                                // Обычно ID находится во второй ячейке (после чекбокса)
                                for (let i = 0; i < cells.length; i++) {
                                    const cellText = cells[i]?.textContent?.trim();
                                    // Проверяем, является ли текст числом (ID заявки)
                                    const id = parseInt(cellText);
                                    if (id && !isNaN(id) && id > 100000 && id < 999999) {
                                        // Это похоже на ID заявки
                                        if (!selectedIds.includes(id)) {
                                            selectedIds.push(id);
                                        }
                                        break;
                                    }
                                }
                            }
                        });
                        
                        // Способ 2: Если не нашли через строки, пробуем через wire:model
                        if (selectedIds.length === 0) {
                            const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
                            allCheckboxes.forEach(function(checkbox) {
                                if (checkbox.checked) {
                                    const wireModel = checkbox.getAttribute('wire:model') || 
                                                    checkbox.getAttribute('wire\\:model') ||
                                                    checkbox.getAttribute('data-wire-model');
                                    if (wireModel) {
                                        const match = wireModel.match(/selectedTableRecords\[(\d+)\]|selectedTableRecords\.(\d+)/);
                                        if (match) {
                                            const id = parseInt(match[1] || match[2]);
                                            if (id && !selectedIds.includes(id)) {
                                                selectedIds.push(id);
                                            }
                                        }
                                    }
                                }
                            });
                        }

                        const container = getContainer();
                        if (!container) {
                            return;
                        }
                        
                        if (selectedIds.length >= 2) {
                            // Вызываем метод Livewire для получения сводки
                            @this.call('getSelectedOrdersSummaryForIds', selectedIds).then(function(summary) {
                                if (summary && summary.count >= 2) {
                                    // Формируем HTML сводки в одну линию
                                    const summaryHtml = `
                                        <div class="fi-ta-selected-summary rounded-xl border border-primary-200/60 bg-primary-50/60 p-4 shadow-sm dark:border-primary-400/20 dark:bg-primary-500/10" style="position: relative; z-index: 100;">
                                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-primary-200/60 pb-3 mb-3 text-base font-medium text-primary-900 dark:border-primary-400/20 dark:text-primary-100">
                                                <span class="text-lg">Выбранные заявки</span>
                                                <span class="text-sm font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-200">
                                                    Количество: ${formatNumber(summary.count)}
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-6 text-lg" style="overflow-x: auto;">
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Палет:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.pallets_count)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Коробов:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.boxes_count)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Объем, м³:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.boxes_volume, 2)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Вес, кг:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.boxes_weight, 2)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Палетирование, шт:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.palletizing_count)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Стоимость забора, ₽:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.pick, 2)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Доставка, ₽:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.delivery, 2)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Палетирование, ₽:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.additional, 2)}</span>
                                                </div>
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <span class="text-base text-slate-500 dark:text-slate-400">Предварительная сумма, ₽:</span>
                                                    <span class="text-lg font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.total, 2)}</span>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    
                                    const cont = getContainer();
                                    if (cont) {
                                        cont.innerHTML = summaryHtml;
                                        cont.style.display = 'block';
                                        cont.style.visibility = 'visible';
                                        cont.style.opacity = '1';
                                        cont.style.height = 'auto';
                                        cont.style.overflow = 'visible';
                                        cont.style.position = 'relative';
                                        cont.style.zIndex = '1000';
                                    }
                                } else {
                                    const cont = getContainer();
                                    if (cont) {
                                        cont.innerHTML = '';
                                        cont.style.display = 'none';
                                    }
                                }
                            }).catch(function(error) {
                                const cont = getContainer();
                                if (cont) {
                                    cont.innerHTML = '';
                                    cont.style.display = 'none';
                                }
                            });
                        } else {
                            const cont = getContainer();
                            if (cont) {
                                cont.innerHTML = '';
                                cont.style.display = 'none';
                            }
                        }
                    }, 200);
                }

                // Отслеживаем изменения чекбоксов
                document.addEventListener('change', function(e) {
                    if (e.target.type === 'checkbox') {
                        updateSummary();
                    }
                });

                // Отслеживаем клики (на случай если change не срабатывает)
                document.addEventListener('click', function(e) {
                    if (e.target.type === 'checkbox') {
                        setTimeout(updateSummary, 100);
                    }
                });

                // Обновляем при загрузке страницы
                function init() {
                    updateSummary();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }

                document.addEventListener('livewire:navigated', function() {
                    setTimeout(updateSummary, 100);
                });

                window.addEventListener('inline-edit-cell-saved', function() {
                    setTimeout(updateSummary, 100);
                });
            })();
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>
