@php
    $formatInt = static fn (float $value): string => number_format($value, 0, ',', ' ');
    $formatDecimal = static fn (float $value): string => rtrim(rtrim(number_format($value, 2, ',', ' '), '0'), ',');
    $formatMoney = static fn (float $value): string => number_format($value, 2, ',', ' ');
@endphp

@if (! empty($summary) && ($summary['count'] ?? 0) > 0)
    <div class="fi-ta-selected-summary mt-6 rounded-xl border border-primary-200/60 bg-primary-50/60 p-4 shadow-sm dark:border-primary-400/20 dark:bg-primary-500/10">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-primary-200/60 pb-3 text-sm font-medium text-primary-900 dark:border-primary-400/20 dark:text-primary-100">
            <span>Выбранные заявки</span>
            <span class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-200">
                {{ __('Количество: :count', ['count' => $formatInt($summary['count'] ?? 0)]) }}
            </span>
        </div>

        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Палет</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatInt($summary['pallets_count'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Коробов</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatInt($summary['boxes_count'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Объем, м³</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatDecimal($summary['boxes_volume'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Вес, кг</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatDecimal($summary['boxes_weight'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Паллетирование, шт</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatInt($summary['palletizing_count'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Стоимость забора, ₽</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatMoney($summary['pick'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Доставка, ₽</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatMoney($summary['delivery'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Паллетирование, ₽</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatMoney($summary['additional'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Индивид. стоимость, ₽</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatMoney($summary['individual_cost'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-white/60 px-3 py-2 text-sm shadow-sm dark:bg-slate-900/40">
                <p class="text-xs text-slate-500">Предварительная сумма, ₽</p>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $formatMoney($summary['total'] ?? 0) }}</p>
            </div>
        </div>
    </div>
@endif
