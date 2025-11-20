<?php

namespace App\Tables\Summarizers;

use Closure;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class ConditionalSum extends Sum
{
    protected ?Closure $expressionResolver = null;

    protected ?Closure $recordValueResolver = null;

    public function expression(?Closure $resolver): static
    {
        $this->expressionResolver = $resolver;

        return $this;
    }

    public function recordValueUsing(?Closure $resolver): static
    {
        $this->recordValueResolver = $resolver;

        return $this;
    }

    public function summarize(Builder $query, string $attribute): int | float | null
    {
        // ПРИОРИТЕТ: Если есть выбранные записи, суммируем только их
        if ($this->recordValueResolver !== null) {
            try {
                $livewire = $this->getLivewire();
                if ($livewire && method_exists($livewire, 'getSelectedTableRecords')) {
                    $selectedRecords = $livewire->getSelectedTableRecords();
                    
                    if ($selectedRecords && $selectedRecords->isNotEmpty()) {
                        // Суммируем только выбранные записи
                        $sum = (float) $selectedRecords->sum(function ($record) {
                            $value = $this->evaluate($this->recordValueResolver, [
                                'record' => $record,
                            ]);
                            return $value === null ? 0.0 : (float) $value;
                        });
                        
                        // Логируем для отладки (можно убрать позже)
                        \Log::debug('ConditionalSum::summarize - Selected records sum', [
                            'attribute' => $attribute,
                            'selected_count' => $selectedRecords->count(),
                            'sum' => $sum,
                        ]);
                        
                        return $sum;
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('ConditionalSum::summarize error', [
                    'error' => $e->getMessage(),
                    'attribute' => $attribute,
                ]);
            }
        }

        $expression = $this->resolveExpression($attribute);

        if ($expression === null) {
            if ($this->recordValueResolver !== null) {
                $sql = $query->cloneWithout(['columns'])->cloneWithoutBindings(['order']);

                // When Filament wraps the table query into a subquery, the builder instance
                // may not contain a model. Try to resolve it from the table component.
                $modelPrototype = method_exists($this->getLivewire(), 'getTable')
                    ? optional($this->getLivewire()->getTable()?->getQuery())->getModel()
                    : null;

                $sum = 0.0;

                foreach ($sql->cursor() as $record) {
                    $recordForEvaluation = $record;

                    if ($modelPrototype && ! $record instanceof Model) {
                        $recordForEvaluation = $modelPrototype->newFromBuilder((array) $record);
                    }

                    $value = $this->evaluate($this->recordValueResolver, [
                        'record' => $recordForEvaluation,
                    ]);

                    $sum += $value === null ? 0.0 : (float) $value;
                }

                return $sum;
            }

            return parent::summarize($query, $attribute);
        }

        return (float) ($query
            ->selectRaw("COALESCE(SUM({$expression}), 0) as aggregate")
            ->value('aggregate') ?? 0);
    }

    /**
     * @return array<string, string>
     */
    public function getSelectStatements(string $column): array
    {
        $expression = $this->resolveExpression($column);

        if ($expression === null) {
            if ($this->recordValueResolver !== null) {
                return [];
            }

            return parent::getSelectStatements($column);
        }

        return [
            $this->getSelectAlias() => "COALESCE(SUM({$expression}), 0)",
        ];
    }

    public function getSelectedState(): int | float | null
    {
        if ($this->recordValueResolver === null) {
            return parent::getSelectedState();
        }

        try {
            /** @var Collection $records */
            $records = $this->getLivewire()->getSelectedTableRecords();

            if ($records->isEmpty()) {
                return parent::getSelectedState();
            }

            // Суммируем только выбранные записи
            return (float) $records->sum(function ($record) {
                $value = $this->evaluate($this->recordValueResolver, [
                    'record' => $record,
                ]);

                return $value === null ? 0.0 : (float) $value;
            });
        } catch (\Throwable $e) {
            return parent::getSelectedState();
        }
    }

    protected function resolveExpression(string $attribute): ?string
    {
        if ($this->expressionResolver === null) {
            return null;
        }

        return $this->evaluate($this->expressionResolver, [
            'attribute' => $attribute,
        ]);
    }
}
