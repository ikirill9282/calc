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

    protected function setUp(): void
    {
        parent::setUp();

        $this->query(function (string $attribute, Builder $query): Builder {
            $selectedKeys = $this->getSelectedRecordKeys();

            if ($selectedKeys->isEmpty()) {
                return $query;
            }

            $model = optional($this->getLivewire()->getTable()?->getQuery())->getModel();

            if (! $model) {
                return $query;
            }

            return $query->whereIn($model->getQualifiedKeyName(), $selectedKeys->all());
        });
    }

    public function getState(): mixed
    {
        $selectedKeys = $this->getSelectedRecordKeys();

        if ($selectedKeys->isNotEmpty()) {
            $livewire = $this->getLivewire();

            if ($this->recordValueResolver !== null && method_exists($livewire, 'getSelectedTableRecords')) {
                $records = $livewire->getSelectedTableRecords();

                if ($records->isNotEmpty()) {
                    $sum = $records->sum(function ($record) {
                        $value = $this->evaluate($this->recordValueResolver, [
                            'record' => $record,
                        ]);

                        return $value === null ? 0.0 : (float) $value;
                    });

                    return $sum === 0.0 ? '0' : $sum;
                }
            }

            $tableQuery = optional($livewire?->getTable()?->getQuery());

            if ($tableQuery instanceof \Illuminate\Database\Eloquent\Builder) {
                $model = $tableQuery->getModel();

                $sum = $tableQuery
                    ->cloneWithout(['columns', 'orders', 'unionOrders'])
                    ->cloneWithoutBindings(['select', 'order', 'union', 'unionOrder'])
                    ->selectRaw('COALESCE(SUM(' . $model->qualifyColumn($this->getColumn()->getName()) . '), 0) as aggregate')
                    ->whereIn($model->getQualifiedKeyName(), $selectedKeys->all())
                    ->value('aggregate') ?? 0.0;

                return (float) $sum === 0.0 ? '0' : (float) $sum;
            }
        }

        return '0';
    }

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
        $selectedKeys = $this->getSelectedRecordKeys();

        if ($selectedKeys->isNotEmpty()) {
            if ($this->recordValueResolver !== null && method_exists($this->getLivewire(), 'getSelectedTableRecords')) {
                $selectedRecords = $this->getLivewire()->getSelectedTableRecords();

                if ($selectedRecords->isNotEmpty()) {
                    return (float) $selectedRecords->sum(function ($record) {
                        $value = $this->evaluate($this->recordValueResolver, [
                            'record' => $record,
                        ]);

                        return $value === null ? 0.0 : (float) $value;
                    });
                }
            }

            $model = optional($this->getLivewire()->getTable()?->getQuery())->getModel();

            if ($model) {
                $query = (clone $query)->whereIn($model->getQualifiedKeyName(), $selectedKeys->all());
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

            return 0.0;
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

        /** @var Collection $records */
        $records = method_exists($this->getLivewire(), 'getSelectedTableRecords')
            ? $this->getLivewire()->getSelectedTableRecords()
            : collect();

        if ($records->isEmpty()) {
            return 0.0;
        }

        return (float) $records->sum(function ($record) {
            $value = $this->evaluate($this->recordValueResolver, [
                'record' => $record,
            ]);

            return $value === null ? 0 : (float) $value;
        });
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

    protected function getSelectedRecordKeys(): Collection
    {
        $livewire = $this->getLivewire();

        if (! $livewire || ! property_exists($livewire, 'selectedTableRecords')) {
            return collect();
        }

        return collect($livewire->selectedTableRecords)
            ->filter(fn ($key) => $key !== null && $key !== '')
            ->values();
    }
}
