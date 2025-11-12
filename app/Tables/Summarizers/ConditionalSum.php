<?php

namespace App\Tables\Summarizers;

use Closure;
use Filament\Tables\Columns\Summarizers\Sum;
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
        $expression = $this->resolveExpression($attribute);

        if ($expression === null) {
            if ($this->recordValueResolver !== null) {
                $sql = $query->cloneWithout(['columns', 'orders'])->cloneWithoutBindings(['order']);
                $sql->orders = [];
                $sql->unionOrders = [];

                $sum = 0.0;

                foreach ($sql->cursor() as $record) {
                    $value = $this->evaluate($this->recordValueResolver, [
                        'record' => $record,
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

        /** @var Collection $records */
        $records = $this->getLivewire()->getSelectedTableRecords();

        if ($records->isEmpty()) {
            return parent::getSelectedState();
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
}
