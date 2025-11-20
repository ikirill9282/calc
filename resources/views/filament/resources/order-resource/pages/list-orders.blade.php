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
            wire:ignore
        >
            {{-- Контент будет обновляться через JavaScript --}}
        </div>

        <script>
            (function() {
                let updateTimeout;
                const container = document.getElementById('selected-orders-summary-container');
                
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

                        console.log('Selected IDs:', selectedIds, 'Checked boxes:', checkedBoxes.length);
                        
                        if (selectedIds.length >= 2) {
                            // Вызываем метод Livewire для получения сводки
                            @this.call('getSelectedOrdersSummaryForIds', selectedIds).then(function(summary) {
                                console.log('Summary received:', summary);
                                if (summary && summary.count >= 2) {
                                    // Формируем HTML сводки
                                    const summaryHtml = `
                                        <div class="fi-ta-selected-summary rounded-xl border border-primary-200/60 bg-primary-50/60 p-4 shadow-sm dark:border-primary-400/20 dark:bg-primary-500/10">
                                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-primary-200/60 pb-3 text-sm font-medium text-primary-900 dark:border-primary-400/20 dark:text-primary-100">
                                                <span>Выбранные заявки</span>
                                                <span class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-200">
                                                    Количество: ${formatNumber(summary.count)}
                                                </span>
                                            </div>
                                            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Палет</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.pallets_count)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Коробов</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.boxes_count)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Объем, м³</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.boxes_volume, 2)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Вес, кг</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.boxes_weight, 2)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Палетирование, шт</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.palletizing_count)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Стоимость забора, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.pick, 2)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Доставка, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.delivery, 2)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Палетирование, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.additional, 2)}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Предварительная сумма, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${formatNumber(summary.total, 2)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    container.innerHTML = summaryHtml;
                                    container.style.display = 'block';
                                } else {
                                    container.innerHTML = '';
                                    container.style.display = 'none';
                                }
                            }).catch(function(error) {
                                console.error('Error getting summary:', error);
                                container.innerHTML = '<div class="p-2 bg-red-100 text-red-800 text-xs">Ошибка получения сводки: ' + error.message + '</div>';
                            });
                        } else {
                            container.innerHTML = '';
                            container.style.display = 'none';
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
                    // Обновляем периодически
                    setInterval(updateSummary, 1500);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            })();
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

