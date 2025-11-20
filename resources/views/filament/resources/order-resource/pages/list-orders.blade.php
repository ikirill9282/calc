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
                
                function updateSummary() {
                    clearTimeout(updateTimeout);
                    updateTimeout = setTimeout(function() {
                        // Получаем выбранные ID из чекбоксов
                        const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model*="selectedTableRecords"]');
                        const selectedIds = [];
                        
                        checkboxes.forEach(function(checkbox) {
                            if (checkbox.checked) {
                                const match = checkbox.getAttribute('wire:model')?.match(/selectedTableRecords\.(\d+)/);
                                if (match) {
                                    selectedIds.push(parseInt(match[1]));
                                }
                            }
                        });

                        const container = document.getElementById('selected-orders-summary-container');
                        
                        if (selectedIds.length >= 2) {
                            // Вызываем метод Livewire для получения сводки
                            @this.call('getSelectedOrdersSummaryForIds', selectedIds).then(function(summary) {
                                if (summary && summary.count >= 2) {
                                    // Формируем HTML сводки
                                    const summaryHtml = `
                                        <div class="fi-ta-selected-summary rounded-xl border border-primary-200/60 bg-primary-50/60 p-4 shadow-sm dark:border-primary-400/20 dark:bg-primary-500/10">
                                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-primary-200/60 pb-3 text-sm font-medium text-primary-900 dark:border-primary-400/20 dark:text-primary-100">
                                                <span>Выбранные заявки</span>
                                                <span class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-200">
                                                    Количество: ${summary.count.toLocaleString('ru-RU')}
                                                </span>
                                            </div>
                                            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Палет</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.pallets_count || 0).toLocaleString('ru-RU')}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Коробов</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.boxes_count || 0).toLocaleString('ru-RU')}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Объем, м³</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.boxes_volume || 0).toLocaleString('ru-RU', {minimumFractionDigits: 0, maximumFractionDigits: 2})}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Вес, кг</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.boxes_weight || 0).toLocaleString('ru-RU', {minimumFractionDigits: 0, maximumFractionDigits: 2})}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Палетирование, шт</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.palletizing_count || 0).toLocaleString('ru-RU')}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Стоимость забора, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.pick || 0).toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Доставка, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.delivery || 0).toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Палетирование, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.additional || 0).toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                                </div>
                                                <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                                                    <p class="text-xs text-slate-500">Предварительная сумма, ₽</p>
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">${(summary.total || 0).toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
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
                                container.innerHTML = '';
                                container.style.display = 'none';
                            });
                        } else {
                            container.innerHTML = '';
                            container.style.display = 'none';
                        }
                    }, 300);
                }

                // Отслеживаем изменения чекбоксов
                document.addEventListener('change', function(e) {
                    if (e.target.type === 'checkbox' && e.target.getAttribute('wire:model')?.includes('selectedTableRecords')) {
                        updateSummary();
                    }
                });

                // Также отслеживаем через Livewire события
                if (window.Livewire) {
                    Livewire.hook('morph.updated', function() {
                        updateSummary();
                    });
                }

                // Обновляем при загрузке страницы
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', updateSummary);
                } else {
                    updateSummary();
                }

                // Обновляем периодически (на случай если события не срабатывают)
                setInterval(updateSummary, 1000);
            })();
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

