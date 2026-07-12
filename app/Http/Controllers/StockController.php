<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;

class StockController extends Controller
{
    public function summary()
    {
        $stocks = PurchaseItem::selectRaw('
                item_id,
                SUM(remaining_qty) as stock_qty
            ')
            ->with('item')
            ->groupBy('item_id')
            ->orderBy('item_id')
            ->get();

        return view('inventory.stock-summary', compact('stocks'));
    }
}