<?php

namespace App\Http\Controllers\Filament;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderExportController
{
    public function show(Request $request, Order $order)
    {
        /** @var ListOrders $page */
        $page = app(ListOrders::class);
        $page->mount();

        return $page->exportSelectedRecords(collect([$order]));
    }
}
