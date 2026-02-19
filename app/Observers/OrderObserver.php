<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderChangeLog;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $changes = $order->getChanges();
        $ignore = ['updated_at', 'changed_fields'];
        $userId = Auth::id();

        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore, true)) {
                continue;
            }

            $oldValue = $order->getOriginal($field);

            if ($this->valuesAreEqual($oldValue, $newValue)) {
                continue;
            }

            OrderChangeLog::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'field' => $field,
                'old_value' => $this->stringifyValue($oldValue),
                'new_value' => $this->stringifyValue($newValue),
            ]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    protected function stringifyValue(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    protected function valuesAreEqual(mixed $old, mixed $new): bool
    {
        if (is_null($old) && is_null($new)) {
            return true;
        }

        return (string) $old === (string) $new;
    }
}
