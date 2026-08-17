<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;

class StockController extends Controller
{
    public function summary()
    {
        $stocks = PurchaseItem::selectRaw('
            item_id,
            SUM(qty) as purchased_qty,
            SUM(remaining_qty) as stock_qty,
            CASE
                WHEN SUM(qty) > 0
                THEN SUM(qty * price) / SUM(qty)
                ELSE 0
            END as avg_price,
            SUM(remaining_qty * price) as stock_value
        ')
            ->with('item')
            ->groupBy('item_id')
            ->orderBy('item_id')
            ->get();

        return view(
            'inventory.stock-summary',
            compact('stocks')
        );
    }
}
