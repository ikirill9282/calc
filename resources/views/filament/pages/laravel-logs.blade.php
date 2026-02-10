<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold dark:text-white">Логи системы</h2>
            <div class="flex items-center gap-4">
                <x-filament::button 
                    wire:click="clearLogs" 
                    color="danger"
                    size="sm"
                >
                    Очистить логи
                </x-filament::button>
                <x-filament::button 
                    wire:click="loadLogs" 
                    color="gray"
                    size="sm"
                >
                    Обновить
                </x-filament::button>
            </div>
        </div>

        <div class="flex gap-4 items-center">
            <div class="flex-1">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Поиск в логах..."
                    class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-base text-gray-950 dark:text-gray-100 outline-none transition duration-75 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:focus:ring-primary-500 sm:text-sm sm:leading-6"
                />
            </div>

            <div class="w-48">
                <select 
                    wire:model.live="level"
                    class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-base text-gray-950 dark:text-gray-100 outline-none transition duration-75 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:focus:ring-primary-500 sm:text-sm sm:leading-6"
                >
                    <option value="all">Все уровни</option>
                    <option value="error">Error</option>
                    <option value="warning">Warning</option>
                    <option value="info">Info</option>
                    <option value="debug">Debug</option>
                </select>
            </div>
        </div>

        @if (empty($logs))
            <div class="rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-6 py-12 text-center text-sm text-gray-600 dark:text-gray-400">
                Логи не найдены.
            </div>
        @else
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <div class="overflow-x-auto">
                    <div class="space-y-4 p-4">
                        @foreach ($logs as $log)
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $this->getLevelColor($log['level']) }}">
                                            {{ strtoupper($log['level']) }}
                                        </span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $log['date'] }}
                                        </span>
                                        @if(isset($log['file']))
                                            <span class="text-xs text-gray-500 dark:text-gray-500">
                                                ({{ $log['file'] }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded mb-2 break-words">
                                    {{ $log['message'] }}
                                </div>
                                
                                @if (!empty(trim($log['stack'])))
                                    <details class="mt-2">
                                        <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                                            Показать стек вызовов
                                        </summary>
                                        <pre class="mt-2 text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded overflow-x-auto font-mono">{{ trim($log['stack']) }}</pre>
                                    </details>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($this->totalPages > 1)
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Страница {{ $this->currentPage }} из {{ $this->totalPages }}
                    </div>
                    <div class="flex gap-2">
                        <x-filament::button 
                            wire:click="previousPage" 
                            :disabled="$this->currentPage === 1"
                            size="sm"
                        >
                            Назад
                        </x-filament::button>
                        <x-filament::button 
                            wire:click="nextPage" 
                            :disabled="$this->currentPage >= $this->totalPages"
                            size="sm"
                        >
                            Вперед
                        </x-filament::button>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>

